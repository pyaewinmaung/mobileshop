<?php
// index.php
require_once 'config/db.php';
require_once 'includes/functions.php';

// Handle wishlist toggle (POST-Redirect-GET)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_wishlist'])) {
    if (isLoggedIn()) {
        $productId = intval($_POST['product_id']);
        toggleWishlist($conn, $productId);
    } else {
        header("Location: /mobileshop/auth/login.php");
        exit;
    }
    header("Location: " . $_SERVER['REQUEST_URI']);
    exit;
}

include 'includes/header.php';

$search = $_GET['search'] ?? '';

// Pre-fetch user's wishlisted product IDs
$wishlistIds = getUserWishlistIds($conn);

// Fetch only the latest 8 products for the homepage
$sql = "SELECT p.*, (SELECT image_url FROM product_images pi WHERE pi.product_id = p.id AND pi.is_primary = 1 LIMIT 1) as primary_image
        FROM products p
        ORDER BY p.created_at DESC LIMIT 8";
$result = $conn->query($sql);
$products = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 border-b border-gray-200">
    <!-- Search Form -->
    <div class="max-w-3xl mx-auto mb-5">
        <form action="products.php" method="GET" class="relative flex items-center w-full h-14 rounded-full focus-within:shadow-md bg-white border border-gray-200 overflow-hidden transition-shadow">
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
    </div>

    <div class="text-center mb-5">
        <h1 class="text-3xl font-extrabold text-gray-900 sm:text-5xl md:text-6xl tracking-tight mb-4 text-transparent bg-clip-text bg-gradient-to-r from-brand-600 to-indigo-600">
            Latest Premium Phones
        </h1>
        <p class="mt-4 max-w-2xl mx-auto text-xl text-gray-500">
            Discover the best high-end mobile devices directly from top brands.
        </p>
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

                    <!-- Wishlist heart button -->
                    <form method="POST" action="" class="absolute top-4 left-4 z-10" onclick="event.stopPropagation();">
                        <input type="hidden" name="toggle_wishlist" value="1">
                        <input type="hidden" name="product_id" value="<?php echo $p['id']; ?>">
                        <button type="submit" class="bg-white/80 backdrop-blur-sm rounded-full p-2 shadow-md hover:bg-white transition-all hover:scale-110" aria-label="Toggle Wishlist">
                            <?php if (in_array($p['id'], $wishlistIds)): ?>
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5 text-red-500">
                                    <path d="M11.645 20.91l-.007-.003-.022-.012a15.247 15.247 0 01-.383-.218 25.18 25.18 0 01-4.244-3.17C4.688 15.36 2.25 12.174 2.25 8.25 2.25 5.322 4.714 3 7.688 3A5.5 5.5 0 0112 5.052 5.5 5.5 0 0116.313 3c2.973 0 5.437 2.322 5.437 5.25 0 3.925-2.438 7.111-4.739 9.256a25.175 25.175 0 01-4.244 3.17 15.247 15.247 0 01-.383.219l-.022.012-.007.004-.003.001a.752.752 0 01-.704 0l-.003-.001z" />
                                </svg>
                            <?php else: ?>
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 hover:text-red-500">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                                </svg>
                            <?php endif; ?>
                        </button>
                    </form>

                    <img src="<?php echo htmlspecialchars($p['primary_image'] ?? 'https://via.placeholder.com/400x400?text=No+Image'); ?>"
                        alt="<?php echo htmlspecialchars($p['model_name']); ?>"
                        class="h-64 w-full object-contain object-center group-hover:scale-105 transition-transform duration-500" />
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

    <!-- See All Button -->
    <div class="mt-16 text-center">
        <a href="products.php" class="inline-flex items-center gap-2 bg-white text-brand-600 border border-brand-200 px-8 py-3 rounded-full font-bold hover:bg-brand-50 hover:border-brand-300 transition-colors shadow-sm">
            See All Products
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
            </svg>
        </a>
    </div>
</div>

<?php include 'includes/footer.php'; ?>