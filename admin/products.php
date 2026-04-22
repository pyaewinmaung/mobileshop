<?php
// admin/products.php
require_once '../config/db.php';

$action = $_GET['action'] ?? 'list';
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_product'])) {
        $brand = trim($_POST['brand']);
        $model = trim($_POST['model_name']);
        $desc = trim($_POST['description']);
        $price = floatval($_POST['price']);
        $stock = intval($_POST['stock']);

        $stmt = $conn->prepare("INSERT INTO products (brand, model_name, description, price, stock_quantity) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssdi", $brand, $model, $desc, $price, $stock);
        if ($stmt->execute()) {
            $product_id = $conn->insert_id;

            // Handle multiple images
            if (isset($_FILES['images']) && count($_FILES['images']['name']) > 0) {
                $uploadDir = '../assets/uploads/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                $stmtImg = $conn->prepare("INSERT INTO product_images (product_id, image_url, is_primary) VALUES (?, ?, ?)");

                foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
                    if (!empty($tmp_name)) {
                        $filename = time() . '_' . basename($_FILES['images']['name'][$key]);
                        $targetFilePath = $uploadDir . $filename;
                        if (move_uploaded_file($tmp_name, $targetFilePath)) {
                            // First image is primary
                            $is_primary = ($key === 0) ? 1 : 0;
                            // Store absolute web url
                            $db_url = '/mobileshop/assets/uploads/' . $filename;
                            $stmtImg->bind_param("isi", $product_id, $db_url, $is_primary);
                            $stmtImg->execute();
                        }
                    }
                }
            }
            $msg = "Product added successfully.";
        }
    } elseif (isset($_POST['edit_product'])) {
        $id = intval($_POST['product_id']);
        $brand = trim($_POST['brand']);
        $model = trim($_POST['model_name']);
        $desc = trim($_POST['description']);
        $price = floatval($_POST['price']);
        $stock = intval($_POST['stock']);

        $stmt = $conn->prepare("UPDATE products SET brand=?, model_name=?, description=?, price=?, stock_quantity=? WHERE id=?");
        $stmt->bind_param("sssdii", $brand, $model, $desc, $price, $stock, $id);
        if ($stmt->execute()) {
            // Handle multiple images
            if (isset($_FILES['images']) && count($_FILES['images']['name']) > 0 && !empty($_FILES['images']['tmp_name'][0])) {
                $uploadDir = '../assets/uploads/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                // Remove existing images to replace them
                $conn->query("DELETE FROM product_images WHERE product_id = " . intval($id));

                $stmtImg = $conn->prepare("INSERT INTO product_images (product_id, image_url, is_primary) VALUES (?, ?, ?)");

                foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
                    if (!empty($tmp_name)) {
                        $filename = time() . '_' . basename($_FILES['images']['name'][$key]);
                        $targetFilePath = $uploadDir . $filename;
                        if (move_uploaded_file($tmp_name, $targetFilePath)) {
                            // First image is primary
                            $is_primary = ($key === 0) ? 1 : 0;
                            // Store absolute web url
                            $db_url = '/mobileshop/assets/uploads/' . $filename;
                            $stmtImg->bind_param("isi", $id, $db_url, $is_primary);
                            $stmtImg->execute();
                        }
                    }
                }
            }
            $msg = "Product updated successfully.";
        }
    } elseif (isset($_POST['delete_product'])) {
        $id = intval($_POST['product_id']);
        $conn->query("DELETE FROM products WHERE id = $id");
        $msg = "Product deleted.";
    }
}

include '../includes/admin_header.php';
?>

<?php if ($action === 'list'): ?>
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-900">Manage Products</h2>
        <a href="?action=add" class="bg-brand-600 hover:bg-brand-700 text-white px-4 py-2 rounded-md transition-colors font-medium shadow-sm">
            + Add New Product
        </a>
    </div>

    <?php if ($msg): ?>
        <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-6">
            <p class="text-sm text-green-700"><?php echo htmlspecialchars($msg); ?></p>
        </div>
    <?php endif; ?>

    <div class="bg-white shadow-sm rounded-xl overflow-hidden border border-gray-200">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Image</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Model / Brand</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stock</th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php
                $products = $conn->query("SELECT p.*, (SELECT image_url FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as image FROM products p ORDER BY id DESC");
                while ($p = $products->fetch_assoc()):
                ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <img src="<?php echo htmlspecialchars($p['image'] ?? 'https://via.placeholder.com/50'); ?>" class="h-12 w-12 rounded object-cover border border-gray-200 bg-gray-50">
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-bold text-gray-900"><?php echo htmlspecialchars($p['model_name']); ?></div>
                            <div class="text-sm text-gray-500"><?php echo htmlspecialchars($p['brand']); ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo formatPrice($p['price']); ?></td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $p['stock_quantity'] > 5 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                <?php echo $p['stock_quantity']; ?> in stock
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <form method="POST" action="products.php" class="inline" onsubmit="return confirm('Are you sure?');">
                                <input type="hidden" name="delete_product" value="1">
                                <input type="hidden" name="product_id" value="<?php echo $p['id']; ?>">
                                <a href="?action=edit&product_id=<?php echo $p['id']; ?>" class="text-blue-600 hover:text-blue-900 bg-blue-50 hover:bg-blue-100 px-3 py-1.5 rounded transition-colors inline-block align-middle mr-1">Edit</a>
                                <button type="submit" class="text-red-600 hover:text-red-900 bg-red-50 hover:bg-red-100 px-3 py-1 rounded transition-colors align-middle">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

