<?php
// checkout.php
require_once 'config/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'includes/functions.php';
requireLogin();

if (empty($_SESSION['cart'])) {
    header("Location: cart.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$total = 0;
foreach ($_SESSION['cart'] as $item) {
    $total += $item['price'] * $item['quantity'];
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cc_num = trim($_POST['cc_num'] ?? '');
    
    // Simple mock validation
    if (strlen($cc_num) < 15) {
        $error = "Invalid credit card number. Please enter a valid 16-digit card.";
    } else {
        // Process Order Transaction
        $conn->begin_transaction();
        
        try {
            // Create Order
            $stmtOrder = $conn->prepare("INSERT INTO orders (user_id, total_amount, status) VALUES (?, ?, 'pending')");
            $stmtOrder->bind_param("id", $user_id, $total);
            $stmtOrder->execute();
            $order_id = $conn->insert_id;
            
            // Insert Items and deduct stock
            $stmtItem = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price_at_purchase) VALUES (?, ?, ?, ?)");
            $stmtStock = $conn->prepare("UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ? AND stock_quantity >= ?");
            
            foreach ($_SESSION['cart'] as $product_id => $item) {
                $qty = $item['quantity'];
                $price = $item['price'];
                
                // insert order item
                $stmtItem->bind_param("iiid", $order_id, $product_id, $qty, $price);
                $stmtItem->execute();
                
                // deduct stock
                $stmtStock->bind_param("iii", $qty, $product_id, $qty);
                $stmtStock->execute();
                
                if ($stmtStock->affected_rows === 0) {
                    throw new Exception("Not enough stock for " . $item['model_name']);
                }
            }
            
            $conn->commit();
            
            // Clear cart
            unset($_SESSION['cart']);
            
            header("Location: user/profile.php?order_success=1");
            exit;
            
        } catch (Exception $e) {
            $conn->rollback();
            $error = "Checkout failed: " . $e->getMessage();
        }
    }
}

include 'includes/header.php';
?>

<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <h1 class="text-3xl font-extrabold text-gray-900 mb-8">Checkout</h1>
    
    <?php if ($error): ?>
        <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-8">
            <p class="text-sm text-red-700"><?php echo htmlspecialchars($error); ?></p>
        </div>
    <?php endif; ?>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-8 border-b border-gray-100">
            <h2 class="text-lg font-bold text-gray-900 mb-4">Order Summary</h2>
            <div class="flex justify-between items-center mb-2">
                <span class="text-gray-600">Total Items:</span>
                <span class="font-medium"><?php echo getCartCount(); ?></span>
            </div>
            <div class="flex justify-between items-center text-xl">
                <span class="font-bold text-gray-900">Total to Pay:</span>
                <span class="font-bold text-brand-600"><?php echo formatPrice($total); ?></span>
            </div>
        </div>

        <form action="checkout.php" method="POST" class="p-8 bg-gray-50">
            <h2 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                </svg>
                Payment Details
            </h2>
            <p class="text-sm text-gray-500 mb-6">This is a mock payment gateway. Use any 16-digit dummy card.</p>

            <div class="space-y-4 max-w-sm">
                <div>
                    <label for="cc_name" class="block text-sm font-medium text-gray-700">Name on Card</label>
                    <input type="text" id="cc_name" name="cc_name" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-500 focus:border-brand-500 sm:text-sm p-3 border">
                </div>
                
                <div>
                    <label for="cc_num" class="block text-sm font-medium text-gray-700">Card Number</label>
                    <input type="text" id="cc_num" name="cc_num" placeholder="0000 0000 0000 0000" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-500 focus:border-brand-500 sm:text-sm p-3 border">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="cc_exp" class="block text-sm font-medium text-gray-700">Expiry</label>
                        <input type="text" id="cc_exp" name="cc_exp" placeholder="MM/YY" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-500 focus:border-brand-500 sm:text-sm p-3 border">
                    </div>
                    <div>
                        <label for="cc_cvv" class="block text-sm font-medium text-gray-700">CVV</label>
                        <input type="text" id="cc_cvv" name="cc_cvv" placeholder="123" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-500 focus:border-brand-500 sm:text-sm p-3 border">
                    </div>
                </div>
            </div>

            <div class="mt-8">
                <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-md text-base font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors">
                    Pay <?php echo formatPrice($total); ?> & Place Order
                </button>
            </div>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
