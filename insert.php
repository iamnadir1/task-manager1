<?php
require 'db.php';

$user_id = 1; 

$categories = ['Work', 'Personal', 'School', 'Health', 'Shopping'];

foreach ($categories as $categories) {
    $stmt = $pdo->prepare("INSERT INTO categories (name, user_id) VALUES (?, ?)");
    $stmt->execute([$categories, $user_id]);
}

echo "✅ Categories inserted successfully!";
?>