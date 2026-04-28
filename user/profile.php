<?php
// user/profile.php
require_once '../config/db.php';
require_once '../includes/functions.php';
requireLogin();

$user_id = $_SESSION['user_id'];
$success = isset($_GET['order_success']) ? "Order placed successfully! Thank you for your purchase." : "";

$tab = $_GET['tab'] ?? 'orders';
$pwd_error = '';
$pwd_success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $old_pwd = $_POST['old_password'] ?? '';
    $new_pwd = $_POST['new_password'] ?? '';
    $confirm_pwd = $_POST['confirm_password'] ?? '';

    if (empty($old_pwd) || empty($new_pwd) || empty($confirm_pwd)) {
        $pwd_error = "All fields are required.";
    } elseif ($new_pwd !== $confirm_pwd) {
        $pwd_error = "New passwords do not match.";
    } elseif (strlen($new_pwd) < 6) {
        $pwd_error = "New password must be at least 6 characters long.";
    } else {
        $stmt = $conn->prepare("SELECT password_hash FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $user_row = $stmt->get_result()->fetch_assoc();

        if ($user_row && password_verify($old_pwd, $user_row['password_hash'])) {
            $new_hash = password_hash($new_pwd, PASSWORD_DEFAULT);
            $update_stmt = $conn->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
            $update_stmt->bind_param("si", $new_hash, $user_id);
            if ($update_stmt->execute()) {
                // Invalidate current session and force re-login
                session_unset();
                session_destroy();
                session_start();
                $_SESSION['login_message'] = "Password changed successfully, please log in again.";
                header("Location: ../auth/login.php");
                exit;
            } else {
                $pwd_error = "Failed to update password. Please try again.";
            }
        } else {
            $pwd_error = "Current password is incorrect.";
        }
    }
    $tab = 'password';
}

// Fetch orders
$stmt = $conn->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY order_date DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$ordersResult = $stmt->get_result();

