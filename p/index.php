<?php
require_once '../config.php';

$token = preg_replace('/[^a-f0-9A-F]/', '', $_GET['t'] ?? basename(strtok($_SERVER['REQUEST_URI'], '?')));
if (!$token || strlen($token) < 6) { header('Location: ' . siteUrl('')); exit; }

$db  = db();
$tok = $db->real_escape_string($token);
$res = $db->query("SELECT * FROM pages WHERE token='$tok' AND is_active=1");

if ($res === false) {
    error_log("p/index.php query failed for token=$tok: " . $db->error);
    http_response_code(500);
    ?><!DOCTYPE html>
    <html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Error — PosterWall</title>
    <style>body{font-family:sans-serif;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0;text-align:center;padding:20px;background:#fff;color:#333;}
    a{display:inline-block;background:#FF6B00;color:#fff;padding:12px 28px;border-radius:10px;font-weight:600;text-decoration:none;}</style>
    </head><body><h2 style="color:#FF6B00;">Kuch Galat Hua</h2>
    <p>Server mein thodi problem hai. Thodi der baad try karo.</p>
    <a href="<?= siteUrl('') ?>">PosterWall pe Jao →</a></body></html>
    <?php exit;
}

if ($res->num_rows === 0) {
    http_response_code(404);
    ?><!DOCTYPE html>
    <html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Page Not Found — PosterWall</title>
    <style>body{font-family:sans-serif;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0;text-align:center;padding:20px;background:#fff;color:#333;}
    a{display:inline-block;background:#FF6B00;color:#fff;padding:12px 28px;border-radius:10px;font-weight:600;text-decoration:none;}</style>
    </head><body><h2 style="color:#FF6B00;">Page Nahi Mila</h2>
    <p>Yeh link expire ho gaya ya galat hai.</p>
    <a href="<?= siteUrl('') ?>">PosterWall pe Jao →</a></body></html>
    <?php exit;
}

$page = $res->fetch_assoc();
$db->query("UPDATE pages SET views=views+1 WHERE token='$tok'");

header('Content-Type: text/html; charset=UTF-8');
header('X-Frame-Options: SAMEORIGIN');

$html = $page['html_content'];
$menuItemsJson = json_decode($page['menu_items'] ?? '[]', true);

if (!empty($menuItemsJson)) {
    // Generate injected HTML
    $injectedHtml = '
    <!-- INJECTED POSTERWALL CART SYSTEM -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    
    <style>
    .pw-fab {
        position: fixed;
        bottom: 28px;
        right: 20px;
        background: linear-gradient(135deg, #7C3AED, #A855F7);
        color: white;
        border: none;
        border-radius: 50px;
        padding: 14px 22px;
        font-family: "Poppins", sans-serif;
        font-weight: 700;
        font-size: 0.92rem;
        box-shadow: 0 8px 32px rgba(124, 58, 237, 0.5);
        cursor: pointer;
        z-index: 9999;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s ease;
    }
    .pw-fab:hover {
        transform: translateY(-3px);
        box-shadow: 0 14px 44px rgba(124, 58, 237, 0.6);
    }
    .pw-fab-badge {
        background: #fff;
        color: #7C3AED;
        border-radius: 50%;
        font-size: 0.72rem;
        font-weight: 800;
        padding: 2px 7px;
        min-width: 20px;
        text-align: center;
    }
    
    .pw-backdrop {
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.75);
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
        z-index: 10000;
        display: none;
        opacity: 0;
        transition: opacity 0.3s ease;
    }
    .pw-backdrop.open {
        display: block;
        opacity: 1;
    }
    
    .pw-drawer {
        position: fixed;
        bottom: -100%;
        left: 0;
        right: 0;
        height: 88vh;
        background: rgba(10,7,30,0.97);
        backdrop-filter: blur(24px);
        -webkit-backdrop-filter: blur(24px);
        border: 1px solid rgba(124,58,237,0.3);
        border-radius: 28px 28px 0 0;
        box-shadow: 0 -16px 60px rgba(124,58,237,0.2);
        z-index: 10001;
        transition: bottom 0.4s cubic-bezier(0.32, 0.94, 0.6, 1);
        display: flex;
        flex-direction: column;
        font-family: "Poppins", sans-serif;
        color: #ede9ff;
        text-align: left;
        overflow: hidden;
    }
    .pw-drawer.open {
        bottom: 0;
    }
    .pw-drawer::before {
        content: "";
        position: absolute;
        top: -50%;
        right: -20%;
        width: 400px;
        height: 400px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(168,85,247,0.12), transparent);
        pointer-events: none;
    }
    
    .pw-dr-header {
        padding: 20px 20px 16px;
        border-bottom: 1px solid rgba(124,58,237,0.2);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .pw-dr-title {
        font-weight: 700;
        font-size: 1.1rem;
        background: linear-gradient(135deg, #A855F7, #C084FC);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
    .pw-dr-close {
        border: 1px solid rgba(124,58,237,0.3);
        background: rgba(124,58,237,0.12);
        color: #C084FC;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        font-weight: bold;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s;
    }
    .pw-dr-close:hover { background: rgba(124,58,237,0.3); }
    
    .pw-dr-body {
        flex: 1;
        overflow-y: auto;
        padding: 20px;
        scrollbar-width: thin;
        scrollbar-color: rgba(124,58,237,0.3) transparent;
    }
    
    .pw-category-title {
        font-weight: 700;
        font-size: 0.88rem;
        margin: 18px 0 10px;
        padding: 4px 12px;
        border-radius: 6px;
        background: rgba(124,58,237,0.12);
        border-left: 3px solid #A855F7;
        display: block;
        color: #C084FC;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .pw-item-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 0;
        border-bottom: 1px solid rgba(124,58,237,0.1);
    }
    
    .pw-item-info {
        flex: 1;
        padding-right: 12px;
    }
    
    .pw-item-name {
        font-weight: 600;
        font-size: 0.92rem;
        color: #ede9ff;
    }
    
    .pw-item-price {
        font-weight: 700;
        color: #C084FC;
        font-size: 0.88rem;
        margin-top: 2px;
    }
    
    .pw-action-col {
        min-width: 90px;
        display: flex;
        justify-content: flex-end;
    }
    
    .pw-btn-add {
        background: rgba(124,58,237,0.12);
        border: 1px solid rgba(124,58,237,0.4);
        color: #C084FC;
        padding: 6px 16px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.8rem;
        cursor: pointer;
        transition: all 0.2s;
    }
    .pw-btn-add:hover {
        background: rgba(124,58,237,0.3);
        color: #fff;
        box-shadow: 0 4px 16px rgba(124,58,237,0.3);
    }
    
    .pw-counter {
        display: flex;
        align-items: center;
        background: linear-gradient(135deg, #7C3AED, #A855F7);
        color: white;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 4px 14px rgba(124,58,237,0.35);
    }
    
    .pw-count-btn {
        border: none;
        background: transparent;
        color: white;
        width: 30px;
        height: 30px;
        font-size: 0.9rem;
        font-weight: bold;
        cursor: pointer;
        transition: background 0.15s;
    }
    .pw-count-btn:hover { background: rgba(255,255,255,0.15); }
    
    .pw-count-val {
        padding: 0 4px;
        font-size: 0.85rem;
        font-weight: 600;
        min-width: 16px;
        text-align: center;
    }
    
    .pw-sticky-footer {
        padding: 16px 20px;
        border-top: 1px solid rgba(124,58,237,0.2);
        background: rgba(10,7,30,0.98);
        display: none;
    }
    
    .pw-footer-btn {
        width: 100%;
        background: linear-gradient(135deg, #7C3AED, #A855F7);
        color: white;
        border: none;
        border-radius: 50px;
        padding: 14px 20px;
        font-weight: 700;
        font-size: 0.92rem;
        cursor: pointer;
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: 0 6px 24px rgba(124,58,237,0.4);
        transition: all 0.3s;
    }
    .pw-footer-btn:hover { transform: translateY(-1px); box-shadow: 0 10px 32px rgba(124,58,237,0.5); }
    
    .pw-form-group {
        margin-bottom: 14px;
        text-align: left;
    }
    .pw-form-group label {
        display: block;
        font-size: 0.78rem;
        font-weight: 600;
        color: #8B7FAB;
        margin-bottom: 6px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .pw-input {
        width: 100%;
        padding: 13px 14px;
        border-radius: 12px;
        border: 1px solid rgba(124,58,237,0.3);
        font-family: inherit;
        font-size: 0.9rem;
        outline: none;
        box-sizing: border-box;
        background: rgba(20,14,50,0.8);
        color: #ede9ff;
        transition: border-color 0.25s;
    }
    .pw-input:focus {
        border-color: #A855F7;
        box-shadow: 0 0 0 3px rgba(124,58,237,0.15);
    }
    .pw-input::placeholder { color: #8B7FAB; }
    
    .pw-btn-secondary {
        background: rgba(124,58,237,0.1);
        color: #C084FC;
        border: 1px solid rgba(124,58,237,0.25);
        width: 100%;
        padding: 12px;
        border-radius: 50px;
        font-weight: 600;
        margin-top: 8px;
        cursor: pointer;
        transition: all 0.25s;
    }
    .pw-btn-secondary:hover { background: rgba(124,58,237,0.2); }
    
    .pw-success-screen {
        text-align: center;
        padding: 30px 10px;
    }
    .pw-success-icon {
        width: 70px;
        height: 70px;
        background: linear-gradient(135deg, #7C3AED, #10b981);
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.2rem;
        margin: 0 auto 20px;
        box-shadow: 0 8px 32px rgba(124,58,237,0.3);
    }
    
    .pw-summary-box {
        background: rgba(124,58,237,0.08);
        border: 1px dashed rgba(124,58,237,0.25);
        border-radius: 14px;
        padding: 16px;
        margin: 16px 0;
        text-align: left;
        font-size: 0.85rem;
        color: #8B7FAB;
    }
    .pw-summary-box strong { color: #C084FC; }
    </style>
    
    <button class="pw-fab" onclick="pwToggleDrawer()"><i class="fas fa-shopping-bag"></i> Place Order</button>
    <div class="pw-backdrop" onclick="pwToggleDrawer()"></div>
    
    <div class="pw-drawer" id="pwDrawer">
        <div class="pw-dr-header">
            <div class="pw-dr-title" id="pwDrTitle">Order Online</div>
            <button class="pw-dr-close" onclick="pwToggleDrawer()">✕</button>
        </div>
        
        <div class="pw-dr-body" id="pwDrBody">
            <!-- Rendered Menu items or Checkout Form goes here -->
        </div>
        
        <div class="pw-sticky-footer" id="pwFooter">
            <button class="pw-footer-btn" onclick="pwGoToCheckout()">
                <span>View Basket</span>
                <span id="pwFooterTotal">₹0.00 &nbsp; Checkout →</span>
            </button>
        </div>
    </div>
    
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <script>
    const pwMenu = ' . json_encode($menuItemsJson, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) . ';
    const pwPageId = ' . (int)$page['id'] . ';
    const pwBizName = ' . json_encode($page['business_name'], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) . ';
    const pwRzpKey = ' . json_encode(RZP_KEY_ID) . ';
    
    let pwCart = {};
    let pwScreen = "menu"; // menu, checkout, success
    
    function pwToggleDrawer() {
        const d = document.getElementById("pwDrawer");
        const b = document.querySelector(".pw-backdrop");
        d.classList.toggle("open");
        b.classList.toggle("open");
        
        if (d.classList.contains("open") && pwScreen !== "success") {
            pwRenderScreen();
        }
    }
    
    function pwRenderScreen() {
        const body = document.getElementById("pwDrBody");
        const footer = document.getElementById("pwFooter");
        const title = document.getElementById("pwDrTitle");
        
        if (pwScreen === "menu") {
            title.textContent = "Order Online — " + pwBizName;
            footer.style.display = pwGetCartTotal() > 0 ? "block" : "none";
            
            let html = "";
            pwMenu.forEach((category, cIdx) => {
                const items = category.items || [];
                if (items.length === 0) return;
                
                html += `<div class="pw-category-title">${category.category}</div>`;
                items.forEach((item, iIdx) => {
                    const name = item.name;
                    // parse price
                    const rawPrice = item.price ? item.price.toString().replace(/[^\d.]/g, "") : "0";
                    const price = parseFloat(rawPrice) || 0;
                    
                    const qty = pwCart[name] ? pwCart[name].qty : 0;
                    
                    html += `
                    <div class="pw-item-row">
                        <div class="pw-item-info">
                            <div class="pw-item-name">${name}</div>
                            <div class="pw-item-price">₹${price}</div>
                        </div>
                        <div class="pw-action-col">
                            ${qty > 0 ? `
                                <div class="pw-counter">
                                    <button class="pw-count-btn" onclick="pwUpdateQty(\`${name}\`, ${price}, -1)">-</button>
                                    <span class="pw-count-val">${qty}</span>
                                    <button class="pw-count-btn" onclick="pwUpdateQty(\`${name}\`, ${price}, 1)">+</button>
                                </div>
                            ` : `
                                <button class="pw-btn-add" onclick="pwUpdateQty(\`${name}\`, ${price}, 1)">+ Add</button>
                            `}
                        </div>
                    </div>`;
                });
            });
            
            body.innerHTML = html;
            pwUpdateFooter();
        } else if (pwScreen === "checkout") {
            title.textContent = "Checkout Details";
            footer.style.display = "none";
            
            let html = `
            <div style="font-size:0.9rem;margin-bottom:16px;"><strong>Basket Summary:</strong></div>
            <div style="margin-bottom:20px;max-height:150px;overflow-y:auto;border-bottom:1px solid rgba(0,0,0,0.06);padding-bottom:10px;">`;
            
            for (let name in pwCart) {
                const c = pwCart[name];
                html += `
                <div style="display:flex;justify-content:space-between;font-size:0.85rem;margin-bottom:6px;">
                    <span>${name} (x${c.qty})</span>
                    <span>₹${c.qty * c.price}</span>
                </div>`;
            }
            
            html += `
            </div>
            <div style="display:flex;justify-content:space-between;font-weight:700;font-size:1.05rem;margin-bottom:20px;color:#FF6B00;">
                <span>Total Amount:</span>
                <span>₹${pwGetCartTotal()}</span>
            </div>
            
            <div class="pw-form-group">
                <label>Your Name <span style="color:#ef4444;">*</span></label>
                <input type="text" id="cust_name" class="pw-input" placeholder="Enter your full name" required>
            </div>
            <div class="pw-form-group">
                <label>Mobile Number <span style="color:#ef4444;">*</span></label>
                <input type="tel" id="cust_phone" class="pw-input" placeholder="10-digit mobile number" maxlength="10" required>
            </div>
            <div class="pw-form-group">
                <label>Delivery Address / Table Number</label>
                <textarea id="cust_addr" class="pw-input" rows="2" placeholder="e.g. Table 5 OR Complete Home Address"></textarea>
            </div>
            
            <button class="pw-footer-btn" style="margin-top:16px;" onclick="pwPlaceOrder()">
                <span>Pay & Confirm</span>
                <span>₹${pwGetCartTotal()} →</span>
            </button>
            <button class="pw-btn-secondary" onclick="pwGoToMenu()">← Back to Menu</button>
            `;
            
            body.innerHTML = html;
        }
    }
    
    function pwUpdateQty(name, price, change) {
        if (!pwCart[name]) {
            pwCart[name] = { qty: 0, price: price };
        }
        
        pwCart[name].qty += change;
        if (pwCart[name].qty <= 0) {
            delete pwCart[name];
        }
        
        pwRenderScreen();
    }
    
    function pwGetCartTotal() {
        let t = 0;
        for (let k in pwCart) {
            t += pwCart[k].qty * pwCart[k].price;
        }
        return t;
    }
    
    function pwGetCartCount() {
        let c = 0;
        for (let k in pwCart) {
            c += pwCart[k].qty;
        }
        return c;
    }
    
    function pwUpdateFooter() {
        const foot = document.getElementById("pwFooter");
        const total = document.getElementById("pwFooterTotal");
        const count = pwGetCartCount();
        const amt = pwGetCartTotal();
        
        if (count > 0) {
            foot.style.display = "block";
            total.innerHTML = `${count} item${count>1?"s":""} | ₹${amt} &nbsp; Checkout →`;
        } else {
            foot.style.display = "none";
        }
    }
    
    function pwGoToCheckout() {
        pwScreen = "checkout";
        pwRenderScreen();
    }
    
    function pwGoToMenu() {
        pwScreen = "menu";
        pwRenderScreen();
    }
    
    function pwPlaceOrder() {
        const name = document.getElementById("cust_name").value.trim();
        const phone = document.getElementById("cust_phone").value.trim();
        const addr = document.getElementById("cust_addr").value.trim();
        
        if (!name || !phone) {
            alert("Name aur Mobile number fill karna mandatory hai!");
            return;
        }
        
        if (phone.length < 10) {
            alert("Please enter a valid 10-digit mobile number!");
            return;
        }
        
        const itemsList = [];
        for (let k in pwCart) {
            itemsList.push({ name: k, qty: pwCart[k].qty });
        }
        
        const payBtn = document.querySelector("#pwDrBody .pw-footer-btn");
        payBtn.disabled = true;
        payBtn.textContent = "Processing...";
        
        fetch("../api/orders.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
                action: "create_order",
                page_id: pwPageId,
                customer_name: name,
                customer_phone: phone,
                delivery_address: addr,
                items: itemsList
            })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success && data.order_id) {
                const options = {
                    key: pwRzpKey,
                    amount: data.amount * 100,
                    currency: "INR",
                    name: pwBizName,
                    description: "Order Checkout via PosterWall",
                    order_id: data.order_id,
                    handler: function(response) {
                        pwVerifyPayment(response.razorpay_order_id, response.razorpay_payment_id, response.razorpay_signature, name, phone, addr);
                    },
                    prefill: {
                        name: name,
                        contact: phone
                    },
                    theme: {
                        color: "#FF6B00"
                    },
                    modal: {
                        ondismiss: function() {
                            payBtn.disabled = false;
                            payBtn.innerHTML = `<span>Pay & Confirm</span><span>₹${pwGetCartTotal()} →</span>`;
                        }
                    }
                };
                const rzp = new Razorpay(options);
                rzp.open();
            } else {
                alert(data.msg || "Order place karne mein koi error aayi. Please dobara try karein.");
                payBtn.disabled = false;
                payBtn.innerHTML = `<span>Pay & Confirm</span><span>₹${pwGetCartTotal()} →</span>`;
            }
        })
        .catch(err => {
            console.error(err);
            alert("Network error. Please try again.");
            payBtn.disabled = false;
            payBtn.innerHTML = `<span>Pay & Confirm</span><span>₹${pwGetCartTotal()} →</span>`;
        });
    }
    
    function pwVerifyPayment(orderId, paymentId, signature, name, phone, addr) {
        const body = document.getElementById("pwDrBody");
        body.innerHTML = `<div style="text-align:center;padding:40px 0;"><i class="fas fa-spinner fa-spin" style="font-size:2rem;color:#FF6B00;"></i><p style="margin-top:12px;">Payment verify ho raha hai...</p></div>`;
        
        fetch("../api/orders.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
                action: "verify_payment",
                razorpay_order_id: orderId,
                razorpay_payment_id: paymentId,
                razorpay_signature: signature
            })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                pwScreen = "success";
                document.getElementById("pwDrTitle").textContent = "Order Confirmed!";
                
                let summaryHtml = "";
                for (let k in pwCart) {
                    summaryHtml += `<div>${k} &times; ${pwCart[k].qty}</div>`;
                }
                
                body.innerHTML = `
                <div class="pw-success-screen">
                    <div class="pw-success-icon"><i class="fas fa-check"></i></div>
                    <h3 style="font-weight:700;margin-bottom:8px;">Order Placed Successfully!</h3>
                    <p style="color:#666;font-size:0.85rem;line-height:1.5;">Aapka order receive ho gaya hai. Aapki payment verify ho gayi hai.</p>
                    
                    <div class="pw-summary-box">
                        <strong>Order Details:</strong>
                        <div style="margin-top:8px;color:#444;">
                            ${summaryHtml}
                        </div>
                        <div style="margin-top:10px;font-weight:700;color:#FF6B00;border-top:1px solid rgba(0,0,0,0.06);padding-top:8px;">
                            Total Paid: ₹${pwGetCartTotal()}
                        </div>
                        <div style="margin-top:12px;font-size:0.75rem;color:#888;">
                            <strong>Delivery Address/Table:</strong><br>${addr || "Not Specified"}
                        </div>
                    </div>
                    
                    <button class="pw-btn-secondary" style="background:#FF6B00;color:white;" onclick="pwResetCart()">Done / Close</button>
                </div>`;
            } else {
                alert("Payment Verification Failed. Please contact support.");
                pwScreen = "checkout";
                pwRenderScreen();
            }
        })
        .catch(err => {
            console.error(err);
            alert("Network error during verification.");
            pwScreen = "checkout";
            pwRenderScreen();
        });
    }
    
    function pwResetCart() {
        pwCart = {};
        pwScreen = "menu";
        pwToggleDrawer();
    }
    </script>
    ';
    
    if (stripos($html, '</body>') !== false) {
        $html = str_ireplace('</body>', $injectedHtml . '</body>', $html);
    } else {
        $html .= $injectedHtml;
    }
}

echo $html;
