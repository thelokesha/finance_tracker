<?php
require_once 'includes/db.php';

echo "<h1>Database Connection Test</h1>";

try {
    // Test simple query
    $result = $conn->query("SELECT * FROM admin");
    
    if ($result && $result->num_rows > 0) {
        echo "<p style='color:green'>✓ Database connection successful!</p>";
        echo "<p>Found " . $result->num_rows . " admin record(s)</p>";
    } else {
        echo "<p style='color:red'>✗ Could not read from admin table.</p>";
    }
    
    // Test customers table
    $customerResult = $conn->query("SELECT * FROM customers ORDER BY id ASC");
    if ($customerResult && $customerResult->num_rows > 0) {
        echo "<h2>Customers</h2>";
        echo "<ul>";
        while ($row = $customerResult->fetch_assoc()) {
            echo "<li>" . htmlspecialchars($row['id'] . ": " . $row['name'] . " - " . $row['email']) . "</li>";
        }
        echo "</ul>";
    } else {
        echo "<p style='color:red'>✗ No customers found.</p>";
    }
    
    // Test loans table
    $loanResult = $conn->query("SELECT l.*, c.name as customer_name FROM loans l JOIN customers c ON l.customer_id = c.id ORDER BY l.id ASC");
    if ($loanResult && $loanResult->num_rows > 0) {
        echo "<h2>Loans</h2>";
        echo "<ul>";
        while ($row = $loanResult->fetch_assoc()) {
            echo "<li>" . htmlspecialchars($row['id'] . ": ₹" . $row['amount'] . " for " . $row['customer_name'] . " at " . $row['interest_rate'] . "% (" . $row['terms'] . ")") . "</li>";
        }
        echo "</ul>";
    } else {
        echo "<p style='color:red'>✗ No loans found.</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color:red'>Error: " . $e->getMessage() . "</p>";
}
?>