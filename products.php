<?php
// products.php
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
$limit = 12;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;

// Count total for pagination
if (!empty($search)) {
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM products WHERE model_name LIKE ? OR brand LIKE ?");
    $searchTerm = '%' . $search . '%';
    $stmt->bind_param("ss", $searchTerm, $searchTerm);
    $stmt->execute();
    $total_row = $stmt->get_result()->fetch_assoc();
} else {
    $total_result = $conn->query("SELECT COUNT(*) as count FROM products");
    $total_row = $total_result->fetch_assoc();
}
$total_products = $total_row['count'];
$total_pages = ceil($total_products / $limit);

if ($page > $total_pages && $total_pages > 0) {
    $page = $total_pages; 
}

$offset = ($page - 1) * $limit;

// Fetch products
if (!empty($search)) {
    $stmt = $conn->prepare("SELECT p.*, (SELECT image_url FROM product_images pi WHERE pi.product_id = p.id AND pi.is_primary = 1 LIMIT 1) as primary_image
                            FROM products p
                            WHERE p.model_name LIKE ? OR p.brand LIKE ?
                            ORDER BY p.created_at DESC LIMIT ? OFFSET ?");
    $stmt->bind_param("ssii", $searchTerm, $searchTerm, $limit, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $sql = "SELECT p.*, (SELECT image_url FROM product_images pi WHERE pi.product_id = p.id AND pi.is_primary = 1 LIMIT 1) as primary_image
            FROM products p
            ORDER BY p.created_at DESC LIMIT $limit OFFSET $offset";
    $result = $conn->query($sql);
}

$products = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}

// Pre-fetch user's wishlisted product IDs
$wishlistIds = getUserWishlistIds($conn);
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 border-b border-gray-200">
    <div class="text-center mb-10">
        <h1 class="text-3xl font-extrabold text-gray-900 sm:text-5xl md:text-6xl tracking-tight mb-4 text-transparent bg-clip-text bg-gradient-to-r from-brand-600 to-indigo-600">
            Full Product Catalog
        </h1>
        <p class="mt-4 max-w-2xl mx-auto text-xl text-gray-500">
            Browse our entire collection of mobile devices.
        </p>
    </div>

    <!-- Search Form -->
    <div class="max-w-3xl mx-auto mb-12">
        <form action="products.php" method="GET" class="relative flex items-center w-full h-14 rounded-full focus-within:shadow-md bg-white border border-gray-200 overflow-hidden transition-shadow">
            <div class="grid place-items-center h-full w-14 text-gray-400">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
            <input class="peer h-full w-full outline-none text-base text-gray-700 pr-2 placeholder-gray-400 bg-transparent" type="text" id="search" name="search" placeholder="Search for phones by brand or model..." value="<?php echo htmlspecialchars($search); ?>" />
            <button type="submit" class="bg-brand-600 hover:bg-brand-700 text-white px-8 h-full font-medium transition-colors">Search</button>
        </form>
        <?php if (!empty($search)): ?>
            <div class="mt-4 flex items-center justify-between px-2">
                <p class="text-sm text-gray-500">Showing results for <span class="font-bold text-gray-900">"<?php echo htmlspecialchars($search); ?>"</span></p>
                <a href="products.php" class="text-sm font-medium text-brand-600 hover:text-brand-800">Clear filters</a>
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

                    <img src="<?php echo htmlspecialchars($p['primary_image'] ?? 'https://via.placeholder.com/400x400?text=No+Image'); ?>" alt="<?php echo htmlspecialchars($p['model_name']); ?>" class="h-64 w-full object-contain object-center group-hover:scale-105 transition-transform duration-500">
                </div>

                <div class="p-6 flex flex-col flex-grow">
                    <p class="text-xs text-brand-600 font-bold uppercase tracking-wider mb-1"><?php echo htmlspecialchars($p['brand']); ?></p>
                    <h3 class="text-xl font-bold text-gray-900 mb-2 truncate"><?php echo htmlspecialchars($p['model_name']); ?></h3>
                    <p class="text-gray-500 text-sm flex-grow line-clamp-2 mb-4"><?php echo htmlspecialchars($p['description']); ?></p>
                    <div class="flex items-center justify-between mt-auto pt-4 border-t border-gray-100">
                        <span class="text-2xl font-bold text-gray-900"><?php echo formatPrice($p['price']); ?></span>
                        <a href="product.php?id=<?php echo $p['id']; ?>" class="bg-gray-900 text-white p-2 rounded-full hover:bg-brand-600 transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

        <?php if (empty($products)): ?>
            <div class="col-span-full py-12 text-center text-gray-500">
                <p>No products found matching your search.</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
        <div class="mt-12 flex justify-center">
            <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
                <a href="?search=<?php echo urlencode($search); ?>&page=<?php echo max(1, $page-1); ?>" class="relative inline-flex items-center rounded-l-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 <?php if($page <= 1) echo 'opacity-50 pointer-events-none'; ?>">
                    <span class="sr-only">Previous</span>
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M12.79 5.23a.75.75 0 01-.02 1.06L8.832 10l3.938 3.71a.75.75 0 11-1.04 1.08l-4.5-4.25a.75.75 0 010-1.08l4.5-4.25a.75.75 0 011.06.02z" clip-rule="evenodd" /></svg>
                </a>
                
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?search=<?php echo urlencode($search); ?>&page=<?php echo $i; ?>" class="relative inline-flex items-center px-4 py-2 text-sm font-semibold <?php echo $i == $page ? 'bg-brand-600 text-white z-10' : 'text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50'; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>

                <a href="?search=<?php echo urlencode($search); ?>&page=<?php echo min($total_pages, $page+1); ?>" class="relative inline-flex items-center rounded-r-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 <?php if($page >= $total_pages) echo 'opacity-50 pointer-events-none'; ?>">
                    <span class="sr-only">Next</span>
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" /></svg>
                </a>
            </nav>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
