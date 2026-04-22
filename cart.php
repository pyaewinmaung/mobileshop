<?php
// cart.php
require_once 'config/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;

    if ($action === 'add' && $product_id > 0) {
        $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;

        // Fetch product to ensure it exists and check stock + get details
        $stmt = $conn->prepare("SELECT id, brand, model_name, price, stock_quantity, (SELECT image_url FROM product_images WHERE product_id = products.id AND is_primary = 1 LIMIT 1) as image FROM products WHERE id = ?");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $product = $stmt->get_result()->fetch_assoc();

        if ($product && $product['stock_quantity'] >= $quantity) {
            // If already in cart, just update quantity
            if (isset($_SESSION['cart'][$product_id])) {
                $_SESSION['cart'][$product_id]['quantity'] += $quantity;
            } else {
                $_SESSION['cart'][$product_id] = [
                    'id' => $product['id'],
                    'brand' => $product['brand'],
                    'model_name' => $product['model_name'],
                    'price' => $product['price'],
                    'quantity' => $quantity,
                    'image' => $product['image']
                ];
            }
        }
        header("Location: cart.php");
        exit;
    }

    if ($action === 'update' && $product_id > 0) {
        $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;
        if ($quantity > 0) {
            $_SESSION['cart'][$product_id]['quantity'] = $quantity;
        } else {
            unset($_SESSION['cart'][$product_id]);
        }
        header("Location: cart.php");
        exit;
    }

    if ($action === 'remove' && $product_id > 0) {
        unset($_SESSION['cart'][$product_id]);
        header("Location: cart.php");
        exit;
    }
}

include 'includes/header.php';

