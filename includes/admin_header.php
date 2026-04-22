<?php
// includes/admin_header.php
require_once __DIR__ . '/functions.php';
requireAdmin();
$base_url = '/mobileshop';
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - MobileShop</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#fdf4ff',
                            100: '#fae8ff',
                            500: '#d946ef',
                            600: '#c026d3',
                            900: '#701a75',
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

<body class="bg-gray-100 flex min-h-screen">

    <!-- Sidebar -->
    <aside class="w-64 bg-gray-900 text-white flex flex-col flex-shrink-0">
        <div class="h-16 flex items-center px-6 bg-gray-950 font-bold text-xl border-b border-gray-800">
            <a href="<?php echo $base_url; ?>/index.php" class="text-white hover:text-brand-500 transition-colors">📱 MobileShop Admin</a>
        </div>

        <div class="px-4 py-6 flex-grow">
            <p class="text-xs uppercase text-gray-500 font-bold mb-4 tracking-wider">Management</p>
            <nav class="space-y-2">
                <a href="dashboard.php" class="block px-4 py-2 rounded-md transition-colors <?php echo $current_page == 'dashboard.php' ? 'bg-brand-600 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white'; ?>">
                    Dashboard
                </a>
                <a href="products.php" class="block px-4 py-2 rounded-md transition-colors <?php echo $current_page == 'products.php' ? 'bg-brand-600 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white'; ?>">
                    Products
                </a>
                <a href="orders.php" class="block px-4 py-2 rounded-md transition-colors <?php echo $current_page == 'orders.php' ? 'bg-brand-600 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white'; ?>">
                    Orders
                </a>
                <a href="users.php" class="block px-4 py-2 rounded-md transition-colors <?php echo $current_page == 'users.php' ? 'bg-brand-600 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white'; ?>">
                    Users
                </a>
            </nav>
        </div>

        <div class="px-4 py-4 border-t border-gray-800">
            <a href="../auth/logout.php" class="flex justify-center items-center w-full px-4 py-2 bg-gray-800 hover:bg-red-600 text-white rounded-md transition-colors border border-gray-700">
                Logout
            </a>
        </div>
    </aside>

    <!-- Main content -->
    <div class="flex-grow flex flex-col min-w-0">
        <header class="h-16 bg-white shadow-sm flex items-center px-8 border-b border-gray-200">
            <h1 class="text-xl font-bold text-gray-800 capitalize"><?php echo pathinfo($current_page, PATHINFO_FILENAME); ?></h1>
        </header>
        <main class="flex-grow p-8 overflow-y-auto">