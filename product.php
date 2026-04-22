<?php
// product.php
require_once 'config/db.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
    header("Location: index.php");
    exit;
}

// Fetch images
$stmtImg = $conn->prepare("SELECT image_url, is_primary FROM product_images WHERE product_id = ? ORDER BY is_primary DESC");
$stmtImg->bind_param("i", $id);
$stmtImg->execute();
$imagesResult = $stmtImg->get_result();
$images = [];
while ($img = $imagesResult->fetch_assoc()) {
    $images[] = $img;
}
$mainImage = !empty($images) ? $images[0]['image_url'] : 'https://via.placeholder.com/600x600?text=No+Image';

include 'includes/header.php';
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="lg:grid lg:grid-cols-2 lg:gap-x-8 lg:p-8">
            <!-- Image Gallery -->
            <div class="p-6 lg:p-0 flex flex-col items-center">
                <div class="w-full aspect-w-1 aspect-h-1 bg-gray-50 rounded-2xl overflow-hidden flex items-center justify-center p-8 mb-6 border border-gray-100">
                    <img id="main-product-image" src="<?php echo htmlspecialchars($mainImage); ?>" alt="<?php echo htmlspecialchars($product['model_name']); ?>" class="w-full h-96 object-contain">
                </div>

                <?php if (count($images) > 1): ?>
                    <div class="flex gap-4 overflow-x-auto pb-4 px-2 w-full justify-center">
                        <?php foreach ($images as $idx => $img): ?>
                            <button type="button" onclick="document.getElementById('main-product-image').src='<?php echo htmlspecialchars($img['image_url']); ?>'" class="flex-shrink-0 w-24 h-24 rounded-lg border-2 border-transparent hover:border-brand-500 focus:outline-none focus:border-brand-500 overflow-hidden bg-gray-50 p-2 transition-colors">
                                <img src="<?php echo htmlspecialchars($img['image_url']); ?>" class="w-full h-full object-contain">
                            </button>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Product Info -->
            <div class="p-8 lg:p-0 lg:pt-8 flex flex-col h-full lg:pl-8 lg:border-l border-gray-100">
                <h2 class="text-sm font-bold text-brand-600 tracking-wider uppercase mb-2"><?php echo htmlspecialchars($product['brand']); ?></h2>
                <h1 class="text-4xl font-extrabold text-gray-900 tracking-tight mb-4"><?php echo htmlspecialchars($product['model_name']); ?></h1>

                <div class="mb-6 flex items-center gap-4">
                    <p class="text-3xl tracking-tight text-gray-900 font-bold"><?php echo formatPrice($product['price']); ?></p>
                    <?php if ($product['stock_quantity'] > 0): ?>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                            In Stock (<?php echo $product['stock_quantity']; ?> available)
                        </span>
                    <?php else: ?>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                            Out of Stock
                        </span>
                    <?php endif; ?>
                </div>

                <div class="prose prose-sm text-gray-500 mb-8 flex-grow">
                    <p class="text-lg leading-relaxed"><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                </div>

                <?php if ($product['stock_quantity'] > 0): ?>
                    <form action="cart.php" method="POST" class="mt-auto">
                        <input type="hidden" name="action" value="add">
                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">

                        <div class="flex items-end gap-4">
                            <div class="w-30">
                                <label for="quantity" class="block text-sm font-medium text-gray-700 mb-1">Quantity</label>
                                <div class="flex items-center border border-gray-400 rounded-md overflow-hidden mr-4 h-9 w-32 bg-white shadow-sm">
                                    <button type="button" onclick="handleQuantity('down')" class="w-9 h-full text-gray-700 hover:bg-gray-100 flex items-center justify-center text-lg border-r border-gray-400 transition-colors cursor-pointer select-none outline-none font-medium">&minus;</button>
                                    <input type="number" id="quantity-<?php echo $id; ?>" name="quantity" value="1" min="1" max="<?php echo $product['stock_quantity']; ?>" class="flex-1 w-full h-full text-center border-none focus:ring-0 text-base font-medium text-gray-900 px-0 m-0 [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none" readonly>
                                    <button type="button" onclick="handleQuantity('up')" class="w-9 h-full text-gray-700 hover:bg-gray-100 flex items-center justify-center text-lg border-l border-gray-400 transition-colors cursor-pointer select-none outline-none font-medium">&#43;</button>
                                </div>
                                <p id="stock-warning" class="text-xs text-red-500 mt-2 font-medium hidden">Maximum available stock reached!</p>

                                <script>
                                    function handleQuantity(action) {
                                        const input = document.getElementById('quantity-<?php echo $id; ?>');
                                        const warning = document.getElementById('stock-warning');
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
                            </div>
                            <button type="submit" class="flex-grow bg-brand-600 border border-transparent rounded-md py-3 px-8 flex items-center justify-center text-base font-medium text-white hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-brand-500 transition-colors shadow-lg shadow-brand-500/30">
                                <svg xmlns="http://www.w3.org/-2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                Add to Cart
                            </button>
                        </div>
                    </form>
                <?php else: ?>
                    <div class="mt-auto bg-gray-100 rounded-md py-4 px-8 text-center text-gray-500 font-medium">
                        Currently unavailable
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>