<?php
// admin/orders.php
require_once '../config/db.php';

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $order_id = intval($_POST['order_id']);
    $status = $_POST['status'];
    $valid_statuses = ['pending', 'shipped', 'delivered', 'cancelled'];
    
    if (in_array($status, $valid_statuses)) {
        $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $order_id);
        if ($stmt->execute()) {
            $msg = "Order #$order_id status updated to $status.";
        }
    }
}

include '../includes/admin_header.php';
?>

<div class="flex justify-between items-center mb-6">
    <h2 class="text-2xl font-bold text-gray-900">Manage Orders</h2>
</div>

<?php if ($msg): ?>
    <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-6">
        <p class="text-sm text-green-700"><?php echo htmlspecialchars($msg); ?></p>
    </div>
<?php endif; ?>

<div class="space-y-6">
    <?php 
    $orders = $conn->query("SELECT o.id, o.total_amount, o.status, o.order_date, u.username, u.email FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.order_date DESC");
    if ($orders->num_rows > 0):
        while($order = $orders->fetch_assoc()): 
    ?>
    <div class="bg-white shadow-sm rounded-xl border border-gray-200 overflow-hidden">
        <div class="bg-gray-50 border-b border-gray-200 px-6 py-4 flex flex-wrap items-center justify-between gap-4">
            <div>
                <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Order #<?php echo $order['id']; ?></p>
                <p class="text-sm text-gray-900"><?php echo date('M d, Y h:i A', strtotime($order['order_date'])); ?></p>
            </div>
            <div>
                <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Customer</p>
                <p class="text-sm text-gray-900"><?php echo htmlspecialchars($order['username']); ?> (<?php echo htmlspecialchars($order['email']); ?>)</p>
            </div>
            <div>
                <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Total Amount</p>
                <p class="text-sm font-bold text-brand-600"><?php echo formatPrice($order['total_amount']); ?></p>
            </div>
            <div>
                <form action="orders.php" method="POST" class="flex items-center gap-2">
                    <input type="hidden" name="update_status" value="1">
                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                    <select name="status" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-brand-500 focus:border-brand-500 sm:text-sm pl-3 pr-8 py-2 border bg-white">
                        <option value="pending" <?php echo $order['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="shipped" <?php echo $order['status'] == 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                        <option value="delivered" <?php echo $order['status'] == 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                        <option value="cancelled" <?php echo $order['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                    <button type="submit" class="bg-gray-800 text-white px-3 py-2 rounded-md text-sm font-medium hover:bg-gray-700 transition">Update</button>
                </form>
            </div>
        </div>
        
        <?php 
        $stmtItems = $conn->prepare("SELECT oi.quantity, oi.price_at_purchase, p.model_name, p.brand FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
        $stmtItems->bind_param("i", $order['id']);
        $stmtItems->execute();
        $items = $stmtItems->get_result();
        ?>
        <div class="px-6 py-4">
            <h4 class="text-sm font-semibold text-gray-700 mb-3 border-b border-gray-100 pb-2">Order Items</h4>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                <?php while($item = $items->fetch_assoc()): ?>
                    <div class="flex items-center bg-gray-50 p-3 rounded-lg border border-gray-100">
                        <div class="flex-grow">
                            <p class="text-sm font-bold text-gray-900"><?php echo htmlspecialchars($item['model_name']); ?></p>
                            <p class="text-xs text-gray-500"><?php echo htmlspecialchars($item['brand']); ?></p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-medium text-gray-900"><?php echo formatPrice($item['price_at_purchase']); ?></p>
                            <p class="text-xs text-gray-500">Qty: <?php echo $item['quantity']; ?></p>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>
    <?php 
        endwhile;
    else: 
    ?>
        <div class="bg-white shadow-sm rounded-xl border border-gray-200 p-12 text-center text-gray-500">
            No orders found.
        </div>
    <?php endif; ?>
</div>

</main>
</div>
</body>
</html>
