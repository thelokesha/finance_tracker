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
$checkLoan = $conn->prepare("SELECT * FROM loans WHERE id = ?");
$checkLoan->bind_param("i", $loan_id);
$checkLoan->execute();
$result = $checkLoan->get_result();

if ($result->num_rows === 0) {
    $_SESSION['message'] = "Loan not found";
    $_SESSION['message_type'] = "error";
    header('Location: home.php');
    exit();
}

$loan = $result->fetch_assoc();
$checkLoan->close();

// Get customers for dropdown
$customerDropdownQuery = "SELECT id, name FROM customers ORDER BY name";
$customerDropdownResult = $conn->query($customerDropdownQuery);

// Handle form submission
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
    
    // If no errors, update loan
    if (empty($errors)) {
        $stmt = $conn->prepare("UPDATE loans SET customer_id = ?, amount = ?, interest_rate = ?, terms = ? WHERE id = ?");
        $stmt->bind_param("iddsi", $customer_id, $amount, $interest_rate, $terms, $loan_id);
        
        if ($stmt->execute()) {
            // Set success message
            $_SESSION['message'] = "Loan updated successfully!";
            $_SESSION['message_type'] = "success";
            header('Location: home.php');
            exit();
        } else {
            // Set error message
            $_SESSION['message'] = "Error updating loan: " . $conn->error;
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
    <title>Edit Loan - Finance Management System</title>
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
            <h2>Edit Loan</h2>
            
            <?php if (isset($error)): ?>
                <div class="error-message"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <div class="form-container">
                <form id="edit-loan-form" method="POST">
                    <div class="form-group">
                        <label for="customer_id">Select Customer</label>
                        <select id="customer_id" name="customer_id" required>
                            <option value="">-- Select Customer --</option>
                            <?php while ($customerOption = $customerDropdownResult->fetch_assoc()): ?>
                                <option value="<?php echo $customerOption['id']; ?>" <?php echo ($customerOption['id'] == $loan['customer_id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($customerOption['name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="amount">Loan Amount</label>
                        <input type="number" id="amount" name="amount" step="0.01" min="0" value="<?php echo htmlspecialchars($loan['amount']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="interest_rate">Interest Rate (%)</label>
                        <input type="number" id="interest_rate" name="interest_rate" step="0.01" min="0" max="100" value="<?php echo htmlspecialchars($loan['interest_rate']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="terms">Terms</label>
                        <input type="text" id="terms" name="terms" placeholder="e.g., 12 months" value="<?php echo htmlspecialchars($loan['terms']); ?>" required>
                    </div>
                    <div class="button-group">
                        <button type="submit" class="btn btn-primary">Update Loan</button>
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