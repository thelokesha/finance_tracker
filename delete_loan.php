<?php
session_start();
require_once 'includes/auth.php'; // Authentication check
require_once 'includes/db.php';   // Database connection

// Check if loan ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['message'] = "Loan ID is required";
    $_SESSION['message_type'] = "error";
    header('Location: home.php');
    exit();
}

$loan_id = filter_var($_GET['id'], FILTER_VALIDATE_INT);

// Verify loan exists
$checkLoan = $conn->prepare("SELECT id FROM loans WHERE id = ?");
$checkLoan->bind_param("i", $loan_id);
$checkLoan->execute();
$result = $checkLoan->get_result();

if ($result->num_rows === 0) {
    $_SESSION['message'] = "Loan not found";
    $_SESSION['message_type'] = "error";
    header('Location: home.php');
    exit();
}

$checkLoan->close();

// Delete the loan
$deleteLoan = $conn->prepare("DELETE FROM loans WHERE id = ?");
$deleteLoan->bind_param("i", $loan_id);

if ($deleteLoan->execute()) {
    $_SESSION['message'] = "Loan deleted successfully!";
    $_SESSION['message_type'] = "success";
} else {
    $_SESSION['message'] = "Error deleting loan: " . $conn->error;
    $_SESSION['message_type'] = "error";
}

$deleteLoan->close();
$conn->close();

header('Location: home.php');
exit();
?>