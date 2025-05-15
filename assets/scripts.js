/**
 * Finance Management System
 * Main JavaScript file for UI interactions and animations
 */

document.addEventListener('DOMContentLoaded', function() {
    // Show message if exists and then fade it out
    const messages = document.querySelectorAll('.error-message, .success-message');
    if (messages.length > 0) {
        setTimeout(() => {
            messages.forEach(message => {
                fadeOut(message);
            });
        }, 3000);
    }
    
    // Form validation for customer form
    const customerForm = document.getElementById('customer-form');
    if (customerForm) {
        customerForm.addEventListener('submit', function(e) {
            if (!validateCustomerForm()) {
                e.preventDefault();
            }
        });
    }
    
    // Form validation for loan form
    const loanForm = document.getElementById('loan-form');
    if (loanForm) {
        loanForm.addEventListener('submit', function(e) {
            if (!validateLoanForm()) {
                e.preventDefault();
            }
        });
    }
    
    // Add smooth scrolling for section navigation
    setupSmoothScrolling();
    
    // Add input animations
    setupInputAnimations();
    
    // Initialize dashboard charts if they exist
    initializeCharts();
});

/**
 * Initialize the dashboard charts if they exist
 */
function initializeCharts() {
    // Check if loanData variable and chart containers exist
    if (typeof loanData !== 'undefined' && 
        document.getElementById('loanDistributionChart') && 
        document.getElementById('customerGrowthChart')) {
        
        // Set chart defaults
        Chart.defaults.color = 'rgba(255, 255, 255, 0.7)';
        Chart.defaults.borderColor = 'rgba(255, 255, 255, 0.1)';
        
        // Extract data for loan distribution chart
        const loanAmounts = loanData.map(loan => loan.amount);
        const customerNames = loanData.map(loan => loan.customer);
        const interestRates = loanData.map(loan => loan.interest);
        
        // Create loan distribution chart
        const loanDistributionChart = new Chart(
            document.getElementById('loanDistributionChart').getContext('2d'),
            {
                type: 'bar',
                data: {
                    labels: customerNames,
                    datasets: [{
                        label: 'Loan Amount ($)',
                        data: loanAmounts,
                        backgroundColor: 'rgba(108, 92, 231, 0.7)',
                        borderColor: 'rgba(108, 92, 231, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return '$' + value;
                                }
                            }
                        }
                    }
                }
            }
        );
        
        // Create customer growth chart (dummy data since we don't track dates)
        const customerGrowthChart = new Chart(
            document.getElementById('customerGrowthChart').getContext('2d'),
            {
                type: 'line',
                data: {
                    labels: customerNames,
                    datasets: [{
                        label: 'Interest Rate (%)',
                        data: interestRates,
                        backgroundColor: 'rgba(162, 155, 254, 0.3)',
                        borderColor: 'rgba(162, 155, 254, 1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return value + '%';
                                }
                            }
                        }
                    }
                }
            }
        );
    }
}

/**
 * Validates the customer form
 * @returns {boolean} True if valid, false otherwise
 */
function validateCustomerForm() {
    const nameInput = document.getElementById('name');
    const emailInput = document.getElementById('email');
    const phoneInput = document.getElementById('phone');
    
    let isValid = true;
    
    // Reset previous errors
    resetFormErrors();
    
    // Validate name
    if (!nameInput.value.trim()) {
        showError(nameInput, 'Name is required');
        isValid = false;
    }
    
    // Validate email
    if (!emailInput.value.trim()) {
        showError(emailInput, 'Email is required');
        isValid = false;
    } else if (!isValidEmail(emailInput.value)) {
        showError(emailInput, 'Please enter a valid email address');
        isValid = false;
    }
    
    // Validate phone
    if (!phoneInput.value.trim()) {
        showError(phoneInput, 'Phone number is required');
        isValid = false;
    }
    
    return isValid;
}

/**
 * Validates the loan form
 * @returns {boolean} True if valid, false otherwise
 */
