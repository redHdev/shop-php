<?php

# Import your dependencies
require_once('vendor/autoload.php');

# Load env variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Database connection (assuming it's in the includes folder)
require 'includes/db.php';

// Common functions (assuming it's in the includes folder)
require 'includes/function.php';

require 'controllers/userController.php';

// Get the route from the URL
$request_url = isset($_GET['url']) ? rtrim($_GET['url'], '/') : 'home';
$request_method = $_SERVER['REQUEST_METHOD'];

// Assuming you have a session-based authentication mechanism
// Start session
session_start();

// Based on the route, decide what content to show
switch ($request_url) {
    case 'signin':
        // Include the signin
        include 'views/signin.php';
        break;

    case 'signup':
        // Include the signup
        if ($request_method == 'GET') {
            include 'views/signup.php';
            break;
        }
        else if ($request_method == 'POST') {
            signUpfunction($_POST);
            break;
        }
    case 'verify/resend':
        $emailAddress = base64_decode($_GET["emailAdress"]);
        sendVerificationEmail($emailAddress);
        break;
    case 'verify':
        include 'views/verification.php';
        break;
    case 'verification':
        if($request_method == 'POST'){
            verificationWithCode($_POST);
            break;
        }
    
    case 'home':
        // Include the homepage content (you can further break this into header, content, footer if you wish)
        include 'views/home.php';
        break;
        
    case 'products':
        // Include the products listing
        include 'views/products.php';
        break;
        
    case 'product-details':
        // Assume you'll pass product ID in the URL as product-details/1
        $productId = explode('/', $route)[1] ?? null;
        if($productId) {
            include 'views/product-details.php';
        } else {
            include 'views/404.php';
        }
        break;
        
    case 'admin':
        // Check if user is authenticated
        if(isset($_SESSION['isAdmin']) && $_SESSION['isAdmin']) {
            // Include the admin dashboard
            include 'admin/index.php';
        } else {
            // Redirect to admin login or show error
            header("Location: /admin/login.php");
        }
        break;
        
    default:
        // If none of the above routes match, show a 404 page
        include 'views/page-error-404.php';
        break;
}

// Don't forget to end your session if you're done with it
// session_destroy();