include '../includes/header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <?php if ($success): ?>
        <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-8">
            <p class="text-sm text-green-700 font-bold"><?php echo htmlspecialchars($success); ?></p>
        </div>
    <?php endif; ?>

    <div class="md:grid md:grid-cols-4 md:gap-8 border-t border-gray-200 py-8">
        <div class="md:col-span-1 mb-8 md:mb-0">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <div class="w-16 h-16 bg-brand-100 text-brand-600 rounded-full flex items-center justify-center text-2xl font-bold mb-4">
                    <?php echo strtoupper(substr($_SESSION['username'], 0, 1)); ?>
                </div>
                <h2 class="text-xl font-bold text-gray-900"><?php echo htmlspecialchars($_SESSION['username']); ?></h2>
                <nav class="space-y-2 border-t border-gray-100 pt-4">
                    <a href="?tab=orders" class="block px-3 py-2 <?php echo $tab === 'orders' ? 'bg-brand-50 text-brand-600' : 'text-gray-600 hover:bg-gray-50'; ?> font-medium rounded-md transition-colors">Order History</a>
                    <a href="?tab=password" class="block px-3 py-2 <?php echo $tab === 'password' ? 'bg-brand-50 text-brand-600' : 'text-gray-600 hover:bg-gray-50'; ?> font-medium rounded-md transition-colors">Change Password</a>
                    <a href="../auth/logout.php" class="block px-3 py-2 text-red-600 hover:bg-red-50 font-medium rounded-md transition-colors">Logout</a>
                </nav>
            </div>
        </div>

        <div class="md:col-span-3">
            <?php if ($tab === 'password'): ?>
                <h3 class="text-2xl font-extrabold text-gray-900 mb-6">Change Password</h3>

                <?php if ($pwd_error): ?>
                    <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded-md">
                        <p class="text-sm text-red-700"><?php echo htmlspecialchars($pwd_error); ?></p>
                    </div>
                <?php endif; ?>
                <?php if ($pwd_success): ?>
                    <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-6 rounded-md">
                        <p class="text-sm text-green-700 font-bold"><?php echo htmlspecialchars($pwd_success); ?></p>
                    </div>
                <?php endif; ?>

                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden max-w-lg">
                    <form action="profile.php?tab=password" method="POST" class="p-6 sm:p-8 space-y-6">
                        <input type="hidden" name="change_password" value="1">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Current Password</label>
                            <input type="password" name="old_password" required class="block w-full border border-gray-300 rounded-md shadow-sm focus:ring-brand-500 focus:border-brand-500 sm:text-sm p-3">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                            <input type="password" name="new_password" required class="block w-full border border-gray-300 rounded-md shadow-sm focus:ring-brand-500 focus:border-brand-500 sm:text-sm p-3">
                            <p class="mt-1 text-xs text-gray-500">Must be at least 6 characters long.</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Confirm New Password</label>
                            <input type="password" name="confirm_password" required class="block w-full border border-gray-300 rounded-md shadow-sm focus:ring-brand-500 focus:border-brand-500 sm:text-sm p-3">
                        </div>
                        <div class="pt-2">
                            <button type="submit" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-md text-sm font-medium text-white bg-brand-600 hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-500 transition-colors">
                                Update Password
                            </button>
                        </div>
                    </form>
                </div>

            <?php else: ?>
                <h3 class="text-2xl font-extrabold text-gray-900 mb-6">Order History</h3>

                <?php if ($ordersResult && $ordersResult->num_rows > 0): ?>
                    <div class="space-y-6">
                        <?php while ($order = $ordersResult->fetch_assoc()): ?>
                            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                                <div class="bg-gray-50 border-b border-gray-100 p-4 sm:px-6 flex flex-wrap items-center justify-between gap-4">
                                    <div>
                                        <p class="text-sm text-gray-500 mb-1">Order Placed</p>
                                        <p class="font-medium text-gray-900"><?php echo date('M d, Y', strtotime($order['order_date'])); ?></p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500 mb-1">Total</p>
                                        <p class="font-medium text-gray-900"><?php echo formatPrice($order['total_amount']); ?></p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500 mb-1">Status</p>
                                        <span class="px-2 py-1 text-xs font-bold rounded-full 
                                    <?php
                                    echo $order['status'] === 'delivered' ? 'bg-green-100 text-green-800' : ($order['status'] === 'shipped' ? 'bg-blue-100 text-blue-800' : ($order['status'] === 'cancelled' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800'));
                                    ?>">
                                            <?php echo ucfirst($order['status']); ?>
                                        </span>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500 mb-1">Order #</p>
                                        <p class="font-medium text-gray-900"><?php echo $order['id']; ?></p>
                                    </div>
                                </div>

                                <?php
                                // Fetch order items
                                $stmtItems = $conn->prepare("SELECT oi.*, p.model_name, p.brand, (SELECT image_url FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as image FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
                                $stmtItems->bind_param("i", $order['id']);
                                $stmtItems->execute();
                                $itemsResult = $stmtItems->get_result();
                                ?>

                                <div class="p-4 sm:px-6 divide-y divide-gray-100">
                                    <?php while ($item = $itemsResult->fetch_assoc()): ?>
                                        <div class="py-4 flex gap-4">
                                            <img src="<?php echo htmlspecialchars($item['image'] ?? 'https://via.placeholder.com/100'); ?>" alt="" class="w-20 h-20 object-contain rounded bg-gray-50 border border-gray-100 p-1">
                                            <div>
                                                <h4 class="font-bold text-gray-900"><a href="../product.php?id=<?php echo $item['product_id']; ?>"><?php echo htmlspecialchars($item['model_name']); ?></a></h4>
                                                <p class="text-sm text-gray-500"><?php echo htmlspecialchars($item['brand']); ?></p>
                                                <p class="mt-1 text-sm"><span class="font-medium text-gray-900"><?php echo formatPrice($item['price_at_purchase']); ?></span> <span class="text-gray-500">x <?php echo $item['quantity']; ?></span></p>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="bg-gray-50 rounded-xl border border-gray-100 p-12 text-center text-gray-500">
                        You haven't placed any orders yet.
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>