<?php elseif ($action === 'add'): ?>
    <div class="flex items-center mb-6">
        <a href="?action=list" class="text-gray-500 hover:text-gray-900 mr-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
        </a>
        <h2 class="text-2xl font-bold text-gray-900">Add New Product</h2>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <form action="products.php" method="POST" enctype="multipart/form-data" class="p-8">
            <input type="hidden" name="add_product" value="1">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Brand</label>
                    <input type="text" name="brand" required class="block w-full border border-gray-300 rounded-md shadow-sm focus:ring-brand-500 focus:border-brand-500 sm:text-sm p-3">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Model Name</label>
                    <input type="text" name="model_name" required class="block w-full border border-gray-300 rounded-md shadow-sm focus:ring-brand-500 focus:border-brand-500 sm:text-sm p-3">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Price ($)</label>
                    <input type="number" step="0.01" name="price" required class="block w-full border border-gray-300 rounded-md shadow-sm focus:ring-brand-500 focus:border-brand-500 sm:text-sm p-3">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Initial Stock</label>
                    <input type="number" name="stock" required class="block w-full border border-gray-300 rounded-md shadow-sm focus:ring-brand-500 focus:border-brand-500 sm:text-sm p-3">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea name="description" rows="4" class="block w-full border border-gray-300 rounded-md shadow-sm focus:ring-brand-500 focus:border-brand-500 sm:text-sm p-3"></textarea>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Product Images (First image will be primary)</label>
                    <input type="file" name="images[]" multiple accept="image/*" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100 p-2 border border-dashed border-gray-300 rounded-md cursor-pointer">
                    <p class="mt-1 text-xs text-gray-500">You can upload multiple images at once.</p>
                </div>
            </div>
            <div class="flex justify-end pt-4 border-t border-gray-100">
                <button type="submit" class="bg-brand-600 text-white px-6 py-2 rounded-md font-medium hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-500 transition-colors shadow-sm">
                    Save Product
                </button>
            </div>
        </form>
    </div>
    <?php elseif ($action === 'edit' && isset($_GET['product_id'])):
    $edit_id = intval($_GET['product_id']);
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $edit_product = $stmt->get_result()->fetch_assoc();
    if ($edit_product):
    ?>
        <div class="flex items-center mb-6">
            <a href="?action=list" class="text-gray-500 hover:text-gray-900 mr-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </a>
            <h2 class="text-2xl font-bold text-gray-900">Edit Product Specification</h2>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <form action="products.php?action=list" method="POST" enctype="multipart/form-data" class="p-8">
                <input type="hidden" name="edit_product" value="1">
                <input type="hidden" name="product_id" value="<?php echo $edit_product['id']; ?>">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Brand</label>
                        <input type="text" name="brand" value="<?php echo htmlspecialchars($edit_product['brand']); ?>" required class="block w-full border border-gray-300 rounded-md shadow-sm focus:ring-brand-500 focus:border-brand-500 sm:text-sm p-3">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Model Name</label>
                        <input type="text" name="model_name" value="<?php echo htmlspecialchars($edit_product['model_name']); ?>" required class="block w-full border border-gray-300 rounded-md shadow-sm focus:ring-brand-500 focus:border-brand-500 sm:text-sm p-3">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Price ($)</label>
                        <input type="number" step="0.01" name="price" value="<?php echo $edit_product['price']; ?>" required class="block w-full border border-gray-300 rounded-md shadow-sm focus:ring-brand-500 focus:border-brand-500 sm:text-sm p-3">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Stock</label>
                        <input type="number" name="stock" value="<?php echo $edit_product['stock_quantity']; ?>" required class="block w-full border border-gray-300 rounded-md shadow-sm focus:ring-brand-500 focus:border-brand-500 sm:text-sm p-3">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <textarea name="description" rows="4" class="block w-full border border-gray-300 rounded-md shadow-sm focus:ring-brand-500 focus:border-brand-500 sm:text-sm p-3"><?php echo htmlspecialchars($edit_product['description']); ?></textarea>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Update Images (Leave blank to keep existing images)</label>
                        <input type="file" name="images[]" multiple accept="image/*" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100 p-2 border border-dashed border-gray-300 rounded-md cursor-pointer">
                    </div>
                </div>
                <div class="flex justify-end pt-4 border-t border-gray-100">
                    <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-md font-medium hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors shadow-sm">
                        Update Specification
                    </button>
                </div>
            </form>
        </div>
    <?php else: ?>
        <div class="bg-red-50 p-4 border-l-4 border-red-500 text-red-700">Product not found.</div>
<?php
    endif;
endif; ?>

</main>
</div>
</body>

</html>