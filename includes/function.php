<?php

// Include the db.php file to make use of the $connection variable
require_once 'db.php';

// Function to fetch all products
function getProducts() {
    global $connection;

    $query = "SELECT * FROM products";
    $result = $connection->query($query);

    $products = [];
    if ($result->num_rows > 0) {
        // Fetch all rows into an associative array
        $products = $result->fetch_all(MYSQLI_ASSOC);
    }

    return $products;
}

// Function to fetch details of a single product
function getProductDetails($productId) {
    global $connection;

    $productId = $connection->real_escape_string($productId); // Escaping the string to prevent SQL injection
    $query = "SELECT * FROM products WHERE id = '$productId'";
    $result = $connection->query($query);

    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    }

    return null;
}