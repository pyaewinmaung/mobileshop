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