function validateLoanForm() {
    const customerSelect = document.getElementById('customer_id');
    const amountInput = document.getElementById('amount');
    const interestInput = document.getElementById('interest_rate');
    const termsInput = document.getElementById('terms');
    
    let isValid = true;
    
    // Reset previous errors
    resetFormErrors();
    
    // Validate customer selection
    if (customerSelect.value === '') {
        showError(customerSelect, 'Please select a customer');
        isValid = false;
    }
    
    // Validate amount
    if (!amountInput.value.trim()) {
        showError(amountInput, 'Loan amount is required');
        isValid = false;
    } else if (parseFloat(amountInput.value) <= 0) {
        showError(amountInput, 'Amount must be greater than zero');
        isValid = false;
    }
    
    // Validate interest rate
    if (!interestInput.value.trim()) {
        showError(interestInput, 'Interest rate is required');
        isValid = false;
    } else {
        const rate = parseFloat(interestInput.value);
        if (rate < 0 || rate > 100) {
            showError(interestInput, 'Interest rate must be between 0 and 100');
            isValid = false;
        }
    }
    
    // Validate terms
    if (!termsInput.value.trim()) {
        showError(termsInput, 'Terms are required');
        isValid = false;
    }
    
    return isValid;
}

/**
 * Shows error message for a form field
 * @param {HTMLElement} input - The input element
 * @param {string} message - Error message to display
 */
function showError(input, message) {
    const formGroup = input.closest('.form-group');
    const errorElement = document.createElement('div');
    errorElement.className = 'input-error';
    errorElement.textContent = message;
    errorElement.style.color = 'var(--error)';
    errorElement.style.fontSize = '0.8rem';
    errorElement.style.marginTop = '5px';
    formGroup.appendChild(errorElement);
    
    // Add error styling to input
    input.style.borderColor = 'var(--error)';
}

/**
 * Reset all form errors
 */
function resetFormErrors() {
    // Remove all error messages
    const errorMessages = document.querySelectorAll('.input-error');
    errorMessages.forEach(error => error.remove());
    
    // Reset input styling
    const inputs = document.querySelectorAll('input, select, textarea');
    inputs.forEach(input => {
        input.style.borderColor = '';
    });
}

/**
 * Validates email format
 * @param {string} email - Email to validate
 * @returns {boolean} True if valid, false otherwise
 */
function isValidEmail(email) {
    const re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return re.test(email.toLowerCase());
}

/**
 * Fade out an element
 * @param {HTMLElement} element - Element to fade out
 */
function fadeOut(element) {
    let opacity = 1;
    const timer = setInterval(function() {
        if (opacity <= 0.1) {
            clearInterval(timer);
            element.style.display = 'none';
        }
        element.style.opacity = opacity;
        opacity -= 0.1;
    }, 50);
}

/**
 * Setup smooth scrolling for section navigation
 */
function setupSmoothScrolling() {
    const navigationLinks = document.querySelectorAll('a[href^="#"]');
    
    navigationLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            const targetId = this.getAttribute('href');
            if (targetId === '#') return;
            
            const targetElement = document.querySelector(targetId);
            if (targetElement) {
                window.scrollTo({
                    top: targetElement.offsetTop - 80, // Adjust for header
                    behavior: 'smooth'
                });
            }
        });
    });
}

/**
 * Setup animations for input fields
 */
function setupInputAnimations() {
    const inputs = document.querySelectorAll('input, select, textarea');
    
    inputs.forEach(input => {
        // Add focus effect
        input.addEventListener('focus', function() {
            this.parentElement.classList.add('input-focused');
        });
        
        // Remove focus effect
        input.addEventListener('blur', function() {
            this.parentElement.classList.remove('input-focused');
        });
    });
}

/**
 * Add table row highlighting
 */
document.addEventListener('DOMContentLoaded', function() {
    const tableRows = document.querySelectorAll('.data-table tbody tr');
    
    tableRows.forEach(row => {
        row.addEventListener('mouseenter', function() {
            this.style.backgroundColor = 'rgba(255, 255, 255, 0.05)';
        });
        
        row.addEventListener('mouseleave', function() {
            this.style.backgroundColor = '';
        });
    });
});
