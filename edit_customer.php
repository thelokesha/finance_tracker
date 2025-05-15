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
$checkCustomer = $conn->prepare("SELECT * FROM customers WHERE id = ?");
$checkCustomer->bind_param("i", $customer_id);
$checkCustomer->execute();
$result = $checkCustomer->get_result();

if ($result->num_rows === 0) {
    $_SESSION['message'] = "Customer not found";
    $_SESSION['message_type'] = "error";
    header('Location: home.php');
    exit();
}

$customer = $result->fetch_assoc();
$checkCustomer->close();

// Handle form submission
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
    
    // If no errors, update customer
    if (empty($errors)) {
        $stmt = $conn->prepare("UPDATE customers SET name = ?, email = ?, phone = ? WHERE id = ?");
        $stmt->bind_param("sssi", $name, $email, $phone, $customer_id);
        
        if ($stmt->execute()) {
            // Set success message
            $_SESSION['message'] = "Customer updated successfully!";
            $_SESSION['message_type'] = "success";
            header('Location: home.php');
            exit();
        } else {
            // Set error message
            $_SESSION['message'] = "Error updating customer: " . $conn->error;
            $_SESSION['message_type'] = "error";
        }
        
        $stmt->close();
    } else {
        // Set error message
        $error = implode(", ", $errors);
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Customer - Finance Management System</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <header class="dashboard-header">
        <div class="logo">
            <h1>Finance Management System</h1>
        </div>
        <div class="user-info">
            <span>Welcome, <?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
            <a href="logout.php" class="btn btn-logout">Logout</a>
        </div>
    </header>

    <div class="container dashboard-container">
        <section class="dashboard-section glass-card">
            <h2>Edit Customer</h2>
            
            <?php if (isset($error)): ?>
                <div class="error-message"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <div class="form-container">
                <form id="edit-customer-form" method="POST">
                    <div class="form-group">
                        <label for="name">Full Name</label>
                        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($customer['name']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($customer['email']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($customer['phone']); ?>" required>
                    </div>
                    <div class="button-group">
                        <button type="submit" class="btn btn-primary">Update Customer</button>
                        <a href="home.php" class="btn btn-logout">Cancel</a>
                    </div>
                </form>
            </div>
        </section>
    </div>

    <footer class="dashboard-footer">
        <p>&copy; <?php echo date('Y'); ?> Finance Management System. All rights reserved.</p>
    </footer>

    <script src="assets/scripts.js"></script>
</body>
</html>

<?php $conn->close(); ?>