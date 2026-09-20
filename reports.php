<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get filter parameters
$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_GET['end_date'] ?? date('Y-m-t');
$category = $_GET['category'] ?? '';

// Build query conditions
$conditions = ["user_id = ?"];
$params = [$_SESSION['user_id']];

if ($category) {
    $conditions[] = "category = ?";
    $params[] = $category;
}

$date_condition = "date BETWEEN ? AND ?";
$conditions[] = $date_condition;
$params[] = $start_date;
$params[] = $end_date;

$where_clause = implode(" AND ", $conditions);

// Get total expenses
$stmt = $conn->prepare("
    SELECT SUM(amount) as total_amount, COUNT(*) as total_expenses 
    FROM expenses 
    WHERE $where_clause
");
$stmt->execute($params);
$total_stats = $stmt->fetch();

// Get expenses by category
$stmt = $conn->prepare("
    SELECT category, SUM(amount) as total, COUNT(*) as count
    FROM expenses 
    WHERE $where_clause
    GROUP BY category
    ORDER BY total DESC
");
$stmt->execute($params);
$category_stats = $stmt->fetchAll();

// Get daily expenses for chart
$stmt = $conn->prepare("
    SELECT DATE(date) as date, SUM(amount) as total
    FROM expenses 
    WHERE $where_clause
    GROUP BY DATE(date)
    ORDER BY date
");
$stmt->execute($params);
$daily_stats = $stmt->fetchAll();

// Get top expenses
$stmt = $conn->prepare("
    SELECT * FROM expenses 
    WHERE $where_clause
    ORDER BY amount DESC 
    LIMIT 5
");
$stmt->execute($params);
$top_expenses = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expense Reports - Daily Expense  Tracker</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* Custom gradient backgrounds */
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .reports-gradient {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }
        
        .card-gradient-1 {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }
        
        .card-gradient-2 {
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
        }
        
        .card-gradient-3 {
            background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
        }
        
        /* Glass morphism effect */
        .glass {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 25px 45px rgba(0, 0, 0, 0.1);
        }
        
        /* Animated elements */
        .animate-float {
            animation: float 6s ease-in-out infinite;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
        
        .hover-scale {
            transition: transform 0.3s ease;
        }
        
        .hover-scale:hover {
            transform: scale(1.02);
        }
        
        /* Chart container styling */
        .chart-container {
            position: relative;
            height: 300px;
        }
        
        /* Filter form styling */
        .filter-input {
            transition: all 0.3s ease;
        }
        
        .filter-input:focus {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        
        /* Expense item hover effects */
        .expense-item {
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .expense-item:hover {
            transform: translateX(5px);
            background: linear-gradient(90deg, #f8fafc, #e2e8f0);
        }
        
        .expense-item::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 4px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            transform: scaleY(0);
            transition: transform 0.3s ease;
        }
        
        .expense-item:hover::before {
            transform: scaleY(1);
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Navigation -->
    <nav class="gradient-bg shadow-xl">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center">
                    <div class="animate-float">
                        <i class="fas fa-chart-line text-white text-2xl mr-3"></i>
                    </div>
                    <a href="dashboard.php" class="text-2xl font-bold text-white hover:text-yellow-300 transition duration-300">
                        Daily Expense  Tracker
                    </a>
                </div>
                <div class="flex items-center space-x-6">
                    <a href="dashboard.php" class="text-white hover:text-yellow-300 transition duration-300 font-medium">
                        <i class="fas fa-home mr-2"></i>Dashboard
                    </a>
                     <div class="glass rounded-full px-4 py-2">
                        <span class="text-black font-medium">
                            <i class="fas fa-user-circle mr-2"></i>
                            Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>
                        </span>
                    </div>
                    <a href="logout.php" class="text-white hover:text-red-300 transition duration-300 font-medium">
                        <i class="fas fa-sign-out-alt mr-2"></i>Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 py-8">
        <!-- Header Section -->
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold text-gray-800 mb-2">
                <i class="fas fa-chart-bar text-purple-500 mr-3"></i>
                Expense Reports
            </h1>
            <p class="text-gray-600 text-lg">Analyze your spending patterns and financial insights</p>
        </div>

        <!-- Filters -->
        <div class="glass rounded-2xl p-8 shadow-2xl mb-8 hover-scale">
            <div class="text-center mb-6">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-r from-purple-500 to-pink-600 rounded-full mb-4">
                    <i class="fas fa-filter text-white text-2xl"></i>
                </div>
                <h2 class="text-2xl font-bold text-gray-800">Report Filters</h2>
                <p class="text-gray-600 mt-2">Customize your expense analysis</p>
            </div>

            <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-calendar-alt text-blue-500 mr-2"></i>Start Date
                    </label>
                    <input type="date" name="start_date" value="<?php echo $start_date; ?>" 
                        class="filter-input w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-blue-500 focus:outline-none bg-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-calendar-check text-green-500 mr-2"></i>End Date
                    </label>
                    <input type="date" name="end_date" value="<?php echo $end_date; ?>" 
                        class="filter-input w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-green-500 focus:outline-none bg-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-tags text-purple-500 mr-2"></i>Category
                    </label>
                    <select name="category" class="filter-input w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-purple-500 focus:outline-none bg-white">
                        <option value="">🏷️ All Categories</option>
                        
                        <!-- Essential Categories -->
                        <optgroup label="🏠 Essential">
                            <option value="Rent" <?php echo $category === 'Rent' ? 'selected' : ''; ?>>🏠 Rent</option>
                            <option value="Electricity" <?php echo $category === 'Electricity' ? 'selected' : ''; ?>>⚡ Electricity</option>
                            <option value="Food" <?php echo $category === 'Food' ? 'selected' : ''; ?>>🍽️ Food</option>
                            <option value="Groceries" <?php echo $category === 'Groceries' ? 'selected' : ''; ?>>🛒 Groceries</option>
                            <option value="Transport" <?php echo $category === 'Transport' ? 'selected' : ''; ?>>🚗 Transport</option>
                        </optgroup>
                        
                        <!-- Bills & Services -->
                        <optgroup label="📄 Bills & Services">
                            <option value="Bills" <?php echo $category === 'Bills' ? 'selected' : ''; ?>>📄 Bills</option>
                            <option value="Recharge" <?php echo $category === 'Recharge' ? 'selected' : ''; ?>>📱 Recharge</option>
                            <option value="Fuel" <?php echo $category === 'Fuel' ? 'selected' : ''; ?>>⛽ Fuel</option>
                            <option value="Insurance" <?php echo $category === 'Insurance' ? 'selected' : ''; ?>>🛡️ Insurance</option>
                        </optgroup>
                        
                        <!-- Lifestyle -->
                        <optgroup label="🛍️ Lifestyle">
                            <option value="Shopping" <?php echo $category === 'Shopping' ? 'selected' : ''; ?>>🛍️ Shopping</option>
                            <option value="Entertainment" <?php echo $category === 'Entertainment' ? 'selected' : ''; ?>>🎬 Entertainment</option>
                            <option value="Travel" <?php echo $category === 'Travel' ? 'selected' : ''; ?>>✈️ Travel</option>
                        </optgroup>
                        
                        <!-- Others -->
                        <optgroup label="🎁 Others">
                            <option value="Healthcare" <?php echo $category === 'Healthcare' ? 'selected' : ''; ?>>🏥 Healthcare</option>
                            <option value="Education" <?php echo $category === 'Education' ? 'selected' : ''; ?>>🎓 Education</option>
                            <option value="Other" <?php echo $category === 'Other' ? 'selected' : ''; ?>>📝 Other</option>
                        </optgroup>
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="submit" class="w-full reports-gradient text-white font-bold py-4 px-6 rounded-xl hover:shadow-lg transform hover:scale-105 transition duration-300">
                        <i class="fas fa-search mr-2"></i>
                        Generate Report
                    </button>
                </div>
            </form>
        </div>

        <!-- Summary Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="glass rounded-2xl p-6 shadow-xl hover-scale border-l-4 border-blue-500">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-gray-500 text-sm font-medium uppercase tracking-wide">Total Expenses</h3>
                        <p class="text-3xl font-bold text-gray-800 mt-2">₹<?php echo number_format($total_stats['total_amount'] ?? 0, 0); ?></p>
                        <p class="text-sm text-gray-500 mt-1">Selected period</p>
                    </div>
                    <div class="card-gradient-1 p-4 rounded-full animate-float">
                        <i class="fas fa-wallet text-white text-2xl"></i>
                    </div>
                </div>
            </div>

            <div class="glass rounded-2xl p-6 shadow-xl hover-scale border-l-4 border-green-500">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-gray-500 text-sm font-medium uppercase tracking-wide">Transactions</h3>
                        <p class="text-3xl font-bold text-gray-800 mt-2"><?php echo $total_stats['total_expenses'] ?? 0; ?></p>
                        <p class="text-sm text-gray-500 mt-1">Total count</p>
                    </div>
                    <div class="card-gradient-2 p-4 rounded-full animate-float" style="animation-delay: 1s;">
                        <i class="fas fa-receipt text-white text-2xl"></i>
                    </div>
                </div>
            </div>

            <div class="glass rounded-2xl p-6 shadow-xl hover-scale border-l-4 border-purple-500">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-gray-500 text-sm font-medium uppercase tracking-wide">Daily Average</h3>
                        <p class="text-3xl font-bold text-gray-800 mt-2">₹<?php echo number_format(($total_stats['total_amount'] ?? 0) / max(1, (strtotime($end_date) - strtotime($start_date)) / (24 * 60 * 60)), 0); ?></p>
                        <p class="text-sm text-gray-500 mt-1">Per day</p>
                    </div>
                    <div class="card-gradient-3 p-4 rounded-full animate-float" style="animation-delay: 2s;">
                        <i class="fas fa-chart-line text-white text-2xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts and Details -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <!-- Daily Expenses Chart -->
            <div class="glass rounded-2xl p-8 shadow-2xl">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-2xl font-bold text-gray-800">
                        <i class="fas fa-chart-area text-blue-500 mr-3"></i>
                        Daily Trend
                    </h2>
                    <div class="bg-blue-100 text-blue-600 px-3 py-1 rounded-full text-sm font-medium">
                        <?php echo count($daily_stats); ?> Days
                    </div>
                </div>
                <div class="chart-container">
                    <canvas id="dailyChart"></canvas>
                </div>
            </div>

            <!-- Category Distribution -->
            <div class="glass rounded-2xl p-8 shadow-2xl">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-2xl font-bold text-gray-800">
                        <i class="fas fa-chart-pie text-purple-500 mr-3"></i>
                        Category Breakdown
                    </h2>
                    <div class="bg-purple-100 text-purple-600 px-3 py-1 rounded-full text-sm font-medium">
                        <?php echo count($category_stats); ?> Categories
                    </div>
                </div>
                <div class="chart-container">
                    <canvas id="categoryChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Top Expenses -->
        <div class="glass rounded-2xl p-8 shadow-2xl">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-bold text-gray-800">
                    <i class="fas fa-trophy text-yellow-500 mr-3"></i>
                    Top Expenses
                </h2>
                <div class="bg-yellow-100 text-yellow-600 px-3 py-1 rounded-full text-sm font-medium">
                    Highest Amounts
                </div>
            </div>

            <div class="space-y-4">
                <?php if (empty($top_expenses)): ?>
                    <div class="text-center py-12">
                        <i class="fas fa-receipt text-gray-300 text-6xl mb-4"></i>
                        <h3 class="text-xl font-semibold text-gray-500 mb-2">No Expenses Found</h3>
                        <p class="text-gray-400 mb-4">No expenses match your current filter criteria</p>
                        <a href="add_expense.php" class="inline-flex items-center text-blue-500 font-medium hover:text-blue-700">
                            <i class="fas fa-plus mr-2"></i>
                            Add your first expense
                        </a>
                    </div>
                <?php else: ?>
                    <?php foreach ($top_expenses as $index => $expense): ?>
                    <div class="expense-item flex items-center justify-between p-4 rounded-xl">
                        <div class="flex items-center">
                            <div class="w-12 h-12 bg-gradient-to-r from-purple-400 to-pink-500 rounded-full flex items-center justify-center text-white font-bold mr-4">
                                #<?php echo $index + 1; ?>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-800 text-lg"><?php echo htmlspecialchars($expense['description']); ?></h3>
                                <div class="flex items-center mt-1">
                                    <span class="text-sm text-gray-500 mr-3">
                                        <i class="fas fa-tag mr-1"></i>
                                        <?php echo htmlspecialchars($expense['category']); ?>
                                    </span>
                                    <span class="text-sm text-gray-500">
                                        <i class="fas fa-calendar mr-1"></i>
                                        <?php echo date('M d, Y', strtotime($expense['date'])); ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-2xl font-bold text-gray-800">₹<?php echo number_format($expense['amount'], 0); ?></p>
                            <div class="text-sm text-gray-500">
                                <?php if ($expense['receipt']): ?>
                                    <i class="fas fa-paperclip mr-1"></i>Receipt attached
                                <?php else: ?>
                                    <i class="fas fa-file-alt mr-1"></i>No receipt
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="gradient-bg text-white mt-12">
        <div class="max-w-7xl mx-auto px-4 py-8">
            <div class="text-center">
                <div class="flex justify-center items-center mb-4">
                    <i class="fas fa-chart-bar text-2xl mr-3"></i>
                    <span class="text-xl font-bold">Daily Expense  Tracker Reports</span>
                </div>
                <p class="text-gray-200 mb-4">Detailed insights for smarter financial decisions</p>
                <div class="flex justify-center space-x-6 text-sm">
                    <span>&copy; 2026 Daily Expense  Tracker. All rights reserved.</span>
                    <span>•</span>
                    <span>Made with ❤️ for better financial management</span>
                </div>
            </div>
        </div>
    </footer>

    <script>
        // Enhanced Daily Expenses Chart
        const dailyData = <?php echo json_encode($daily_stats); ?>;
        const dailyCtx = document.getElementById('dailyChart').getContext('2d');
        
        new Chart(dailyCtx, {
            type: 'line',
            data: {
                labels: dailyData.map(item => new Date(item.date).toLocaleDateString('en-US', { month: 'short', day: 'numeric' })),
                datasets: [{
                    label: 'Daily Expenses (₹)',
                    data: dailyData.map(item => item.total),
                    borderColor: '#667eea',
                    backgroundColor: 'rgba(102, 126, 234, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#667eea',
                    pointBorderColor: '#ffffff',
                    pointBorderWidth: 2,
                    pointRadius: 6,
                    pointHoverRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0,0,0,0.8)',
                        titleColor: '#ffffff',
                        bodyColor: '#ffffff',
                        borderColor: '#667eea',
                        borderWidth: 1,
                        cornerRadius: 10,
                        callbacks: {
                            label: function(context) {
                                return `Amount: ₹${context.parsed.y.toLocaleString()}`;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0,0,0,0.1)'
                        },
                        ticks: {
                            callback: function(value) {
                                return '₹' + value.toLocaleString();
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                },
                animation: {
                    duration: 2000,
                    easing: 'easeInOutQuart'
                }
            }
        });

        // Enhanced Category Distribution Chart
        const categoryData = <?php echo json_encode($category_stats); ?>;
        const categoryCtx = document.getElementById('categoryChart').getContext('2d');
        
        const colors = [
            '#FF6B6B', '#4ECDC4', '#45B7D1', '#96CEB4', '#FFEAA7',
            '#DDA0DD', '#98D8C8', '#F7DC6F', '#BB8FCE', '#85C1E9'
        ];
        
        new Chart(categoryCtx, {
            type: 'doughnut',
            data: {
                labels: categoryData.map(item => item.category),
                datasets: [{
                    data: categoryData.map(item => item.total),
                    backgroundColor: colors.slice(0, categoryData.length),
                    borderWidth: 3,
                    borderColor: '#ffffff',
                    hoverBorderWidth: 5,
                    hoverBorderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            usePointStyle: true,
                            font: {
                                size: 12,
                                weight: 'bold'
                            }
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0,0,0,0.8)',
                        titleColor: '#ffffff',
                        bodyColor: '#ffffff',
                        borderColor: '#ffffff',
                        borderWidth: 1,
                        cornerRadius: 10,
                        callbacks: {
                            label: function(context) {
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = ((context.parsed / total) * 100).toFixed(1);
                                return `${context.label}: ₹${context.parsed.toLocaleString()} (${percentage}%)`;
                            }
                        }
                    }
                },
                animation: {
                    animateRotate: true,
                    animateScale: true,
                    duration: 2000
                }
            }
        });

        // Enhanced interactions
        document.addEventListener('DOMContentLoaded', function() {
            // Animate expense items on load
            const expenseItems = document.querySelectorAll('.expense-item');
            expenseItems.forEach((item, index) => {
                item.style.opacity = '0';
                item.style.transform = 'translateX(-20px)';
                item.style.transition = 'all 0.6s ease';
                
                setTimeout(() => {
                    item.style.opacity = '1';
                    item.style.transform = 'translateX(0)';
                }, index * 100);
            });

            // Form interactions
            const filterInputs = document.querySelectorAll('.filter-input');
            filterInputs.forEach(input => {
                input.addEventListener('focus', function() {
                    this.parentElement.style.transform = 'scale(1.02)';
                });
                
                input.addEventListener('blur', function() {
                    this.parentElement.style.transform = 'scale(1)';
                });
            });
        });
    </script>
</body>
</html> 