<?php
session_start();
require_once 'config/database.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error = "Please enter both email and password";
    } else {
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Invalid email or password";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Daily Expense  Tracker</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Custom gradient backgrounds */
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .login-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            position: relative;
        }
        
        .login-bg::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 1000"><polygon fill="%23ffffff08" points="0,1000 1000,0 1000,1000"/></svg>');
            background-size: cover;
        }
        
        /* Glass morphism effect */
        .glass {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 25px 45px rgba(0, 0, 0, 0.1);
        }
        
        /* Floating animation */
        .float {
            animation: float 6s ease-in-out infinite;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }
        
        /* Input focus effects */
        .input-group {
            position: relative;
        }
        
        .input-group input:focus + label,
        .input-group input:not(:placeholder-shown) + label {
            transform: translateY(-25px) scale(0.8);
            color: #667eea;
        }
        
        .input-group label {
            position: absolute;
            left: 12px;
            top: 12px;
            transition: all 0.3s ease;
            pointer-events: none;
            color: #9ca3af;
        }
        
        /* Button hover effects */
        .btn-gradient {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            transition: all 0.3s ease;
        }
        
        .btn-gradient:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
        }
    </style>
</head>
<body class="login-bg">
    <!-- Navigation -->
    <nav class="relative z-10 bg-transparent">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center">
                    <div class="float">
                        <i class="fas fa-wallet text-white text-2xl mr-3"></i>
                    </div>
                    <a href="index.php" class="text-2xl font-bold text-white hover:text-yellow-300 transition duration-300">
                        Daily Expense  Tracker
                    </a>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="register.php" class="text-white hover:text-yellow-300 transition duration-300 font-medium">
                        <i class="fas fa-user-plus mr-2"></i>Register
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Login Form -->
    <div class="relative z-10 min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full">
            <!-- Welcome Section -->
            <div class="text-center mb-8">
                <div class="float">
                    <div class="inline-flex items-center justify-center w-20 h-20 bg-white rounded-full shadow-lg mb-4">
                        <i class="fas fa-wallet text-4xl text-purple-600"></i>
                    </div>
                </div>
                <h1 class="text-4xl font-bold text-white mb-2">Welcome Back!</h1>
                <p class="text-white opacity-90">Sign in to continue managing your expenses</p>
            </div>

            <!-- Login Card -->
            <div class="glass rounded-2xl p-8 shadow-2xl">
                <div class="text-center mb-6">
                    <h2 class="text-2xl font-bold text-gray-800">Sign In</h2>
                    <p class="text-gray-600 mt-2">
                        Don't have an account? 
                        <a href="register.php" class="font-semibold text-purple-600 hover:text-purple-800 transition duration-300">
                            Create one here
                        </a>
                    </p>
                </div>

                <?php if ($error): ?>
                    <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-6 rounded-r-lg">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-exclamation-circle text-red-400"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-red-700 font-medium"><?php echo $error; ?></p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" class="space-y-6">
                    <div class="input-group">
                        <input id="email" name="email" type="email" required 
                            placeholder=" "
                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-purple-500 focus:outline-none transition duration-300 bg-white">
                        <label for="email" class="text-gray-500">
                            <i class="fas fa-envelope mr-2"></i>Email Address
                        </label>
                    </div>

                    <div class="input-group">
                        <input id="password" name="password" type="password" required 
                            placeholder=" "
                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-purple-500 focus:outline-none transition duration-300 bg-white">
                        <label for="password" class="text-gray-500">
                            <i class="fas fa-lock mr-2"></i>Password
                        </label>
                    </div>

                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <input id="remember_me" name="remember_me" type="checkbox" 
                                class="h-4 w-4 text-purple-600 focus:ring-purple-500 border-gray-300 rounded">
                            <label for="remember_me" class="ml-2 text-sm text-gray-700 font-medium">
                                Remember me
                            </label>
                        </div>

                        <div class="text-sm">
                            <a href="#" class="font-medium text-purple-600 hover:text-purple-800 transition duration-300">
                                Forgot password?
                            </a>
                        </div>
                    </div>

                    <button type="submit" 
                        class="w-full btn-gradient text-white font-bold py-3 px-4 rounded-xl focus:outline-none focus:ring-4 focus:ring-purple-300">
                        <i class="fas fa-sign-in-alt mr-2"></i>
                        Sign In
                    </button>
                </form>

           
            </div>

            <!-- Features Preview -->
            <div class="mt-8 text-center">
                <p class="text-white opacity-75 mb-4">Why choose Daily Expense  Tracker?</p>
                <div class="grid grid-cols-3 gap-4 text-white">
                    <div class="text-center">
                        <i class="fas fa-chart-pie text-2xl mb-2 opacity-75"></i>
                        <p class="text-xs opacity-75">Smart Analytics</p>
                    </div>
                    <div class="text-center">
                        <i class="fas fa-shield-alt text-2xl mb-2 opacity-75"></i>
                        <p class="text-xs opacity-75">Secure & Private</p>
                    </div>
                    <div class="text-center">
                        <i class="fas fa-mobile-alt text-2xl mb-2 opacity-75"></i>
                        <p class="text-xs opacity-75">Mobile Friendly</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->   *
    <footer class="relative z-10 bg-black bg-opacity-20 text-white">
        <div class="max-w-7xl mx-auto px-4 py-6">
            <div class="text-center">
                <p class="text-white opacity-75">&copy; 2026 Daily Expense  Tracker. All rights reserved. Made with ❤️ for better financial management.</p>
            </div>
        </div>
    </footer>

    <script>
        // Add some interactive effects
        document.addEventListener('DOMContentLoaded', function() {
            // Animate form elements on load
            const formElements = document.querySelectorAll('.input-group, button');
            formElements.forEach((element, index) => {
                element.style.opacity = '0';
                element.style.transform = 'translateY(20px)';
                element.style.transition = 'all 0.6s ease';
                
                setTimeout(() => {
                    element.style.opacity = '1';
                    element.style.transform = 'translateY(0)';
                }, index * 100);
            });

            // Add floating particles effect
            createParticles();
        });

        function createParticles() {
            const particleCount = 50;
            const body = document.body;

            for (let i = 0; i < particleCount; i++) {
                const particle = document.createElement('div');
                particle.style.position = 'fixed';
                particle.style.width = '4px';
                particle.style.height = '4px';
                particle.style.backgroundColor = 'rgba(255, 255, 255, 0.1)';
                particle.style.borderRadius = '50%';
                particle.style.pointerEvents = 'none';
                particle.style.zIndex = '1';
                
                // Random position
                particle.style.left = Math.random() * 100 + '%';
                particle.style.top = Math.random() * 100 + '%';
                
                // Random animation
                particle.style.animation = `float ${3 + Math.random() * 4}s ease-in-out infinite`;
                particle.style.animationDelay = Math.random() * 2 + 's';
                
                body.appendChild(particle);
            }
        }
    </script>
</body>
</html> 