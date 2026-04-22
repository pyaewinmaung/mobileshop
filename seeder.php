<?php
// seeder.php
require 'config/db.php';

echo "Running seeder...<br>";

// Drop existing tables to start fresh
$tables = ['order_items', 'orders', 'product_images', 'products', 'users'];
$conn->query("SET FOREIGN_KEY_CHECKS = 0");
foreach ($tables as $table) {
    if ($conn->query("DROP TABLE IF EXISTS $table")) {
        echo "Dropped table $table<br>";
    } else {
        echo "Error dropping $table: " . $conn->error . "<br>";
    }
}
$conn->query("SET FOREIGN_KEY_CHECKS = 1");

// Read and execute schema
$schema = file_get_contents('schema.sql');
if ($conn->multi_query($schema)) {
    do {
        // flush multi_queries
        if ($res = $conn->store_result()) {
            $res->free();
        }
    } while ($conn->more_results() && $conn->next_result());
    echo "Schema created successfully.<br>";
} else {
    die("Error creating schema: " . $conn->error);
}

// Seed admin
$adminPass = password_hash('admin123', PASSWORD_DEFAULT);
$stmt = $conn->prepare("INSERT INTO users (username, email, password_hash, role) VALUES (?, ?, ?, ?)");
$role = 'admin';
$adminUser = 'Admin';
$adminEmail = 'admin@gmail.com';
$stmt->bind_param("ssss", $adminUser, $adminEmail, $adminPass, $role);
$stmt->execute();
echo "Inserted admin user (admin@gmail.com / admin123)<br>";

// Seed user
$userPass = password_hash('user123', PASSWORD_DEFAULT);
$roleUser = 'user';
$normalUser = 'User';
$normalEmail = 'user@gmail.com';
$stmt->bind_param("ssss", $normalUser, $normalEmail, $userPass, $roleUser);
$stmt->execute();
echo "Inserted regular user (user@gmail.com / user123)<br>";

// Seed Products
$products = [
    ['brand' => 'Apple', 'model_name' => 'iPhone 15 Pro', 'description' => 'Titanium design, A17 Pro chip, Action button, 48MP Main camera.', 'price' => 999.00, 'stock' => 50],
    ['brand' => 'Samsung', 'model_name' => 'Galaxy S24 Ultra', 'description' => 'AI features, Titanium frame, 200MP camera, built-in S Pen.', 'price' => 1299.00, 'stock' => 30],
    ['brand' => 'Google', 'model_name' => 'Pixel 8 Pro', 'description' => 'Google Tensor G3, advanced AI photo editing, 50MP wide lens.', 'price' => 899.00, 'stock' => 15],
    ['brand' => 'OnePlus', 'model_name' => '13 5G', 'description' => 'Snapdragon 8 Gen 3, Hasselblad Camera for Mobile, 100W Charging.', 'price' => 799.00, 'stock' => 45],
];

$stmtProd = $conn->prepare("INSERT INTO products (brand, model_name, description, price, stock_quantity) VALUES (?, ?, ?, ?, ?)");
$stmtImage = $conn->prepare("INSERT INTO product_images (product_id, image_url, is_primary) VALUES (?, ?, ?)");
$primary = 1;
$secondary = 0;

$placeholders = [
    'https://images.unsplash.com/photo-1695048133142-1a20484d2569?q=80&w=600&auto=format&fit=crop', // Apple
    'https://images.unsplash.com/photo-1610945415295-d9bbf067e59c?q=80&w=600&auto=format&fit=crop', // Samsung
    'https://images.unsplash.com/photo-1598327105666-5b89351aff97?q=80&w=600&auto=format&fit=crop', // Google
    'https://images.unsplash.com/photo-1598124146163-36819847286d?q=80&w=600&auto=format&fit=crop'  // OnePlus
];

foreach ($products as $i => $p) {
    $stmtProd->bind_param("sssdi", $p['brand'], $p['model_name'], $p['description'], $p['price'], $p['stock']);
    $stmtProd->execute();
    $productId = $conn->insert_id;

    // Primary image
    $url = $placeholders[$i];
    $stmtImage->bind_param("isi", $productId, $url, $primary);
    $stmtImage->execute();

    // Secondary mock image
    $url2 = 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?q=80&w=600&auto=format&fit=crop';
    $stmtImage->bind_param("isi", $productId, $url2, $secondary);
    $stmtImage->execute();
}
echo "Inserted " . count($products) . " dummy products with multiple images.<br>";

echo "Seeding completed successfully!<br>";
