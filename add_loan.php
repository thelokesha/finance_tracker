<?php
session_start();
require_once 'includes/auth.php'; // Authentication check
require_once 'includes/db.php';   // Database connection

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate input
    $customer_id = filter_var($_POST['customer_id'], FILTER_VALIDATE_INT);
    $amount = filter_var($_POST['amount'], FILTER_VALIDATE_FLOAT);
    $interest_rate = filter_var($_POST['interest_rate'], FILTER_VALIDATE_FLOAT);
    $terms = trim($_POST['terms']);
    
    $errors = [];
    
    // Validate customer_id
    if (!$customer_id) {
        $errors[] = "Please select a valid customer";
    } else {
        // Check if customer exists
        $checkCustomer = $conn->prepare("SELECT id FROM customers WHERE id = ?");
        $checkCustomer->bind_param("i", $customer_id);
        $checkCustomer->execute();
        $customerResult = $checkCustomer->get_result();
        
        if ($customerResult->num_rows === 0) {
            $errors[] = "Selected customer does not exist";
        }
        
        $checkCustomer->close();
    }
    
    // Validate amount
    if ($amount === false || $amount <= 0) {
        $errors[] = "Please enter a valid loan amount";
    }
    
    // Validate interest rate
    if ($interest_rate === false || $interest_rate < 0 || $interest_rate > 100) {
        $errors[] = "Please enter a valid interest rate (0-100%)";
    }
    
    // Validate terms
    if (empty($terms)) {
        $errors[] = "Terms are required";
    } elseif (strlen($terms) > 100) {
        $errors[] = "Terms are too long (maximum 100 characters)";
    }
    
    // If no errors, insert new loan
    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO loans (customer_id, amount, interest_rate, terms) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("idds", $customer_id, $amount, $interest_rate, $terms);
        
        if ($stmt->execute()) {
            // Set success message
            $_SESSION['message'] = "Loan added successfully!";
            $_SESSION['message_type'] = "success";
        } else {
            // Set error message
            $_SESSION['message'] = "Error adding loan: " . $conn->error;
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
