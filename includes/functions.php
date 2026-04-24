<?php
// includes/functions.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isLoggedIn()
{
    return isset($_SESSION['user_id']);
}

function isAdmin()
{
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function requireLogin()
{
    if (!isLoggedIn()) {
        header("Location: /mobileshop/auth/login.php");
        exit;
    }
}

function requireAdmin()
{
    if (!isAdmin()) {
        header("Location: /mobileshop/index.php");
        exit;
    }
}

function getCartCount()
{
    $count = 0;
    if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $item) {
            $count += $item['quantity'];
        }
    }
    return $count;
}

function formatPrice($price)
{
    return "$" . number_format($price, 2);
}

/**
 * Delete all image files from disk for a given product.
 * Queries the product_images table, resolves each image_url to a
 * filesystem path, and unlinks the file if it exists.
 *
 * @param mysqli $conn       Database connection
 * @param int    $productId  The product ID whose images should be removed
 * @return int   Number of files successfully deleted
 */
function deleteProductImageFiles($conn, $productId)
{
    $deleted = 0;
    $stmt = $conn->prepare("SELECT image_url FROM product_images WHERE product_id = ?");
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $imageUrl = $row['image_url'];
        // Convert DB URL (e.g. /mobileshop/assets/uploads/img.jpg) to filesystem path
        // Works on both Windows (WAMP) and Linux environments
        $docRoot = rtrim($_SERVER['DOCUMENT_ROOT'] ?? 'c:/wamp64/www', '/\\');
        $filePath = $docRoot . str_replace('/', DIRECTORY_SEPARATOR, $imageUrl);

        if (file_exists($filePath) && is_file($filePath)) {
            if (unlink($filePath)) {
                $deleted++;
            }
        }
    }
    $stmt->close();
    return $deleted;
}

/**
 * Get the number of items in the current user's wishlist.
 *
 * @param mysqli $conn  Database connection
 * @return int          Wishlist item count (0 if not logged in)
 */
function getWishlistCount($conn)
{
    if (!isLoggedIn()) return 0;
    $userId = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM wishlist WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return (int)$row['count'];
}

/**
 * Get an array of product IDs in the current user's wishlist.
 * Useful for checking if a product card should show a filled heart.
 *
 * @param mysqli $conn  Database connection
 * @return array        Array of product IDs (empty if not logged in)
 */
function getUserWishlistIds($conn)
{
    if (!isLoggedIn()) return [];
    $userId = $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT product_id FROM wishlist WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $ids = [];
    while ($row = $result->fetch_assoc()) {
        $ids[] = (int)$row['product_id'];
    }
    $stmt->close();
    return $ids;
}

/**
 * Toggle a product in the user's wishlist (add if missing, remove if present).
 * Uses INSERT IGNORE + affected_rows check for idempotent behavior.
 *
 * @param mysqli $conn       Database connection
 * @param int    $productId  The product to toggle
 * @return string            'added' or 'removed'
 */
function toggleWishlist($conn, $productId)
{
    $userId = $_SESSION['user_id'];

    // Try to insert — IGNORE silently skips if duplicate exists
    $stmt = $conn->prepare("INSERT IGNORE INTO wishlist (user_id, product_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $userId, $productId);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        $stmt->close();
        return 'added';
    }

    // Already existed — remove it (toggle off)
    $stmt->close();
    $stmt = $conn->prepare("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?");
    $stmt->bind_param("ii", $userId, $productId);
    $stmt->execute();
    $stmt->close();
    return 'removed';
}
