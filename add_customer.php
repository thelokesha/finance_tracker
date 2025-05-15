<?php
session_start();
require_once 'includes/auth.php'; // Authentication check
require_once 'includes/db.php';   // Database connection

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate input
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    
    $errors = [];
    
    // Validate name
    if (empty($name)) {
        $errors[] = "Name is required";
    } elseif (strlen($name) > 255) {
        $errors[] = "Name is too long (maximum 255 characters)";
    }
    
    // Validate email
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    } elseif (strlen($email) > 255) {
        $errors[] = "Email is too long (maximum 255 characters)";
    }
    
    // Validate phone
    if (empty($phone)) {
        $errors[] = "Phone number is required";
    } elseif (strlen($phone) > 20) {
        $errors[] = "Phone number is too long (maximum 20 characters)";
    }
    
    // If no errors, insert new customer
    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO customers (name, email, phone) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $email, $phone);
        
        if ($stmt->execute()) {
            // Set success message
            $_SESSION['message'] = "Customer added successfully!";
            $_SESSION['message_type'] = "success";
        } else {
            // Set error message
            $_SESSION['message'] = "Error adding customer: " . $conn->error;
            $_SESSION['message_type'] = "error";
        }
        
        $stmt->close();
    } else {
        // Set error message
        $_SESSION['message'] = "Error: " . implode(", ", $errors);
        $_SESSION['message_type'] = "error";
    }
    
    // Redirect back to home page
    header('Location: home.php');
    exit();
} else {
    // Not a POST request, redirect to home page
    header('Location: home.php');
    exit();
}

$conn->close();
?>