$total = 0;
$totalQuantity = 0;
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <h1 class="text-3xl font-extrabold text-gray-900 mb-8">Shopping Cart</h1>

    <?php if (empty($_SESSION['cart'])): ?>
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-12 text-center">
            <svg xmlns="http://www.w3.org/-2000/svg" class="h-16 w-16 mx-auto text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
            </svg>
            <h2 class="text-2xl font-bold text-gray-900 mb-2">Your cart is empty</h2>
            <p class="text-gray-500 mb-6">Looks like you haven't added anything to your cart yet.</p>
            <a href="index.php" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-brand-600 hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-500">
                Continue Shopping
            </a>
        </div>
    <?php else: ?>
        <div class="lg:grid lg:grid-cols-12 lg:gap-x-12 lg:items-start">
            <div class="lg:col-span-8">
                <ul role="list" class="border-t border-b border-gray-200 divide-y divide-gray-200">
                    <?php foreach ($_SESSION['cart'] as $id => $item):
                        $stmtStock = $conn->prepare("SELECT stock_quantity FROM products WHERE id = ?");
                        $stmtStock->bind_param("i", $id);
                        $stmtStock->execute();
                        $maxStock = $stmtStock->get_result()->fetch_assoc()['stock_quantity'] ?? 0;

                        $itemTotal = $item['price'] * $item['quantity'];
                        $total += $itemTotal;
                        $totalQuantity += $item['quantity'];
                    ?>
                        <li class="flex py-6 sm:py-10">
                            <div class="flex-shrink-0">
                                <img src="<?php echo htmlspecialchars($item['image'] ?? 'https://via.placeholder.com/150'); ?>" alt="<?php echo htmlspecialchars($item['model_name']); ?>" class="w-24 h-24 rounded-md object-contain object-center sm:w-32 sm:h-32 bg-gray-50 p-2 border border-gray-100">
                            </div>

                            <div class="ml-4 flex-1 flex flex-col justify-between sm:ml-6">
                                <div class="relative pr-9 sm:grid sm:grid-cols-2 sm:gap-x-6 sm:pr-0">
                                    <div>
                                        <div class="flex justify-between">
                                            <h3 class="text-sm">
                                                <a href="product.php?id=<?php echo $id; ?>" class="font-bold text-gray-700 hover:text-gray-800">
                                                    <?php echo htmlspecialchars($item['model_name']); ?>
                                                </a>
                                            </h3>
                                        </div>
                                        <p class="mt-1 text-sm font-medium text-brand-600"><?php echo htmlspecialchars($item['brand']); ?></p>
                                        <p class="mt-1 text-sm font-bold text-gray-900"><?php echo formatPrice($item['price']); ?></p>
                                    </div>

                                    <div class="mt-4 sm:mt-0 sm:pr-9">
                                        <form action="cart.php" method="POST" class="flex items-center">
                                            <input type="hidden" name="action" value="update">
                                            <input type="hidden" name="product_id" value="<?php echo $id; ?>">
                                            <label for="quantity-<?php echo $id; ?>" class="sr-only">Quantity</label>
                                            <div class="flex items-center border border-gray-400 rounded-md overflow-hidden mr-4 h-9 w-28 bg-white shadow-sm">
                                                <button type="button" onclick="handleQuantity('down', <?php echo $id; ?>)" class="w-9 h-full text-gray-700 hover:bg-gray-100 flex items-center justify-center text-lg border-r border-gray-400 transition-colors cursor-pointer select-none outline-none font-medium">&minus;</button>
                                                <input type="number" id="quantity-<?php echo $id; ?>" name="quantity" value="<?php echo $item['quantity']; ?>" min="1" max="<?php echo $maxStock; ?>" class="flex-1 w-full h-full text-center border-none focus:ring-0 text-base font-medium text-gray-900 px-0 m-0 [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none" readonly>
                                                <button type="button" onclick="handleQuantity('up', <?php echo $id; ?>)" class="w-9 h-full text-gray-700 hover:bg-gray-100 flex items-center justify-center text-lg border-l border-gray-400 transition-colors cursor-pointer select-none outline-none font-medium">&#43;</button>
                                            </div>
                                            <button type="submit" class="text-sm font-medium text-brand-600 hover:text-brand-500 bg-brand-50 px-3 py-1.5 rounded-md hover:bg-brand-100 transition-colors">Update</button>
                                        </form>
                                        <p id="stock-warning-<?php echo $id; ?>" class="text-xs text-red-500 mt-2 font-medium hidden">Maximum available stock reached!</p>

                                        <div class="absolute top-0 right-0">
                                            <form action="cart.php" method="POST">
                                                <input type="hidden" name="action" value="remove">
                                                <input type="hidden" name="product_id" value="<?php echo $id; ?>">
                                                <button type="submit" class="-m-2 p-2 inline-flex text-gray-400 hover:text-red-500 transition-colors">
                                                    <span class="sr-only">Remove</span>
                                                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                <p class="mt-4 flex text-sm text-gray-700 space-x-2">
                                    Subtotal: <strong class="ml-1 text-gray-900"><?php echo formatPrice($itemTotal); ?></strong>
                                </p>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <!-- Order summary -->
            <section aria-labelledby="summary-heading" class="mt-16 bg-gray-50 rounded-lg px-4 py-6 sm:p-6 lg:p-8 lg:mt-0 lg:col-span-4 border border-gray-100 shadow-sm">
                <h2 id="summary-heading" class="text-lg font-medium text-gray-900">Order summary</h2>

                <dl class="mt-6 space-y-4">
                    <div class="flex items-center justify-between">
                        <dt class="text-sm text-gray-600">Quantity</dt>
                        <dd class="text-sm font-medium text-gray-900"><?php echo $totalQuantity; ?></dd>
                    </div>
                    <div class="flex items-center justify-between">
                        <dt class="text-sm text-gray-600">Subtotal</dt>
                        <dd class="text-sm font-medium text-gray-900"><?php echo formatPrice($total); ?></dd>
                    </div>
                    <div class="border-t border-gray-200 pt-4 flex items-center justify-between">
                        <dt class="text-sm text-gray-600">Shipping</dt>
                        <dd class="text-sm font-medium text-green-600">Free</dd>
                    </div>
                    <div class="border-t border-gray-200 pt-4 flex items-center justify-between">
                        <dt class="text-base font-bold text-gray-900">Order total</dt>
                        <dd class="text-base font-bold text-gray-900"><?php echo formatPrice($total); ?></dd>
                    </div>
                </dl>

                <div class="mt-6">
                    <?php if (isLoggedIn()): ?>
                        <a href="checkout.php" class="w-full bg-brand-600 border border-transparent rounded-md shadow-sm py-3 px-4 text-base font-medium text-white hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-500 flex justify-center items-center transition-colors">
                            Checkout
                        </a>
                    <?php else: ?>
                        <a href="auth/login.php" class="w-full bg-gray-800 border border-transparent rounded-md shadow-sm py-3 px-4 text-base font-medium text-white hover:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-900 flex justify-center items-center transition-colors">
                            Login to Checkout
                        </a>
                    <?php endif; ?>
                </div>
            </section>
        </div>
    <?php endif; ?>
</div>

<script>
    function handleQuantity(action, id) {
        const input = document.getElementById('quantity-' + id);
        const warning = document.getElementById('stock-warning-' + id);
        const max = parseInt(input.getAttribute('max'));

        if (action === 'up') {
            if (parseInt(input.value) < max) {
                input.stepUp();
                warning.classList.add('hidden');
            } else {
                warning.classList.remove('hidden');
            }
        } else {
            input.stepDown();
            warning.classList.add('hidden');
        }
    }
</script>

<?php include 'includes/footer.php'; ?>