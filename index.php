<?php
session_start();

// If already logged in, redirect to home page
if (isset($_SESSION['admin_id'])) {
    header('Location: home.php');
    exit();
}

// Database connection
require_once 'includes/db.php';

$error = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    
    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password';
    } else {
        // Prepare query to prevent SQL injection
        $stmt = $conn->prepare("SELECT id, username, password FROM admin WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $admin = $result->fetch_assoc();
            
            // Verify password (in a real app, you'd use password_verify with hashed passwords)
            if ($password === $admin['password']) {
                // Set session variables
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_username'] = $admin['username'];
                
                // Redirect to home page
                header('Location: home.php');
                exit();
            } else {
                $error = 'Invalid username or password';
            }
        } else {
            $error = 'Invalid username or password';
        }
        
        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finance Management System - Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="container login-container">
        <div class="login-card glass-card">
            <div class="login-header">
                <h1>Finance Management System</h1>
                <p>Admin Login</p>
            </div>
            <?php if ($error): ?>
                <div class="error-message"><?php echo $error; ?></div>
            <?php endif; ?>
            <form method="POST" action="index.php" class="login-form">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit" class="btn btn-primary">Login</button>
            </form>
        </div>
    </div>

    <script src="assets/scripts.js"></script>
</body>
</html>
