<?php
// MySQL Configuration
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'finance_db';

// Create MySQL connection
$conn = new mysqli($host, $user, $password);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database if not exists
$conn->query("CREATE DATABASE IF NOT EXISTS $database");

// Select the database
$conn->select_db($database);

// Check if tables exist (simple check for one table)
$result = $conn->query("SHOW TABLES LIKE 'admin'");
if ($result->num_rows == 0) {
    // Import SQL file if tables do not exist
    $sqlFilePath = __DIR__ . '/../sql/finance_db.sql';
    $sqlCommands = file_get_contents($sqlFilePath);

    if (!$conn->multi_query($sqlCommands)) {
        die("Error importing SQL file: " . $conn->error);
    }

    // Wait for multi_query to finish
    while ($conn->more_results() && $conn->next_result()) {;}
}

// Optional: Set timezone
date_default_timezone_set('UTC');

// Ready to use $conn for your queries
?>

