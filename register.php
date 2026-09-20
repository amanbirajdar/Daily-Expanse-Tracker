<?php
require_once 'config/database.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validation
    if (empty($username) || empty($email) || empty($password)) {
        $error = "All fields are required";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long";
    } else {
        // Check if email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->rowCount() > 0) {
            $error = "Email already exists";
        } else {
            // Hash password and insert user
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            if ($stmt->execute([$username, $email, $hashed_password])) {
                $success = "Registration successful! Please login.";
            } else {
                $error = "Registration failed. Please try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Daily Expense  Tracker</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Custom gradient backgrounds */
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .register-bg {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            min-height: 100vh;
            position: relative;
        }
        
        .register-bg::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 1000"><polygon fill="%23ffffff08" points="0,0 1000,1000 0,1000"/></svg>');
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
            color: #f093fb;
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
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            transition: all 0.3s ease;
        }
        
        .btn-gradient:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(240, 147, 251, 0.4);
        }
        
        /* Progress bar */
        .progress-bar {
            height: 4px;
            background: #e5e7eb;
            border-radius: 2px;
            overflow: hidden;
        }
        
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #f093fb, #f5576c);
            transition: width 0.3s ease;
        }
    </style>
</head>
<body class="register-bg">
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
                    <a href="login.php" class="text-white hover:text-yellow-300 transition duration-300 font-medium">
                        <i class="fas fa-sign-in-alt mr-2"></i>Login
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Registration Form -->
    <div class="relative z-10 min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full">
            <!-- Welcome Section -->
            <div class="text-center mb-8">
                <div class="float">
                    <div class="inline-flex items-center justify-center w-20 h-20 bg-white rounded-full shadow-lg mb-4">
                        <i class="fas fa-user-plus text-4xl text-pink-600"></i>
                    </div>
                </div>
                <h1 class="text-4xl font-bold text-white mb-2">Join Daily Expense  Tracker!</h1>
                <p class="text-white opacity-90">Create your account and start managing expenses smartly</p>
            </div>

            <!-- Registration Card -->
            <div class="glass rounded-2xl p-8 shadow-2xl">
                <div class="text-center mb-6">
                    <h2 class="text-2xl font-bold text-gray-800">Create Account</h2>
                    <p class="text-gray-600 mt-2">
                        Already have an account? 
                        <a href="login.php" class="font-semibold text-pink-600 hover:text-pink-800 transition duration-300">
                            Sign in here
                        </a>
                    </p>
                </div>

                <!-- Progress Bar -->
                <div class="mb-6">
                    <div class="progress-bar">
                        <div class="progress-fill" id="progressFill" style="width: 0%"></div>
                    </div>
                    <p class="text-xs text-gray-500 mt-2 text-center">Complete all fields to register</p>
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

                <?php if ($success): ?>
                    <div class="bg-green-50 border-l-4 border-green-400 p-4 mb-6 rounded-r-lg">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-check-circle text-green-400"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-green-700 font-medium"><?php echo $success; ?></p>
                                <p class="text-green-600 text-sm mt-1">
                                    <a href="login.php" class="underline">Click here to login</a>
                                </p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" class="space-y-6" id="registerForm">
                    <div class="input-group">
                        <input id="username" name="username" type="text" required 
                            placeholder=" "
                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-pink-500 focus:outline-none transition duration-300 bg-white">
                        <label for="username" class="text-gray-500">
                            <i class="fas fa-user mr-2"></i>Username
                        </label>
                    </div>

                    <div class="input-group">
                        <input id="email" name="email" type="email" required 
                            placeholder=" "
                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-pink-500 focus:outline-none transition duration-300 bg-white">
                        <label for="email" class="text-gray-500">
                            <i class="fas fa-envelope mr-2"></i>Email Address
                        </label>
                    </div>

                    <div class="input-group">
                        <input id="password" name="password" type="password" required 
                            placeholder=" "
                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-pink-500 focus:outline-none transition duration-300 bg-white">
                        <label for="password" class="text-gray-500">
                            <i class="fas fa-lock mr-2"></i>Password
                        </label>
                        <div class="mt-2">
                            <div class="text-xs text-gray-500">Password strength:</div>
                            <div class="flex space-x-1 mt-1">
                                <div class="h-1 w-1/4 bg-gray-200 rounded" id="strength1"></div>
                                <div class="h-1 w-1/4 bg-gray-200 rounded" id="strength2"></div>
                                <div class="h-1 w-1/4 bg-gray-200 rounded" id="strength3"></div>
                                <div class="h-1 w-1/4 bg-gray-200 rounded" id="strength4"></div>
                            </div>
                        </div>
                    </div>

                    <div class="input-group">
                        <input id="confirm_password" name="confirm_password" type="password" required 
                            placeholder=" "
                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-pink-500 focus:outline-none transition duration-300 bg-white">
                        <label for="confirm_password" class="text-gray-500">
                            <i class="fas fa-lock mr-2"></i>Confirm Password
                        </label>
                        <div class="mt-2 text-xs" id="passwordMatch"></div>
                    </div>

                    <div class="flex items-center">
                        <input id="terms" name="terms" type="checkbox" required
                            class="h-4 w-4 text-pink-600 focus:ring-pink-500 border-gray-300 rounded">
                        <label for="terms" class="ml-2 text-sm text-gray-700">
                            I agree to the 
                            <a href="#" class="text-pink-600 hover:text-pink-800 font-medium">Terms of Service</a> 
                            and 
                            <a href="#" class="text-pink-600 hover:text-pink-800 font-medium">Privacy Policy</a>
                        </label>
                    </div>

                    <button type="submit" 
                        class="w-full btn-gradient text-white font-bold py-3 px-4 rounded-xl focus:outline-none focus:ring-4 focus:ring-pink-300">
                        <i class="fas fa-user-plus mr-2"></i>
                        Create Account
                    </button>
                </form>

                <!-- Benefits Section -->
                <div class="mt-6 p-4 bg-gray-50 rounded-xl">
                    <h3 class="text-sm font-semibold text-gray-800 mb-3">What you'll get:</h3>
                    <div class="space-y-2 text-xs text-gray-600">
                        <div class="flex items-center">
                            <i class="fas fa-check text-green-500 mr-2"></i>
                            <span>Unlimited expense tracking</span>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-check text-green-500 mr-2"></i>
                            <span>Smart budget management</span>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-check text-green-500 mr-2"></i>
                            <span>Detailed financial reports</span>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-check text-green-500 mr-2"></i>
                            <span>Secure data encryption</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="relative z-10 bg-black bg-opacity-20 text-white">
        <div class="max-w-7xl mx-auto px-4 py-6">
            <div class="text-center">
                <p class="text-white opacity-75">&copy; 2026 Daily Expense  Tracker. All rights reserved. Made with ❤️ for better financial management.</p>
            </div>
        </div>
    </footer>

    <script>
        // Enhanced form interactions
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('registerForm');
            const inputs = form.querySelectorAll('input[required]');
            const progressFill = document.getElementById('progressFill');
            const passwordInput = document.getElementById('password');
            const confirmPasswordInput = document.getElementById('confirm_password');
            const passwordMatch = document.getElementById('passwordMatch');

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

            // Update progress bar
            function updateProgress() {
                let filledInputs = 0;
                inputs.forEach(input => {
                    if (input.value.trim() !== '') {
                        filledInputs++;
                    }
                });
                const progress = (filledInputs / inputs.length) * 100;
                progressFill.style.width = progress + '%';
            }

            // Password strength checker
            function checkPasswordStrength(password) {
                let strength = 0;
                const strengthBars = [
                    document.getElementById('strength1'),
                    document.getElementById('strength2'),
                    document.getElementById('strength3'),
                    document.getElementById('strength4')
                ];

                // Reset bars
                strengthBars.forEach(bar => {
                    bar.className = 'h-1 w-1/4 bg-gray-200 rounded';
                });

                if (password.length >= 6) strength++;
                if (password.match(/[a-z]/) && password.match(/[A-Z]/)) strength++;
                if (password.match(/\d/)) strength++;
                if (password.match(/[^a-zA-Z\d]/)) strength++;

                const colors = ['bg-red-400', 'bg-yellow-400', 'bg-blue-400', 'bg-green-400'];
                for (let i = 0; i < strength; i++) {
                    strengthBars[i].className = `h-1 w-1/4 ${colors[strength - 1]} rounded`;
                }
            }

            // Password match checker
            function checkPasswordMatch() {
                const password = passwordInput.value;
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
            inputs.forEach(input => {
                input.addEventListener('input', updateProgress);
            });

            passwordInput.addEventListener('input', function() {
                checkPasswordStrength(this.value);
                checkPasswordMatch();
            });

            confirmPasswordInput.addEventListener('input', checkPasswordMatch);

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