-- Create the finance database
CREATE DATABASE IF NOT EXISTS finance_db;

-- Use the finance database
USE finance_db;

-- Admin table for user authentication
CREATE TABLE IF NOT EXISTS admin (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(100) NOT NULL,
  password VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Customers table
CREATE TABLE IF NOT EXISTS customers (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  email VARCHAR(255) NOT NULL,
  phone VARCHAR(20) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Loans table with foreign key to customers
CREATE TABLE IF NOT EXISTS loans (
  id INT AUTO_INCREMENT PRIMARY KEY,
  customer_id INT NOT NULL,
  amount DECIMAL(10,2) NOT NULL,
  interest_rate FLOAT NOT NULL,
  terms VARCHAR(100) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE
);

-- Insert a default admin user (username: admin, password: admin123)
-- In a production environment, use a properly hashed password!
INSERT INTO admin (username, password) 
VALUES ('admin', 'admin123')
ON DUPLICATE KEY UPDATE username = 'admin';

-- Sample customers for testing (optional)
INSERT INTO customers (name, email, phone) VALUES
('John Doe', 'john@example.com', '555-123-4567'),
('Jane Smith', 'jane@example.com', '555-987-6543'),
('Michael Johnson', 'michael@example.com', '555-456-7890');

-- Sample loans for testing (optional)
INSERT INTO loans (customer_id, amount, interest_rate, terms) VALUES
(1, 5000.00, 5.25, '12 months'),
(2, 10000.00, 4.75, '24 months'),
(3, 15000.00, 6.00, '36 months');
