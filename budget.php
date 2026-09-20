<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Handle budget submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $category = $_POST['category'];
    $amount = $_POST['amount'];
    $period = $_POST['period'];
    $start_date = $_POST['start_date'];
    
    // Calculate end date based on period
    $end_date = date('Y-m-d', strtotime($start_date));
    switch ($period) {
        case 'daily':
            $end_date = date('Y-m-d', strtotime($start_date . ' +1 day'));
            break;
        case 'weekly':
            $end_date = date('Y-m-d', strtotime($start_date . ' +1 week'));
            break;
        case 'monthly':
            $end_date = date('Y-m-d', strtotime($start_date . ' +1 month'));
            break;
        case 'yearly':
            $end_date = date('Y-m-d', strtotime($start_date . ' +1 year'));
            break;
    }

    $stmt = $conn->prepare("INSERT INTO budgets (user_id, category, amount, period, start_date, end_date) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$_SESSION['user_id'], $category, $amount, $period, $start_date, $end_date]);
    
    header("Location: budget.php?success=1");
    exit();
}

// Get current budgets
$stmt = $conn->prepare("
    SELECT b.*, 
           COALESCE(SUM(e.amount), 0) as spent_amount
    FROM budgets b
    LEFT JOIN expenses e ON e.user_id = b.user_id 
        AND e.category = b.category 
        AND e.date BETWEEN b.start_date AND b.end_date
    WHERE b.user_id = ? AND b.end_date >= CURDATE()
    GROUP BY b.id
");
$stmt->execute([$_SESSION['user_id']]);
$budgets = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Budget Management - Expence</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Custom gradient backgrounds */
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .budget-gradient {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }
        
        .card-gradient-1 {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .card-gradient-2 {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }
        
        .card-gradient-3 {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
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
        
        /* Progress bar animations */
        .progress-bar {
            transition: width 1s ease-in-out;
        }
        
        /* Custom form styling */
        .form-input {
            transition: all 0.3s ease;
        }
        
        .form-input:focus {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        
        /* Budget card hover effects */
        .budget-card {
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .budget-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }
        
        .budget-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }
        
        .budget-card:hover::before {
            left: 100%;
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
                        <i class="fas fa-chart-pie text-white text-2xl mr-3"></i>
                    </div>
                    <a href="dashboard.php" class="text-2xl font-bold text-white hover:text-yellow-300 transition duration-300">
                        Expence
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
                <i class="fas fa-piggy-bank text-blue-500 mr-3"></i>
                Budget Management
            </h1>
            <p class="text-gray-600 text-lg">Set budgets, track spending, and achieve your financial goals</p>
        </div>

        <?php if (isset($_GET['success'])): ?>
            <div class="max-w-2xl mx-auto mb-8">
                <div class="bg-green-50 border-l-4 border-green-400 p-4 rounded-r-lg">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-check-circle text-green-400 text-xl"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-green-700 font-medium">Budget created successfully!</p>
                            <p class="text-green-600 text-sm mt-1">Your new budget has been set and is now active.</p>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Budget Form -->
            <div class="glass rounded-2xl p-8 shadow-2xl hover-scale">
                <div class="text-center mb-6">
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-r from-blue-500 to-purple-600 rounded-full mb-4">
                        <i class="fas fa-plus text-white text-2xl"></i>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-800">Create New Budget</h2>
                    <p class="text-gray-600 mt-2">Set spending limits for different categories</p>
                </div>

                <form method="POST" class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-tags text-blue-500 mr-2"></i>Category
                        </label>
                        <select name="category" required class="form-input w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-blue-500 focus:outline-none bg-white">
                            <option value="">🏷️ Select a category</option>
                            
                            <!-- Essential Categories -->
                            <optgroup label="🏠 Essential">
                                <option value="Rent">🏠 Rent</option>
                                <option value="Electricity">⚡ Electricity</option>
                                <option value="Food">🍽️ Food</option>
                                <option value="Groceries">🛒 Groceries</option>
                                <option value="Transport">🚗 Transport</option>
                            </optgroup>
                            
                            <!-- Bills & Services -->
                            <optgroup label="📄 Bills & Services">
                                <option value="Bills">📄 Bills</option>
                                <option value="Recharge">📱 Recharge</option>
                                <option value="Fuel">⛽ Fuel</option>
                                <option value="Insurance">🛡️ Insurance</option>
                                <option value="Internet">🌐 Internet</option>
                                <option value="Water">💧 Water</option>
                                <option value="Gas">🔥 Gas</option>
                            </optgroup>
                            
                            <!-- Lifestyle -->
                            <optgroup label="🛍️ Lifestyle">
                                <option value="Shopping">🛍️ Shopping</option>
                                <option value="Clothing">👕 Clothing</option>
                                <option value="Entertainment">🎬 Entertainment</option>
                                <option value="Travel">✈️ Travel</option>
                                <option value="Beauty">💄 Beauty</option>
                                <option value="Fitness">💪 Fitness</option>
                                <option value="Dining Out">🍽️ Dining Out</option>
                            </optgroup>
                            
                            <!-- Health & Education -->
                            <optgroup label="🏥 Health & Education">
                                <option value="Healthcare">🏥 Healthcare</option>
                                <option value="Medicine">💊 Medicine</option>
                                <option value="Education">🎓 Education</option>
                                <option value="Books">📚 Books</option>
                            </optgroup>
                            
                            <!-- Others -->
                            <optgroup label="🎁 Others">
                                <option value="Gifts">🎁 Gifts</option>
                                <option value="Charity">❤️ Charity</option>
                                <option value="Pet Care">🐕 Pet Care</option>
                                <option value="Home Maintenance">🔧 Home Maintenance</option>
                                <option value="Other">📝 Other</option>
                            </optgroup>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-dollar-sign text-green-500 mr-2"></i>Budget Amount
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-gray-500 sm:text-sm">₹</span>
                            </div>
                            <input type="number" name="amount" step="0.01" required 
                                placeholder="0.00"
                                class="form-input pl-8 w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-green-500 focus:outline-none bg-white">
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-clock text-purple-500 mr-2"></i>Budget Period
                        </label>
                        <select name="period" required class="form-input w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-purple-500 focus:outline-none bg-white">
                            <option value="">Select period</option>
                            <option value="daily">📅 Daily</option>
                            <option value="weekly">📆 Weekly</option>
                            <option value="monthly">🗓️ Monthly</option>
                            <option value="yearly">📊 Yearly</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-calendar-alt text-red-500 mr-2"></i>Start Date
                        </label>
                        <input type="date" name="start_date" required 
                            value="<?php echo date('Y-m-d'); ?>"
                            class="form-input w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-red-500 focus:outline-none bg-white">
                    </div>
                    
                    <button type="submit" class="w-full budget-gradient text-white font-bold py-4 px-6 rounded-xl hover:shadow-lg transform hover:scale-105 transition duration-300">
                        <i class="fas fa-piggy-bank mr-2"></i>
                        Create Budget
                    </button>
                </form>
            </div>

            <!-- Current Budgets -->
            <div class="glass rounded-2xl p-8 shadow-2xl">
                <div class="text-center mb-6">
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-r from-green-500 to-blue-600 rounded-full mb-4">
                        <i class="fas fa-chart-bar text-white text-2xl"></i>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-800">Active Budgets</h2>
                    <p class="text-gray-600 mt-2">Monitor your spending progress</p>
                </div>

                <div class="space-y-6 max-h-96 overflow-y-auto custom-scrollbar">
                    <?php if (empty($budgets)): ?>
                        <div class="text-center py-12">
                            <i class="fas fa-piggy-bank text-gray-300 text-6xl mb-4"></i>
                            <h3 class="text-xl font-semibold text-gray-500 mb-2">No Budgets Set</h3>
                            <p class="text-gray-400 mb-4">Create your first budget to start tracking your spending</p>
                            <div class="inline-flex items-center text-blue-500 font-medium">
                                <i class="fas fa-arrow-left mr-2"></i>
                                Use the form to get started
                            </div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($budgets as $budget): 
                            $percentage = ($budget['spent_amount'] / $budget['amount']) * 100;
                            $remaining = $budget['amount'] - $budget['spent_amount'];
                            
                            if ($percentage > 90) {
                                $color = 'from-red-500 to-red-600';
                                $bgColor = 'bg-red-500';
                                $textColor = 'text-red-600';
                                $status = 'Over Budget';
                                $statusIcon = 'fas fa-exclamation-triangle';
                            } elseif ($percentage > 70) {
                                $color = 'from-yellow-500 to-orange-500';
                                $bgColor = 'bg-yellow-500';
                                $textColor = 'text-yellow-600';
                                $status = 'Warning';
                                $statusIcon = 'fas fa-exclamation-circle';
                            } else {
                                $color = 'from-green-500 to-green-600';
                                $bgColor = 'bg-green-500';
                                $textColor = 'text-green-600';
                                $status = 'On Track';
                                $statusIcon = 'fas fa-check-circle';
                            }
                        ?>
                        <div class="budget-card bg-white rounded-xl p-6 border-l-4 border-blue-500 shadow-lg">
                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <h3 class="text-lg font-bold text-gray-800"><?php echo htmlspecialchars($budget['category']); ?></h3>
                                    <div class="flex items-center mt-1">
                                        <span class="text-sm text-gray-500 mr-3">
                                            <i class="fas fa-calendar mr-1"></i>
                                            <?php echo ucfirst($budget['period']); ?>
                                        </span>
                                        <span class="<?php echo $textColor; ?> text-sm font-medium">
                                            <i class="<?php echo $statusIcon; ?> mr-1"></i>
                                            <?php echo $status; ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-2xl font-bold text-gray-800">₹<?php echo number_format($budget['spent_amount'], 0); ?></div>
                                    <div class="text-sm text-gray-500">of ₹<?php echo number_format($budget['amount'], 0); ?></div>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <div class="flex justify-between text-sm mb-2">
                                    <span class="font-medium">Progress</span>
                                    <span class="font-bold"><?php echo number_format($percentage, 1); ?>%</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden">
                                    <div class="progress-bar bg-gradient-to-r <?php echo $color; ?> h-3 rounded-full transition-all duration-1000" 
                                         style="width: <?php echo min($percentage, 100); ?>%"></div>
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-2 gap-4 text-center">
                                <div class="bg-gray-50 rounded-lg p-3">
                                    <div class="text-lg font-bold <?php echo $remaining >= 0 ? 'text-green-600' : 'text-red-600'; ?>">
                                        ₹<?php echo number_format(abs($remaining), 0); ?>
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        <?php echo $remaining >= 0 ? 'Remaining' : 'Over Budget'; ?>
                                    </div>
                                </div>
                                <div class="bg-gray-50 rounded-lg p-3">
                                    <div class="text-lg font-bold text-blue-600">
                                        <?php 
                                        $days_left = max(0, (strtotime($budget['end_date']) - time()) / (60 * 60 * 24));
                                        echo ceil($days_left);
                                        ?>
                                    </div>
                                    <div class="text-xs text-gray-500">Days Left</div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="gradient-bg text-white mt-12">
        <div class="max-w-7xl mx-auto px-4 py-8">
            <div class="text-center">
                <div class="flex justify-center items-center mb-4">
                    <i class="fas fa-piggy-bank text-2xl mr-3"></i>
                    <span class="text-xl font-bold">Daily Expense  Tracker Budget Manager</span>
                </div>
                <p class="text-gray-200 mb-4">Smart budgeting for better financial control</p>
                <div class="flex justify-center space-x-6 text-sm">
                    <span>&copy; 2024 Daily Expense  Tracker. All rights reserved.</span>
                    <span>•</span>
                    <span>Made with ❤️ for better financial management</span>
                </div>
            </div>
        </div>
    </footer>

    <script>
        // Enhanced interactions
        document.addEventListener('DOMContentLoaded', function() {
            // Animate budget cards on load
            const budgetCards = document.querySelectorAll('.budget-card');
            budgetCards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                card.style.transition = 'all 0.6s ease';
                
                setTimeout(() => {
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 200);
            });

            // Animate progress bars
            const progressBars = document.querySelectorAll('.progress-bar');
            setTimeout(() => {
                progressBars.forEach(bar => {
                    const width = bar.style.width;
                    bar.style.width = '0%';
                    setTimeout(() => {
                        bar.style.width = width;
                    }, 500);
                });
            }, 1000);

            // Form validation and feedback
            const form = document.querySelector('form');
            const inputs = form.querySelectorAll('input, select');
            
            inputs.forEach(input => {
                input.addEventListener('focus', function() {
                    this.parentElement.style.transform = 'scale(1.02)';
                });
                
                input.addEventListener('blur', function() {
                    this.parentElement.style.transform = 'scale(1)';
                });
            });

            // Add custom scrollbar styles
            const style = document.createElement('style');
            style.textContent = `
                .custom-scrollbar::-webkit-scrollbar {
                    width: 6px;
                }
                .custom-scrollbar::-webkit-scrollbar-track {
                    background: #f1f1f1;
                    border-radius: 10px;
                }
                .custom-scrollbar::-webkit-scrollbar-thumb {
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    border-radius: 10px;
                }
                .custom-scrollbar::-webkit-scrollbar-thumb:hover {
                    background: linear-gradient(135deg, #5a67d8 0%, #6b46c1 100%);
                }
            `;
            document.head.appendChild(style);
        });
    </script>
</body>
</html> 