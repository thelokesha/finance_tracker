<?php
session_start();
require_once 'includes/auth.php'; // Authentication check
require_once 'includes/db.php';   // Database connection

// Fetch all customers - sorted by oldest first (ascending)
$customersQuery = "SELECT * FROM customers ORDER BY id ASC";
$customersResult = $conn->query($customersQuery);

// Fetch all loans with customer names - sorted by oldest first (ascending)
$loansQuery = "SELECT l.*, c.name as customer_name 
               FROM loans l 
               JOIN customers c ON l.customer_id = c.id 
               ORDER BY l.id ASC";
$loansResult = $conn->query($loansQuery);

// Get customers for dropdown
$customerDropdownQuery = "SELECT id, name FROM customers ORDER BY name";
$customerDropdownResult = $conn->query($customerDropdownQuery);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finance Management Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        <?php if (isset($_SESSION['message'])): ?>
            <div class="<?php echo $_SESSION['message_type'] === 'success' ? 'success-message' : 'error-message'; ?>">
                <?php 
                    echo $_SESSION['message']; 
                    unset($_SESSION['message']); 
                    unset($_SESSION['message_type']);
                ?>
            </div>
        <?php endif; ?>
        <!-- Overview Dashboard -->
        <section class="dashboard-section glass-card" id="overview-section">
            <h2>Dashboard Overview</h2>
            <div class="dashboard-cards">
                <div class="overview-card">
                    <h3>Customers</h3>
                    <div class="stat-number"><?php echo $customersResult->num_rows; ?></div>
                    <p>Total Customers</p>
                </div>
                <div class="overview-card">
                    <h3>Loans</h3>
                    <div class="stat-number"><?php echo $loansResult->num_rows; ?></div>
                    <p>Active Loans</p>
                </div>
                <div class="overview-card">
                    <h3>Total Loan Amount</h3>
                    <?php 
                    // Calculate total loan amount
                    $totalAmount = 0;
                    $loansTotal = $conn->query("SELECT SUM(amount) as total FROM loans");
                    $totalRow = $loansTotal->fetch_assoc();
                    $totalAmount = $totalRow['total'] ?? 0;
                    ?>
                    <div class="stat-number">$<?php echo number_format($totalAmount, 2); ?></div>
                    <p>Total Portfolio</p>
                </div>
                <div class="overview-card">
                    <h3>Average Interest</h3>
                    <?php 
                    // Calculate average interest rate
                    $avgInterest = 0;
                    $interestQuery = $conn->query("SELECT AVG(interest_rate) as average FROM loans");
                    $avgRow = $interestQuery->fetch_assoc();
                    $avgInterest = $avgRow['average'] ?? 0;
                    ?>
                    <div class="stat-number"><?php echo number_format($avgInterest, 2); ?>%</div>
                    <p>Average Rate</p>
                </div>
            </div>
            
            <!-- Overview Table for Customers and Loans -->
            <div class="overview-table-container">
                <h3>Customer & Loan Overview</h3>
                <table class="data-table overview-table">
                    <thead>
                        <tr>
                            <th>Customer</th>
                            <th>Email</th>
                            <th>Active Loans</th>
                            <th>Total Amount</th>
                            <th>Avg. Interest</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Get customer summary with loan info
                        $customerSummaryQuery = "
                            SELECT 
                                c.id as customer_id,
                                c.name as customer_name,
                                c.email as email,
                                COUNT(l.id) as loan_count,
                                COALESCE(SUM(l.amount), 0) as total_amount,
                                COALESCE(AVG(l.interest_rate), 0) as avg_interest
                            FROM 
                                customers c
                            LEFT JOIN 
                                loans l ON c.id = l.customer_id
                            GROUP BY 
                                c.id
                            ORDER BY 
                                c.id ASC
                        ";
                        
                        $customerSummaryResult = $conn->query($customerSummaryQuery);
                        
                        while ($summary = $customerSummaryResult->fetch_assoc()): 
                        ?>
                            <tr>
                                <td><?php echo htmlspecialchars($summary['customer_name']); ?></td>
                                <td><?php echo htmlspecialchars($summary['email']); ?></td>
                                <td><?php echo $summary['loan_count']; ?></td>
                                <td>$<?php echo number_format($summary['total_amount'], 2); ?></td>
                                <td><?php echo number_format($summary['avg_interest'], 2); ?>%</td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="dashboard-section glass-card" id="customers-section">
            <h2>Customer Management</h2>
            
            <!-- Customer Form -->
            <div class="form-container">
                <h3>Add New Customer</h3>
                <form id="customer-form" action="add_customer.php" method="POST">
                    <div class="form-group">
                        <label for="name">Full Name</label>
                        <input type="text" id="name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" id="phone" name="phone" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Add Customer</button>
                </form>
            </div>
            
            <!-- Customers Table -->
            <div class="table-container">
                <h3>Customers List</h3>
                <?php if ($customersResult->num_rows > 0): ?>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Date Added</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($customer = $customersResult->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($customer['id']); ?></td>
                                    <td><?php echo htmlspecialchars($customer['name']); ?></td>
                                    <td><?php echo htmlspecialchars($customer['email']); ?></td>
                                    <td><?php echo htmlspecialchars($customer['phone']); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($customer['created_at'])); ?></td>
                                    <td class="action-buttons">
                                        <a href="edit_customer.php?id=<?php echo $customer['id']; ?>" class="btn btn-edit">Edit</a>
                                        <a href="delete_customer.php?id=<?php echo $customer['id']; ?>" class="btn btn-delete" onclick="return confirm('Are you sure you want to delete this customer?');">Delete</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p class="empty-state">No customers found. Add your first customer above.</p>
                <?php endif; ?>
            </div>
        </section>

        <section class="dashboard-section glass-card" id="loans-section">
            <h2>Loan Management</h2>
            
            <!-- Loan Form -->
            <div class="form-container">
                <h3>Add New Loan</h3>
                <?php if ($customerDropdownResult->num_rows > 0): ?>
                    <form id="loan-form" action="add_loan.php" method="POST">
                        <div class="form-group">
                            <label for="customer_id">Select Customer</label>
                            <select id="customer_id" name="customer_id" required>
                                <option value="">-- Select Customer --</option>
                                <?php while ($customerOption = $customerDropdownResult->fetch_assoc()): ?>
                                    <option value="<?php echo $customerOption['id']; ?>">
                                        <?php echo htmlspecialchars($customerOption['name']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="amount">Loan Amount</label>
                            <input type="number" id="amount" name="amount" step="0.01" min="0" required>
                        </div>
                        <div class="form-group">
                            <label for="interest_rate">Interest Rate (%)</label>
                            <input type="number" id="interest_rate" name="interest_rate" step="0.01" min="0" max="100" required>
                        </div>
                        <div class="form-group">
                            <label for="terms">Terms</label>
                            <input type="text" id="terms" name="terms" placeholder="e.g., 12 months" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Add Loan</button>
                    </form>
                <?php else: ?>
                    <p class="empty-state">Please add a customer first before creating a loan.</p>
                <?php endif; ?>
            </div>
            
            <!-- Loans Table -->
            <div class="table-container">
                <h3>Loans List</h3>
                <?php if ($loansResult->num_rows > 0): ?>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Customer</th>
                                <th>Amount</th>
                                <th>Interest Rate</th>
                                <th>Terms</th>
                                <th>Date Added</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($loan = $loansResult->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($loan['id']); ?></td>
                                    <td><?php echo htmlspecialchars($loan['customer_name']); ?></td>
                                    <td>$<?php echo number_format($loan['amount'], 2); ?></td>
                                    <td><?php echo htmlspecialchars($loan['interest_rate']); ?>%</td>
                                    <td><?php echo htmlspecialchars($loan['terms']); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($loan['created_at'])); ?></td>
                                    <td class="action-buttons">
                                        <a href="edit_loan.php?id=<?php echo $loan['id']; ?>" class="btn btn-edit">Edit</a>
                                        <a href="delete_loan.php?id=<?php echo $loan['id']; ?>" class="btn btn-delete" onclick="return confirm('Are you sure you want to delete this loan?');">Delete</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p class="empty-state">No loans found. Create your first loan above.</p>
                <?php endif; ?>
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
