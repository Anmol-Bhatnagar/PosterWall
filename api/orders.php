<?php
require_once '../config.php';
header('Content-Type: application/json');

$input  = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';
$db     = db();

if ($action === 'create_order') {
    $pageId    = (int)($input['page_id'] ?? 0);
    $custName  = trim($input['customer_name'] ?? '');
    $custPhone = trim($input['customer_phone'] ?? '');
    $custAddr  = trim($input['delivery_address'] ?? '');
    $items     = $input['items'] ?? []; // Array of {"name":"Paneer Tikka","qty":2}
    
    if (!$pageId || !$custName || !$custPhone || empty($items)) {
        echo json_encode(['error' => 'missing_fields', 'msg' => 'Please fill all required fields.']);
        exit;
    }

    // Verify page exists and is active
    $pageRes = $db->query("SELECT * FROM pages WHERE id=$pageId AND is_active=1");
    if (!$pageRes || $pageRes->num_rows === 0) {
        echo json_encode(['error' => 'invalid_page', 'msg' => 'Business page not found.']);
        exit;
    }
    $page = $pageRes->fetch_assoc();

    // Verify pricing to prevent client-side tampering
    $menuData = json_decode($page['menu_items'] ?? '[]', true) ?: [];
    
    // Flatten menu items for easy lookup
    $priceMap = [];
    foreach ($menuData as $category) {
        $catItems = $category['items'] ?? [];
        foreach ($catItems as $item) {
            $name = trim($item['name'] ?? '');
            // Extract numeric price from string
            $rawPrice = preg_replace('/[^\d.]/', '', $item['price'] ?? '0');
            $price = (float)$rawPrice;
            if ($name !== '') {
                $priceMap[$name] = $price;
            }
        }
    }

    $calculatedTotal = 0;
    $orderItemsDetails = [];
    foreach ($items as $ordered) {
        $name = trim($ordered['name'] ?? '');
        $qty  = (int)($ordered['qty'] ?? 0);
        if ($name === '' || $qty <= 0) continue;

        // Lookup price
        $price = isset($priceMap[$name]) ? $priceMap[$name] : 0.0;
        
        $calculatedTotal += ($price * $qty);
        $orderItemsDetails[] = [
            'name'  => $name,
            'qty'   => $qty,
            'price' => $price
        ];
    }

    if ($calculatedTotal <= 0) {
        echo json_encode(['error' => 'invalid_amount', 'msg' => 'Order total must be greater than zero.']);
        exit;
    }

    // Create Razorpay Order
    $amountInPaise = round($calculatedTotal * 100);
    $receiptId = 'ord_' . time() . '_' . $pageId . '_' . rand(100, 999);
    
    $ch = curl_init('https://api.razorpay.com/v1/orders');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_USERPWD        => RZP_KEY_ID . ':' . RZP_KEY_SECRET,
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
        CURLOPT_POSTFIELDS     => json_encode([
            'amount'   => $amountInPaise,
            'currency' => 'INR',
            'receipt'  => $receiptId
        ])
    ]);
    
    $res = json_decode(curl_exec($ch), true);
    if (PHP_VERSION_ID < 80500) {
        curl_close($ch);
    }

    if (!isset($res['id'])) {
        error_log("Razorpay Order creation failed: " . json_encode($res));
        echo json_encode(['error' => 'rzp_failed', 'msg' => 'Could not initiate payment. Try again.']);
        exit;
    }

    $rzpOrderId = $db->real_escape_string($res['id']);
    
    // Insert pending order
    $safeName  = $db->real_escape_string($custName);
    $safePhone = $db->real_escape_string($custPhone);
    $safeAddr  = $db->real_escape_string($custAddr);
    $safeItems = $db->real_escape_string(json_encode($orderItemsDetails, JSON_UNESCAPED_UNICODE));
    
    $db->query("INSERT INTO orders (page_id, customer_name, customer_phone, delivery_address, items, total_amount, payment_status, rzp_order_id) VALUES ($pageId, '$safeName', '$safePhone', '$safeAddr', '$safeItems', $calculatedTotal, 'pending', '$rzpOrderId')");

    echo json_encode([
        'success'  => true,
        'order_id' => $res['id'],
        'amount'   => $calculatedTotal
    ]);
    exit;
}

if ($action === 'verify_payment') {
    $pid = $input['razorpay_payment_id'] ?? '';
    $oid = $input['razorpay_order_id']   ?? '';
    $sig = $input['razorpay_signature']  ?? '';

    if (!$pid || !$oid || !$sig) {
        echo json_encode(['error' => 'missing_params']);
        exit;
    }

    // Verify signature
    $expected = hash_hmac('sha256', $oid . '|' . $pid, RZP_KEY_SECRET);
    if (!hash_equals($expected, $sig)) {
        echo json_encode(['error' => 'invalid_signature']);
        exit;
    }

    // Update order status in DB
    $safePid = $db->real_escape_string($pid);
    $safeOid = $db->real_escape_string($oid);

    // Verify order exists
    $orderRes = $db->query("SELECT * FROM orders WHERE rzp_order_id='$safeOid'");
    if (!$orderRes || $orderRes->num_rows === 0) {
        echo json_encode(['error' => 'order_not_found']);
        exit;
    }
    
    $db->query("UPDATE orders SET payment_status='success', rzp_payment_id='$safePid' WHERE rzp_order_id='$safeOid'");

    echo json_encode(['success' => true]);
    exit;
}

echo json_encode(['error' => 'invalid_action']);
?>
