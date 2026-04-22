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
    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            
            sidebar.classList.toggle('-translate-x-full');
            
            if (sidebar.classList.contains('-translate-x-full')) {
                overlay.classList.add('hidden');
            } else {
                overlay.classList.remove('hidden');
            }
        }
    </script>
</head>

<body class="bg-gray-100 flex h-screen overflow-hidden text-gray-900">

    <!-- Mobile Sidebar Overlay -->
    <div id="sidebar-overlay" class="fixed inset-0 bg-gray-900 bg-opacity-50 z-40 hidden transition-opacity lg:hidden" onclick="toggleSidebar()"></div>

    <!-- Sidebar -->
    <aside id="sidebar" class="bg-gray-900 text-white w-64 flex flex-col flex-shrink-0 fixed inset-y-0 left-0 transform -translate-x-full lg:relative lg:translate-x-0 z-50 transition-transform duration-300 shadow-xl lg:shadow-none">
        <div class="h-16 flex items-center justify-between px-6 bg-gray-950 font-bold text-xl border-b border-gray-800">
            <a href="<?php echo $base_url; ?>/index.php" class="text-white hover:text-brand-500 transition-colors flex items-center">📱 MobileShop</a>
            <button class="lg:hidden text-gray-400 hover:text-white focus:outline-none" onclick="toggleSidebar()">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <div class="px-4 py-6 flex-grow overflow-y-auto">
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

    <!-- Main Workspace Area -->
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden relative">
        
        <!-- Fixed Header -->
        <header class="h-16 bg-white shadow-sm flex flex-shrink-0 items-center justify-between px-4 lg:px-8 border-b border-gray-200 z-30">
            <div class="flex items-center">
                <button class="lg:hidden text-gray-600 hover:text-brand-600 focus:outline-none mr-4 bg-gray-100 p-2 rounded-md transition-colors" onclick="toggleSidebar()">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <h1 class="text-xl font-bold text-gray-800 capitalize"><?php echo str_replace('.php', '', pathinfo($current_page, PATHINFO_FILENAME)); ?></h1>
            </div>
        </header>

        <!-- Scrollable Main Content -->
        <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8">