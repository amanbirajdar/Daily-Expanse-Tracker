<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get user's expenses for the current month
$current_month = date('Y-m');
$stmt = $conn->prepare("
    SELECT SUM(amount) as total_amount, COUNT(*) as total_expenses 
    FROM expenses 
    WHERE user_id = ? AND DATE_FORMAT(date, '%Y-%m') = ?
");
$stmt->execute([$_SESSION['user_id'], $current_month]);
$monthly_stats = $stmt->fetch();

// Get recent expenses
$stmt = $conn->prepare("
    SELECT * FROM expenses 
    WHERE user_id = ? 
    ORDER BY date DESC 
    LIMIT 5
");
$stmt->execute([$_SESSION['user_id']]);
$recent_expenses = $stmt->fetchAll();

// Get expenses by category
$stmt = $conn->prepare("
    SELECT category, SUM(amount) as total 
    FROM expenses 
    WHERE user_id = ? AND DATE_FORMAT(date, '%Y-%m') = ?
    GROUP BY category
");
$stmt->execute([$_SESSION['user_id'], $current_month]);
$category_stats = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Daily Expense  Tracker</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* Custom gradient backgrounds */
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .card-gradient {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }
        
        .card-gradient-2 {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }
        
        .card-gradient-3 {
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
        }
        
        .card-gradient-4 {
            background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
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
            transform: scale(1.05);
        }
        
        /* Glass morphism effect */
        .glass {
            background: rgba(255, 255, 255, 0.25);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.18);
        }
        
        /* Custom scrollbar */
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
        }
        
        .custom-scrollbar::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }
        
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 10px;
        }
        
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #555;
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
                        <i class="fas fa-wallet text-white text-2xl mr-3"></i>
                    </div>
                    <a href="dashboard.php" class="text-2xl font-bold text-white hover:text-yellow-300 transition duration-300">
                        Daily Expense  Tracker
                    </a>
                </div>
                <div class="flex items-center space-x-6">
                    <div class="glass rounded-full px-4 py-2">
                        <span class="text-white font-medium">
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
        <!-- Welcome Section -->
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold text-gray-800 mb-2">
                <i class="fas fa-chart-line text-blue-500 mr-3"></i>
                Dashboard Overview
            </h1>
            <p class="text-gray-600 text-lg">Track your expenses and manage your budget efficiently</p>
        </div>

        <!-- Quick Stats -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-2xl shadow-xl p-6 hover-scale border-l-4 border-blue-500">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-gray-500 text-sm font-medium uppercase tracking-wide">Total Expenses</h3>
                        <p class="text-3xl font-bold text-gray-800 mt-2">₹<?php echo number_format($monthly_stats['total_amount'] ?? 0, 2); ?></p>
                        <p class="text-sm text-gray-500 mt-1">This month</p>
                    </div>
                    <div class="card-gradient-2 p-4 rounded-full animate-float">
                        <i class="fas fa-wallet text-white text-2xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-xl p-6 hover-scale border-l-4 border-green-500">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-gray-500 text-sm font-medium uppercase tracking-wide">Transactions</h3>
                        <p class="text-3xl font-bold text-gray-800 mt-2"><?php echo $monthly_stats['total_expenses'] ?? 0; ?></p>
                        <p class="text-sm text-gray-500 mt-1">Total count</p>
                    </div>
                    <div class="card-gradient-3 p-4 rounded-full animate-float" style="animation-delay: 1s;">
                        <i class="fas fa-receipt text-white text-2xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-xl p-6 hover-scale border-l-4 border-purple-500">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-gray-500 text-sm font-medium uppercase tracking-wide">Daily Average</h3>
                        <p class="text-3xl font-bold text-gray-800 mt-2">₹<?php echo number_format(($monthly_stats['total_amount'] ?? 0) / date('t'), 2); ?></p>
                        <p class="text-sm text-gray-500 mt-1">Per day</p>
                    </div>
                    <div class="card-gradient-4 p-4 rounded-full animate-float" style="animation-delay: 2s;">
                        <i class="fas fa-chart-pie text-white text-2xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts and Recent Expenses -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <!-- Category Distribution Chart -->
            <div class="bg-white rounded-2xl shadow-xl p-6">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-2xl font-bold text-gray-800">
                        <i class="fas fa-chart-doughnut text-purple-500 mr-3"></i>
                        Expense Categories
                    </h2>
                    <div class="bg-purple-100 text-purple-600 px-3 py-1 rounded-full text-sm font-medium">
                        This Month
                    </div>
                </div>
                <div class="relative">
                    <canvas id="categoryChart" class="max-h-80"></canvas>
                </div>
            </div>

            <!-- Recent Expenses -->
            <div class="bg-white rounded-2xl shadow-xl p-6">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-2xl font-bold text-gray-800">
                        <i class="fas fa-clock text-blue-500 mr-3"></i>
                        Recent Expenses
                    </h2>
                    <a href="reports.php" class="bg-blue-100 text-blue-600 px-3 py-1 rounded-full text-sm font-medium hover:bg-blue-200 transition duration-300">
                        View All
                    </a>
                </div>
                <div class="space-y-4 custom-scrollbar max-h-80 overflow-y-auto">
                    <?php if (empty($recent_expenses)): ?>
                        <div class="text-center py-8">
                            <i class="fas fa-receipt text-gray-300 text-4xl mb-4"></i>
                            <p class="text-gray-500">No expenses recorded yet</p>
                            <a href="add_expense.php" class="text-blue-500 hover:text-blue-700 font-medium">Add your first expense</a>
                        </div>
                    <?php else: ?>
                        <?php foreach ($recent_expenses as $expense): ?>
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl hover:bg-gray-100 transition duration-300">
                            <div class="flex items-center">
                                <div class="w-12 h-12 bg-gradient-to-r from-blue-400 to-purple-500 rounded-full flex items-center justify-center text-white font-bold">
                                    <?php echo strtoupper(substr($expense['category'], 0, 2)); ?>
                                </div>
                                <div class="ml-4">
                                    <h3 class="font-semibold text-gray-800"><?php echo htmlspecialchars($expense['description']); ?></h3>
                                    <p class="text-sm text-gray-500">
                                        <i class="fas fa-tag mr-1"></i>
                                        <?php echo htmlspecialchars($expense['category']); ?>
                                    </p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="font-bold text-lg text-gray-800">₹<?php echo number_format($expense['amount'], 2); ?></p>
                                <p class="text-sm text-gray-500">
                                    <i class="fas fa-calendar mr-1"></i>
                                    <?php echo date('M d, Y', strtotime($expense['date'])); ?>
                                </p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white rounded-2xl shadow-xl p-6">
            <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">
                <i class="fas fa-bolt text-yellow-500 mr-3"></i>
                Quick Actions
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <a href="add_expense.php" class="group relative overflow-hidden rounded-xl p-6 text-center text-white card-gradient-2 hover-scale">
                    <div class="relative z-10">
                        <i class="fas fa-plus-circle text-4xl mb-4 group-hover:animate-bounce"></i>
                        <h3 class="font-bold text-lg">Add Expense</h3>
                        <p class="text-sm opacity-90">Record new expense</p>
                    </div>
                    <div class="absolute inset-0 bg-black opacity-0 group-hover:opacity-10 transition duration-300"></div>
                </a>
                
                <a href="budget.php" class="group relative overflow-hidden rounded-xl p-6 text-center text-white card-gradient-3 hover-scale">
                    <div class="relative z-10">
                        <i class="fas fa-chart-pie text-4xl mb-4 group-hover:animate-bounce"></i>
                        <h3 class="font-bold text-lg">Set Budget</h3>
                        <p class="text-sm opacity-90">Manage your budget</p>
                    </div>
                    <div class="absolute inset-0 bg-black opacity-0 group-hover:opacity-10 transition duration-300"></div>
                </a>
                
                <a href="reports.php" class="group relative overflow-hidden rounded-xl p-6 text-center text-white card-gradient hover-scale">
                    <div class="relative z-10">
                        <i class="fas fa-chart-line text-4xl mb-4 group-hover:animate-bounce"></i>
                        <h3 class="font-bold text-lg">View Reports</h3>
                        <p class="text-sm opacity-90">Analyze spending</p>
                    </div>
                    <div class="absolute inset-0 bg-black opacity-0 group-hover:opacity-10 transition duration-300"></div>
                </a>
                
                <a href="profile.php" class="group relative overflow-hidden rounded-xl p-6 text-center text-white card-gradient-4 hover-scale">
                    <div class="relative z-10">
                        <i class="fas fa-user text-4xl mb-4 group-hover:animate-bounce"></i>
                        <h3 class="font-bold text-lg">Profile</h3>
                        <p class="text-sm opacity-90">Account settings</p>
                    </div>
                    <div class="absolute inset-0 bg-black opacity-0 group-hover:opacity-10 transition duration-300"></div>
                </a>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="gradient-bg text-white mt-12">
        <div class="max-w-7xl mx-auto px-4 py-8">
            <div class="text-center">
                <div class="flex justify-center items-center mb-4">
                    <i class="fas fa-wallet text-2xl mr-3"></i>
                    <span class="text-xl font-bold">Daily Expense  Tracker</span>
                </div>
                <p class="text-gray-200 mb-4">Your personal expense tracking companion</p>
                <div class="flex justify-center space-x-6 text-sm">
                    <span> <p>&copy; 2026 Daily Expense  Tracker. All rights reserved.</p>
                    <span>•</span>
                    <span>Made with ❤️ for better financial management</span>
                </div>
            </div>
        </div>
    </footer>

    <script>
        // Enhanced Category Distribution Chart
        const categoryData = <?php echo json_encode($category_stats); ?>;
        const ctx = document.getElementById('categoryChart').getContext('2d');
        
        // Generate beautiful colors
        const colors = [
            '#FF6B6B', '#4ECDC4', '#45B7D1', '#96CEB4', '#FFEAA7',
            '#DDA0DD', '#98D8C8', '#F7DC6F', '#BB8FCE', '#85C1E9'
        ];
        
        new Chart(ctx, {
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
                        displayColors: true,
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

        // Add some interactive effects
        document.addEventListener('DOMContentLoaded', function() {
            // Animate stats on load
            const stats = document.querySelectorAll('.hover-scale');
            stats.forEach((stat, index) => {
                setTimeout(() => {
                    stat.style.opacity = '0';
                    stat.style.transform = 'translateY(20px)';
                    stat.style.transition = 'all 0.6s ease';
                    
                    setTimeout(() => {
                        stat.style.opacity = '1';
                        stat.style.transform = 'translateY(0)';
                    }, 100);
                }, index * 200);
            });
        });
    </script>
</body>
</html> 