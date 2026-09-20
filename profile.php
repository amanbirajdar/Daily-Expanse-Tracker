<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    $errors = [];
    
    // Validate current password
    $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    
    if (!password_verify($current_password, $user['password'])) {
        $errors[] = "Current password is incorrect";
    }
    
    // Validate email uniqueness
    if ($email !== $_SESSION['email']) {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$email, $_SESSION['user_id']]);
        if ($stmt->fetch()) {
            $errors[] = "Email already exists";
        }
    }
    
    // Validate new password if provided
    if (!empty($new_password)) {
        if ($new_password !== $confirm_password) {
            $errors[] = "New passwords do not match";
        }
        if (strlen($new_password) < 6) {
            $errors[] = "New password must be at least 6 characters long";
        }
    }
    
    if (empty($errors)) {
        if (!empty($new_password)) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, password = ? WHERE id = ?");
            $stmt->execute([$username, $email, $hashed_password, $_SESSION['user_id']]);
        } else {
            $stmt = $conn->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
            $stmt->execute([$username, $email, $_SESSION['user_id']]);
        }
        
        $_SESSION['username'] = $username;
        $_SESSION['email'] = $email;
        
        header("Location: profile.php?success=1");
        exit();
    }
}

// Get user data
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// Get user statistics
$stmt = $conn->prepare("
    SELECT 
        COUNT(*) as total_expenses,
        SUM(amount) as total_spent,
        COUNT(DISTINCT category) as unique_categories,
        COUNT(DISTINCT DATE(date)) as active_days
    FROM expenses 
    WHERE user_id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$stats = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Daily Expense  Tracker</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Custom gradient backgrounds */
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .profile-gradient {
            background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
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
        
        .card-gradient-4 {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
        
        /* Form input styling */
        .form-input {
            transition: all 0.3s ease;
        }
        
        .form-input:focus {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        
        /* Profile avatar */
        .profile-avatar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            width: 120px;
            height: 120px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            color: white;
            margin: 0 auto 1rem;
            box-shadow: 0 20px 40px rgba(102, 126, 234, 0.3);
        }
        
        /* Statistics card animations */
        .stat-card {
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }
        
        .stat-card:hover::before {
            left: 100%;
        }
        
        /* Password strength indicator */
        .password-strength {
            height: 4px;
            background: #e5e7eb;
            border-radius: 2px;
            overflow: hidden;
            margin-top: 8px;
        }
        
        .password-strength-fill {
            height: 100%;
            transition: width 0.3s ease;
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
                        <i class="fas fa-user-circle text-white text-2xl mr-3"></i>
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
            <div class="profile-avatar animate-float">
                <?php echo strtoupper(substr($_SESSION['username'], 0, 2)); ?>
            </div>
            <h1 class="text-4xl font-bold text-gray-800 mb-2">
                Profile Settings
            </h1>
            <p class="text-gray-600 text-lg">Manage your account and view your expense statistics</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Profile Form -->
            <div class="glass rounded-2xl p-8 shadow-2xl hover-scale">
                <div class="text-center mb-6">
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-r from-purple-500 to-pink-600 rounded-full mb-4">
                        <i class="fas fa-edit text-white text-2xl"></i>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-800">Update Profile</h2>
                    <p class="text-gray-600 mt-2">Keep your information up to date</p>
                </div>
                
                <?php if (isset($_GET['success'])): ?>
                <div class="bg-green-50 border-l-4 border-green-400 p-4 mb-6 rounded-r-lg">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-check-circle text-green-400 text-xl"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-green-700 font-medium">Profile updated successfully!</p>
                            <p class="text-green-600 text-sm mt-1">Your changes have been saved.</p>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($errors)): ?>
                <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-6 rounded-r-lg">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-circle text-red-400 text-xl"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-red-700 font-medium">Please fix the following errors:</p>
                            <ul class="list-disc list-inside text-red-600 text-sm mt-2">
                                <?php foreach ($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <form method="POST" class="space-y-6" id="profileForm">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-user text-blue-500 mr-2"></i>Username
                        </label>
                        <input type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required 
                            class="form-input w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-blue-500 focus:outline-none bg-white">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-envelope text-green-500 mr-2"></i>Email Address
                        </label>
                        <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required 
                            class="form-input w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-green-500 focus:outline-none bg-white">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-lock text-red-500 mr-2"></i>Current Password
                        </label>
                        <input type="password" name="current_password" required 
                            class="form-input w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-red-500 focus:outline-none bg-white">
                        <p class="text-xs text-gray-500 mt-1">Required to confirm changes</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-key text-purple-500 mr-2"></i>New Password (Optional)
                        </label>
                        <input type="password" name="new_password" id="newPassword"
                            class="form-input w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-purple-500 focus:outline-none bg-white">
                        <div class="password-strength">
                            <div class="password-strength-fill" id="passwordStrengthFill"></div>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Leave blank to keep current password</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-check-circle text-orange-500 mr-2"></i>Confirm New Password
                        </label>
                        <input type="password" name="confirm_password" id="confirmPassword"
                            class="form-input w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-orange-500 focus:outline-none bg-white">
                        <div class="mt-2 text-xs" id="passwordMatch"></div>
                    </div>
                    
                    <button type="submit" class="w-full profile-gradient text-white font-bold py-4 px-6 rounded-xl hover:shadow-lg transform hover:scale-105 transition duration-300">
                        <i class="fas fa-save mr-2"></i>
                        Update Profile
                    </button>
                </form>
            </div>

            <!-- User Statistics -->
            <div class="space-y-6">
                <!-- Account Info -->
                <div class="glass rounded-2xl p-8 shadow-2xl">
                    <div class="text-center mb-6">
                        <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-r from-blue-500 to-cyan-600 rounded-full mb-4">
                            <i class="fas fa-info-circle text-white text-2xl"></i>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-800">Account Info</h2>
                        <p class="text-gray-600 mt-2">Your account details</p>
                    </div>

                    <div class="space-y-4">
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl">
                            <div class="flex items-center">
                                <i class="fas fa-calendar-plus text-blue-500 mr-3"></i>
                                <span class="font-medium text-gray-700">Member Since</span>
                            </div>
                            <span class="text-gray-600"><?php echo date('M d, Y', strtotime($user['created_at'])); ?></span>
                        </div>
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl">
                            <div class="flex items-center">
                                <i class="fas fa-clock text-green-500 mr-3"></i>
                                <span class="font-medium text-gray-700">Last Updated</span>
                            </div>
                            <span class="text-gray-600"><?php echo date('M d, Y', strtotime($user['updated_at'])); ?></span>
                        </div>
                    </div>
                </div>

                <!-- Statistics -->
                <div class="glass rounded-2xl p-8 shadow-2xl">
                    <div class="text-center mb-6">
                        <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-r from-green-500 to-blue-600 rounded-full mb-4">
                            <i class="fas fa-chart-bar text-white text-2xl"></i>
                        </div>
                        <h2 class="text-2xl font-bold text-gray-800">Your Statistics</h2>
                        <p class="text-gray-600 mt-2">Track your expense journey</p>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="stat-card bg-white rounded-xl p-4 border-l-4 border-blue-500">
                            <div class="flex items-center">
                                <div class="card-gradient-1 p-3 rounded-full">
                                    <i class="fas fa-receipt text-white"></i>
                                </div>
                                <div class="ml-3">
                                    <p class="text-xs text-gray-600 uppercase tracking-wide">Total Expenses</p>
                                    <p class="text-xl font-bold text-gray-800"><?php echo number_format($stats['total_expenses']); ?></p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="stat-card bg-white rounded-xl p-4 border-l-4 border-green-500">
                            <div class="flex items-center">
                                <div class="card-gradient-2 p-3 rounded-full">
                                    <i class="fas fa-wallet text-white"></i>
                                </div>
                                <div class="ml-3">
                                    <p class="text-xs text-gray-600 uppercase tracking-wide">Total Spent</p>
                                    <p class="text-xl font-bold text-gray-800">₹<?php echo number_format($stats['total_spent'], 0); ?></p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="stat-card bg-white rounded-xl p-4 border-l-4 border-purple-500">
                            <div class="flex items-center">
                                <div class="card-gradient-3 p-3 rounded-full">
                                    <i class="fas fa-tags text-white"></i>
                                </div>
                                <div class="ml-3">
                                    <p class="text-xs text-gray-600 uppercase tracking-wide">Categories</p>
                                    <p class="text-xl font-bold text-gray-800"><?php echo $stats['unique_categories']; ?></p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="stat-card bg-white rounded-xl p-4 border-l-4 border-yellow-500">
                            <div class="flex items-center">
                                <div class="card-gradient-4 p-3 rounded-full">
                                    <i class="fas fa-calendar text-white"></i>
                                </div>
                                <div class="ml-3">
                                    <p class="text-xs text-gray-600 uppercase tracking-wide">Active Days</p>
                                    <p class="text-xl font-bold text-gray-800"><?php echo $stats['active_days']; ?></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Achievement Badges -->
                    <div class="mt-6 pt-6 border-t border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4 text-center">
                            <i class="fas fa-trophy text-yellow-500 mr-2"></i>
                            Achievements
                        </h3>
                        <div class="flex justify-center space-x-4">
                            <?php if ($stats['total_expenses'] >= 10): ?>
                                <div class="text-center">
                                    <div class="w-12 h-12 bg-gradient-to-r from-yellow-400 to-orange-500 rounded-full flex items-center justify-center mb-2">
                                        <i class="fas fa-star text-white"></i>
                                    </div>
                                    <p class="text-xs text-gray-600">Tracker</p>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($stats['total_expenses'] >= 50): ?>
                                <div class="text-center">
                                    <div class="w-12 h-12 bg-gradient-to-r from-purple-400 to-pink-500 rounded-full flex items-center justify-center mb-2">
                                        <i class="fas fa-medal text-white"></i>
                                    </div>
                                    <p class="text-xs text-gray-600">Expert</p>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($stats['unique_categories'] >= 5): ?>
                                <div class="text-center">
                                    <div class="w-12 h-12 bg-gradient-to-r from-green-400 to-blue-500 rounded-full flex items-center justify-center mb-2">
                                        <i class="fas fa-crown text-white"></i>
                                    </div>
                                    <p class="text-xs text-gray-600">Organizer</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="gradient-bg text-white mt-12">
        <div class="max-w-7xl mx-auto px-4 py-8">
            <div class="text-center">
                <div class="flex justify-center items-center mb-4">
                    <i class="fas fa-user-circle text-2xl mr-3"></i>
                    <span class="text-xl font-bold">Daily Expense  Tracker Profile</span>
                </div>
                <p class="text-gray-200 mb-4">Manage your account and track your progress</p>
                <div class="flex justify-center space-x-6 text-sm">
                    <span>&copy; 2026 Daily Expense  Tracker. All rights reserved.</span>
                    <span>•</span>
                    <span>Made with ❤️ for better financial management</span>
                </div>
            </div>
        </div>
    </footer>

    <script>
        // Enhanced form interactions
        document.addEventListener('DOMContentLoaded', function() {
            const newPasswordInput = document.getElementById('newPassword');
            const confirmPasswordInput = document.getElementById('confirmPassword');
            const passwordStrengthFill = document.getElementById('passwordStrengthFill');
            const passwordMatch = document.getElementById('passwordMatch');

            // Animate stat cards on load
            const statCards = document.querySelectorAll('.stat-card');
            statCards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                card.style.transition = 'all 0.6s ease';
                
                setTimeout(() => {
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 200);
            });

            // Password strength checker
            function checkPasswordStrength(password) {
                let strength = 0;
                let color = '#e5e7eb';

                if (password.length >= 6) strength++;
                if (password.match(/[a-z]/) && password.match(/[A-Z]/)) strength++;
                if (password.match(/\d/)) strength++;
                if (password.match(/[^a-zA-Z\d]/)) strength++;

                const colors = ['#ef4444', '#f59e0b', '#3b82f6', '#10b981'];
                const widths = ['25%', '50%', '75%', '100%'];

                if (password.length > 0) {
                    color = colors[strength - 1] || colors[0];
                    passwordStrengthFill.style.width = widths[strength - 1] || '25%';
                    passwordStrengthFill.style.background = color;
                } else {
                    passwordStrengthFill.style.width = '0%';
                }
            }

            // Password match checker
            function checkPasswordMatch() {
                const password = newPasswordInput.value;
                const confirmPassword = confirmPasswordInput.value;

                if (confirmPassword === '') {
                    passwordMatch.textContent = '';
                    passwordMatch.className = 'mt-2 text-xs';
                } else if (password === confirmPassword) {
                    passwordMatch.textContent = '✓ Passwords match';
                    passwordMatch.className = 'mt-2 text-xs text-green-600';
                } else {
                    passwordMatch.textContent = '✗ Passwords do not match';
                    passwordMatch.className = 'mt-2 text-xs text-red-600';
                }
            }

            // Event listeners
            newPasswordInput.addEventListener('input', function() {
                checkPasswordStrength(this.value);
                checkPasswordMatch();
            });

            confirmPasswordInput.addEventListener('input', checkPasswordMatch);

            // Form input focus effects
            const formInputs = document.querySelectorAll('.form-input');
            formInputs.forEach(input => {
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