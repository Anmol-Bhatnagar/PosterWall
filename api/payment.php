<?php
require_once '../config.php';
header('Content-Type: application/json');
if (!loggedIn()) { echo json_encode(['error'=>'not_logged_in']); exit; }

$input  = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';
$uid    = (int)$_SESSION['uid'];
$db     = db();

if ($action === 'create_order') {
    $amount = max(9, (int)($input['amount'] ?? 9));
    $ch = curl_init('https://api.razorpay.com/v1/orders');
    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER=>true,CURLOPT_POST=>true,CURLOPT_USERPWD=>RZP_KEY_ID.':'.RZP_KEY_SECRET,CURLOPT_HTTPHEADER=>['Content-Type: application/json'],CURLOPT_POSTFIELDS=>json_encode(['amount'=>$amount*100,'currency'=>'INR','receipt'=>'pw_'.time().'_'.$uid])]);
    $res = json_decode(curl_exec($ch), true); curl_close($ch);
    if (isset($res['id'])) echo json_encode(['success'=>true,'order_id'=>$res['id']]);
    else echo json_encode(['error'=>'order_failed']);
    exit;
}

if ($action === 'verify') {
    $pid = $input['razorpay_payment_id'] ?? '';
    $oid = $input['razorpay_order_id']   ?? '';
    $sig = $input['razorpay_signature']  ?? '';
    if (!hash_equals(hash_hmac('sha256', $oid.'|'.$pid, RZP_KEY_SECRET), $sig)) {
        echo json_encode(['error'=>'invalid_signature']); exit;
    }
    $ch = curl_init("https://api.razorpay.com/v1/orders/$oid");
    curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_USERPWD=>RZP_KEY_ID.':'.RZP_KEY_SECRET]);
    $order = json_decode(curl_exec($ch), true); curl_close($ch);
    $amount = ($order['amount'] ?? 900) / 100;
    $db->query("INSERT INTO wallets (user_id,balance) VALUES ($uid,$amount) ON DUPLICATE KEY UPDATE balance=balance+$amount");
    $p = $db->real_escape_string($pid); $o = $db->real_escape_string($oid);
    $db->query("INSERT INTO transactions (user_id,rzp_order,rzp_payment,amount,type,status,note) VALUES ($uid,'$o','$p',$amount,'credit','success','Wallet recharge via Razorpay')");
    echo json_encode(['success'=>true,'amount'=>$amount]);
    exit;
}

echo json_encode(['error'=>'invalid_action']);
?>
