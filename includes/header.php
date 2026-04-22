<?php
// includes/header.php
require_once __DIR__ . '/functions.php';
$base_url = '/mobileshop';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mobile Shop E-Commerce</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#f0f9ff',
                            100: '#e0f2fe',
                            500: '#0ea5e9',
                            600: '#0284c7',
                            900: '#0c4a6e',
                        }
                    }
                }
            }
        }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>

<body class="bg-gray-50 flex flex-col min-h-screen">

    <nav class="bg-white shadow-sm border-b sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex">
                    <div class="flex-shrink-0 flex items-center">
                        <a href="<?php echo $base_url; ?>/index.php" class="text-2xl font-bold text-brand-600 flex items-center gap-2">
                            📱 MobileShop
                        </a>
                    </div>
                </div>
                <div class="flex items-center gap-4 sm:gap-6">
                    <!-- Desktop links -->
                    <div class="hidden md:flex items-center gap-6">
                        <a href="<?php echo $base_url; ?>/products.php" class="text-gray-600 hover:text-brand-600 font-medium transition-colors">Products</a>
                        <?php if (isLoggedIn()): ?>
                            <?php if (isAdmin()): ?>
                                <a href="<?php echo $base_url; ?>/admin/dashboard.php" class="text-gray-600 hover:text-brand-600 font-medium transition-colors">Admin Dashboard</a>
                            <?php else: ?>
                                <a href="<?php echo $base_url; ?>/user/profile.php" class="text-gray-600 hover:text-brand-600 font-medium transition-colors flex items-center gap-1" aria-label="Profile">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                                    </svg>
                                </a>
                            <?php endif; ?>
                        <?php else: ?>
                            <a href="<?php echo $base_url; ?>/auth/login.php" class="text-gray-600 hover:text-brand-600 font-medium transition-colors">Login</a>
                            <a href="<?php echo $base_url; ?>/auth/register.php" class="bg-brand-600 text-white px-4 py-2 rounded-md font-medium hover:bg-brand-700 transition-colors shadow-md hover:shadow-lg">Sign Up</a>
                        <?php endif; ?>
                    </div>

                    <!-- Cart Icon (Always visible) -->
                    <a href="<?php echo $base_url; ?>/cart.php" class="text-gray-600 hover:text-brand-600 font-medium transition-colors relative block" aria-label="Cart">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z" />
                        </svg>
                        <?php if (getCartCount() > 0): ?>
                            <span class="absolute -top-2 -right-3 bg-red-500 text-white text-xs font-bold rounded-full h-5 w-5 flex items-center justify-center">
                                <?php echo getCartCount(); ?>
                            </span>
                        <?php endif; ?>
                    </a>

                    <!-- Mobile Menu Toggle Button -->
                    <button type="button" class="md:hidden text-gray-600 hover:text-brand-600 focus:outline-none" onclick="document.getElementById('mobile-menu').classList.toggle('hidden')">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Mobile Menu -->
            <div id="mobile-menu" class="hidden md:hidden border-t border-gray-100 py-3 space-y-2">
                <a href="<?php echo $base_url; ?>/products.php" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-brand-600 hover:bg-gray-50">Products</a>
                <?php if (isLoggedIn()): ?>
                    <?php if (isAdmin()): ?>
                        <a href="<?php echo $base_url; ?>/admin/dashboard.php" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-brand-600 hover:bg-gray-50">Admin Dashboard</a>
                    <?php else: ?>
                        <a href="<?php echo $base_url; ?>/user/profile.php" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-brand-600 hover:bg-gray-50"> Profile</a>
                    <?php endif; ?>
                <?php else: ?>
                    <a href="<?php echo $base_url; ?>/auth/login.php" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 hover:text-brand-600 hover:bg-gray-50">Login</a>
                    <a href="<?php echo $base_url; ?>/auth/register.php" class="block px-3 py-2 rounded-md text-base font-medium text-brand-600 hover:bg-brand-50 mt-2">Sign Up</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <main class="flex-grow">
        <!-- content opens here -->