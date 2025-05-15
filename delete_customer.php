<?php
session_start();
require_once 'includes/auth.php'; // Authentication check
require_once 'includes/db.php';   // Database connection

// Check if customer ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['message'] = "Customer ID is required";
    $_SESSION['message_type'] = "error";
    header('Location: home.php');
    exit();
}

$customer_id = filter_var($_GET['id'], FILTER_VALIDATE_INT);

// Verify customer exists
$checkCustomer = $conn->prepare("SELECT id FROM customers WHERE id = ?");
$checkCustomer->bind_param("i", $customer_id);
$checkCustomer->execute();
$result = $checkCustomer->get_result();

if ($result->num_rows === 0) {
    $_SESSION['message'] = "Customer not found";
    $_SESSION['message_type'] = "error";
    header('Location: home.php');
    exit();
}

$checkCustomer->close();

// Check if customer has any loans
$checkLoans = $conn->prepare("SELECT COUNT(*) as loan_count FROM loans WHERE customer_id = ?");
$checkLoans->bind_param("i", $customer_id);
$checkLoans->execute();
$loanResult = $checkLoans->get_result();
$loanCount = $loanResult->fetch_assoc()['loan_count'];
$checkLoans->close();

if ($loanCount > 0) {
    $_SESSION['message'] = "Cannot delete customer with active loans. Please delete the loans first.";
    $_SESSION['message_type'] = "error";
    header('Location: home.php');
    exit();
}

// Delete the customer
$deleteCustomer = $conn->prepare("DELETE FROM customers WHERE id = ?");
$deleteCustomer->bind_param("i", $customer_id);

if ($deleteCustomer->execute()) {
    $_SESSION['message'] = "Customer deleted successfully!";
    $_SESSION['message_type'] = "success";
} else {
    $_SESSION['message'] = "Error deleting customer: " . $conn->error;
    $_SESSION['message_type'] = "error";
}

$deleteCustomer->close();
$conn->close();

header('Location: home.php');
exit();
?>