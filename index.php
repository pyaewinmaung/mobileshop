<?php
// index.php
require_once 'config/db.php';
include 'includes/header.php';

$search = $_GET['search'] ?? '';

// Fetch products with their primary image
if (!empty($search)) {
    $stmt = $conn->prepare("SELECT p.*, (SELECT image_url FROM product_images pi WHERE pi.product_id = p.id AND pi.is_primary = 1 LIMIT 1) as primary_image
                            FROM products p
                            WHERE p.model_name LIKE ? OR p.brand LIKE ?
                            ORDER BY p.created_at DESC");
    $searchTerm = '%' . $search . '%';
    $stmt->bind_param("ss", $searchTerm, $searchTerm);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $sql = "SELECT p.*, (SELECT image_url FROM product_images pi WHERE pi.product_id = p.id AND pi.is_primary = 1 LIMIT 1) as primary_image
            FROM products p
            ORDER BY p.created_at DESC";
    $result = $conn->query($sql);
}
$products = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 border-b border-gray-200">
    <div class="text-center mb-10">
        <h1 class="text-3xl font-extrabold text-gray-900 sm:text-5xl md:text-6xl tracking-tight mb-4 text-transparent bg-clip-text bg-gradient-to-r from-brand-600 to-indigo-600">
            Latest Premium Phones
        </h1>
        <p class="mt-4 max-w-2xl mx-auto text-xl text-gray-500">
            Discover the best high-end mobile devices directly from top brands.
        </p>
    </div>

    <!-- Search Form -->
    <div class="max-w-3xl mx-auto mb-12">
        <form action="index.php" method="GET" class="relative flex items-center w-full h-14 rounded-full focus-within:shadow-md bg-white border border-gray-200 overflow-hidden transition-shadow">
            <div class="grid place-items-center h-full w-14 text-gray-400">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
            <input
                class="peer h-full w-full outline-none text-base text-gray-700 pr-2 placeholder-gray-400 bg-transparent"
                type="text"
                id="search"
                name="search"
                placeholder="Search for phones by brand or model..."
                value="<?php echo htmlspecialchars($search); ?>" />
            <button type="submit" class="bg-brand-600 hover:bg-brand-700 text-white px-8 h-full font-medium transition-colors">
                Search
            </button>
        </form>
        <?php if (!empty($search)): ?>
            <div class="mt-4 flex items-center justify-between px-2">
                <p class="text-sm text-gray-500">Showing results for <span class="font-bold text-gray-900">"<?php echo htmlspecialchars($search); ?>"</span></p>
                <a href="index.php" class="text-sm font-medium text-brand-600 hover:text-brand-800">Clear filters</a>
            </div>
        <?php endif; ?>
    </div>

    <!-- Product Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
        <?php foreach ($products as $p): ?>
            <div class="bg-white rounded-2xl shadow-sm hover:shadow-xl transition-all duration-300 border border-gray-100 overflow-hidden group flex flex-col h-full cursor-pointer relative" onclick="window.location.href='product.php?id=<?php echo $p['id']; ?>'">
                <!-- Image container with fixed aspect ratio -->
                <div class="aspect-w-1 aspect-h-1 w-full overflow-hidden bg-gray-50 flex items-center justify-center p-6 relative">
                    <?php if ($p['stock_quantity'] <= 0): ?>
                        <div class="absolute top-4 right-4 bg-red-500 text-white text-xs font-bold px-3 py-1 rounded-full z-10">Out of Stock</div>
                    <?php endif; ?>
                    <img src="<?php echo htmlspecialchars($p['primary_image'] ?? 'https://via.placeholder.com/400x400?text=No+Image'); ?>"
                        alt="<?php echo htmlspecialchars($p['model_name']); ?>"
                        class="h-64 w-full object-contain object-center group-hover:scale-105 transition-transform duration-500">
                </div>

                <div class="p-6 flex flex-col flex-grow">
                    <p class="text-xs text-brand-600 font-bold uppercase tracking-wider mb-1"><?php echo htmlspecialchars($p['brand']); ?></p>
                    <h3 class="text-xl font-bold text-gray-900 mb-2 truncate"><?php echo htmlspecialchars($p['model_name']); ?></h3>

                    <p class="text-gray-500 text-sm flex-grow line-clamp-2 mb-4">
                        <?php echo htmlspecialchars($p['description']); ?>
                    </p>

                    <div class="flex items-center justify-between mt-auto pt-4 border-t border-gray-100">
                        <span class="text-2xl font-bold text-gray-900"><?php echo formatPrice($p['price']); ?></span>
                        <a href="product.php?id=<?php echo $p['id']; ?>" class="bg-gray-900 text-white p-2 rounded-full hover:bg-brand-600 transition-colors">
                            <svg xmlns="http://www.w3.org/-2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

        <?php if (empty($products)): ?>
            <div class="col-span-full py-12 text-center text-gray-500">
                <p>No products available right now.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>