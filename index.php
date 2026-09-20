<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expance - Smart Daily Expense Tracker</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Custom gradient backgrounds */
        .hero-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            position: relative;
            overflow: hidden;
        }
        
        .hero-bg::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 1000"><polygon fill="%23ffffff08" points="0,1000 1000,0 1000,1000"/></svg>');
            background-size: cover;
        }
        
        .features-bg {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }
        
        .stats-bg {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }
        
        /* Glass morphism effect */
        .glass {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 25px 45px rgba(0, 0, 0, 0.1);
        }
        
        .glass-dark {
            background: rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        /* Floating animations */
        .float {
            animation: float 6s ease-in-out infinite;
        }
        
        .float-delayed {
            animation: float 6s ease-in-out infinite;
            animation-delay: -2s;
        }
        
        .float-delayed-2 {
            animation: float 6s ease-in-out infinite;
            animation-delay: -4s;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }
        
        /* Pulse animation */
        .pulse-slow {
            animation: pulse 4s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
        
        /* Hover effects */
        .feature-card {
            transition: all 0.3s ease;
        }
        
        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }
        
        .btn-gradient {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            transition: all 0.3s ease;
        }
        
        .btn-gradient:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
        }
        
        .btn-secondary {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            transition: all 0.3s ease;
        }
        
        .btn-secondary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(240, 147, 251, 0.4);
        }
        
        /* Scroll animations */
        .fade-in {
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.8s ease;
        }
        
        .fade-in.visible {
            opacity: 1;
            transform: translateY(0);
        }
        
        /* Navigation styling */
        .nav-glass {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }
        
        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        
        ::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 4px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
        }
        
        /* Testimonial cards */
        .testimonial-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        /* Stats counter animation */
        .counter {
            font-weight: bold;
            font-size: 2.5rem;
        }
        
        /* Mobile responsive adjustments */
        @media (max-width: 768px) {
            .hero-title {
                font-size: 2.5rem;
            }
            
            .hero-subtitle {
                font-size: 1.1rem;
            }
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="nav-glass fixed w-full z-50 transition-all duration-300">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center">
                    <div class="float">
                        <i class="fas fa-wallet text-purple-600 text-2xl mr-3"></i>
                    </div>
                    <a href="index.php" class="text-2xl font-bold bg-gradient-to-r from-purple-600 to-blue-600 bg-clip-text text-transparent">
                        Expance
                    </a>
                </div>
                <div class="hidden md:flex items-center space-x-6">
                    <a href="#features" class="text-gray-700 hover:text-purple-600 transition duration-300 font-medium">Features</a>
                    <a href="#how-it-works" class="text-gray-700 hover:text-purple-600 transition duration-300 font-medium">How It Works</a>
                    <!-- <a href="#testimonials" class="text-gray-700 hover:text-purple-600 transition duration-300 font-medium">Reviews</a> -->
                    <a href="login.php" class="text-gray-700 hover:text-purple-600 transition duration-300 font-medium">Login</a>
                    <a href="register.php" class="btn-gradient text-white px-6 py-2 rounded-full font-semibold">
                        Get Started Free
                    </a>
                </div>
                <!-- Mobile menu button -->
                <div class="md:hidden">
                    <button id="mobile-menu-btn" class="text-gray-700 hover:text-purple-600">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Mobile menu -->
        <div id="mobile-menu" class="md:hidden hidden bg-white border-t">
            <div class="px-4 py-4 space-y-3">
                <a href="#features" class="block text-gray-700 hover:text-purple-600 font-medium">Features</a>
                <a href="#how-it-works" class="block text-gray-700 hover:text-purple-600 font-medium">How It Works</a>
                <a href="#testimonials" class="block text-gray-700 hover:text-purple-600 font-medium">Reviews</a>
                <a href="login.php" class="block text-gray-700 hover:text-purple-600 font-medium">Login</a>
                <a href="register.php" class="btn-gradient text-white px-6 py-2 rounded-full font-semibold inline-block">
                    Get Started Free
                </a>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-bg text-white pt-20 pb-16 min-h-screen flex items-center">
        <div class="relative z-10 max-w-7xl mx-auto px-4">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                <!-- Left Column - Content -->
                <div class="text-center lg:text-left">
                    <div class="fade-in">
                        <h1 class="hero-title text-5xl lg:text-6xl font-bold mb-6 leading-tight">
                            Take Control of Your 
                            <span class="bg-gradient-to-r from-yellow-300 to-pink-300 bg-clip-text text-transparent">
                                Finances
                            </span>
                        </h1>
                        <p class="hero-subtitle text-xl lg:text-2xl mb-8 text-white opacity-90 leading-relaxed">
                            Track expenses, manage budgets, and achieve your financial goals with our smart, intuitive expense tracker.
                        </p>
                        <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                            <a href="register.php" class="btn-gradient text-white px-8 py-4 rounded-full font-semibold text-lg inline-flex items-center justify-center">
                                <i class="fas fa-rocket mr-2"></i>
                                Start Free Today
                            </a>
                            <a href="#features" class="glass-dark text-white px-8 py-4 rounded-full font-semibold text-lg inline-flex items-center justify-center">
                                <i class="fas fa-play mr-2"></i>
                                See How It Works
                            </a>
                        </div>
                        <div class="mt-8 flex items-center justify-center lg:justify-start space-x-6 text-sm">
                            <div class="flex items-center">
                                <i class="fas fa-check-circle text-green-300 mr-2"></i>
                                <span>Free to start</span>
                            </div>
                            <div class="flex items-center">
                                <i class="fas fa-check-circle text-green-300 mr-2"></i>
                                <span>No credit card required</span>
                            </div>
                            <div class="flex items-center">
                                <i class="fas fa-check-circle text-green-300 mr-2"></i>
                                <span>Setup in 2 minutes</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Right Column - Visual -->
                <div class="relative">
                    <div class="float">
                        <div class="glass rounded-3xl p-8 max-w-md mx-auto">
                            <div class="text-center mb-6">
                                <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-r from-purple-500 to-blue-500 rounded-full mb-4">
                                    <i class="fas fa-chart-line text-white text-2xl"></i>
                                </div>
                                <h3 class="text-xl font-bold text-gray-800">Monthly Overview</h3>
                            </div>
                            <div class="space-y-4">
                                <div class="flex justify-between items-center p-3 bg-green-50 rounded-lg">
                                    <div class="flex items-center">
                                        <div class="w-3 h-3 bg-green-500 rounded-full mr-3"></div>
                                        <span class="text-gray-700">Income</span>
                                    </div>
                                    <span class="font-bold text-green-600">₹45,000</span>
                                </div>
                                <div class="flex justify-between items-center p-3 bg-red-50 rounded-lg">
                                    <div class="flex items-center">
                                        <div class="w-3 h-3 bg-red-500 rounded-full mr-3"></div>
                                        <span class="text-gray-700">Expenses</span>
                                    </div>
                                    <span class="font-bold text-red-600">₹32,500</span>
                                </div>
                                <div class="flex justify-between items-center p-3 bg-blue-50 rounded-lg">
                                    <div class="flex items-center">
                                        <div class="w-3 h-3 bg-blue-500 rounded-full mr-3"></div>
                                        <span class="text-gray-700">Savings</span>
                                    </div>
                                    <span class="font-bold text-blue-600">₹12,500</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Floating elements -->
                    <div class="absolute -top-4 -right-4 float-delayed">
                        <div class="glass-dark rounded-full p-4">
                            <i class="fas fa-receipt text-white text-xl"></i>
                        </div>
                    </div>
                    <div class="absolute -bottom-4 -left-4 float-delayed-2">
                        <div class="glass-dark rounded-full p-4">
                            <i class="fas fa-piggy-bank text-white text-xl"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats-bg py-16">
        <div class="max-w-7xl mx-auto px-4">
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-8 text-center text-white">
                <div class="fade-in">
                    <div class="counter" data-target="10000">0</div>
                    <p class="text-lg opacity-90">Happy Users</p>
                </div>
                <div class="fade-in">
                    <div class="counter" data-target="50000">0</div>
                    <p class="text-lg opacity-90">Expenses Tracked</p>
                </div>
                <div class="fade-in">
                    <div class="counter" data-target="25000">0</div>
                    <p class="text-lg opacity-90">Money Saved</p>
                </div>
                <div class="fade-in">
                    <div class="counter" data-target="99">0</div>
                    <p class="text-lg opacity-90">Satisfaction Rate</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4">
            <div class="text-center mb-16 fade-in">
                <h2 class="text-4xl lg:text-5xl font-bold text-gray-800 mb-6">
                    Everything You Need to 
                    <span class="bg-gradient-to-r from-purple-600 to-blue-600 bg-clip-text text-transparent">
                        Manage Money
                    </span>
                </h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                    Powerful features designed to make expense tracking effortless and financial management a breeze.
                </p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Expense Tracking -->
                <div class="feature-card glass rounded-2xl p-8 text-center fade-in">
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-r from-blue-500 to-purple-500 rounded-full mb-6">
                        <i class="fas fa-wallet text-white text-2xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-800 mb-4">Smart Expense Tracking</h3>
                    <p class="text-gray-600 leading-relaxed">
                        Log expenses instantly with our intuitive interface. Categorize automatically and never miss a transaction.
                    </p>
                    <div class="mt-6 flex justify-center space-x-2">
                        <span class="px-3 py-1 bg-blue-100 text-blue-600 rounded-full text-sm">Quick Entry</span>
                        <span class="px-3 py-1 bg-purple-100 text-purple-600 rounded-full text-sm">Auto Categories</span>
                    </div>
                </div>

                <!-- Receipt Management -->
                <div class="feature-card glass rounded-2xl p-8 text-center fade-in">
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-r from-green-500 to-teal-500 rounded-full mb-6">
                        <i class="fas fa-receipt text-white text-2xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-800 mb-4">Receipt Management</h3>
                    <p class="text-gray-600 leading-relaxed">
                        Upload and organize receipts effortlessly. Support for all file formats with smart storage system.
                    </p>
                    <div class="mt-6 flex justify-center space-x-2">
                        <span class="px-3 py-1 bg-green-100 text-green-600 rounded-full text-sm">All Formats</span>
                        <span class="px-3 py-1 bg-teal-100 text-teal-600 rounded-full text-sm">Cloud Storage</span>
                    </div>
                </div>

                <!-- Budget Planner -->
                <div class="feature-card glass rounded-2xl p-8 text-center fade-in">
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-r from-orange-500 to-red-500 rounded-full mb-6">
                        <i class="fas fa-chart-pie text-white text-2xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-800 mb-4">Budget Planner</h3>
                    <p class="text-gray-600 leading-relaxed">
                        Set smart budgets and get real-time alerts. Stay on track with personalized spending insights.
                    </p>
                    <div class="mt-6 flex justify-center space-x-2">
                        <span class="px-3 py-1 bg-orange-100 text-orange-600 rounded-full text-sm">Smart Alerts</span>
                        <span class="px-3 py-1 bg-red-100 text-red-600 rounded-full text-sm">Goal Tracking</span>
                    </div>
                </div>

                <!-- Smart Reports -->
                <div class="feature-card glass rounded-2xl p-8 text-center fade-in">
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-r from-purple-500 to-pink-500 rounded-full mb-6">
                        <i class="fas fa-chart-line text-white text-2xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-800 mb-4">Smart Analytics</h3>
                    <p class="text-gray-600 leading-relaxed">
                        Get detailed insights into your spending patterns with beautiful charts and actionable reports.
                    </p>
                    <div class="mt-6 flex justify-center space-x-2">
                        <span class="px-3 py-1 bg-purple-100 text-purple-600 rounded-full text-sm">Visual Charts</span>
                        <span class="px-3 py-1 bg-pink-100 text-pink-600 rounded-full text-sm">Trends</span>
                    </div>
                </div>

                <!-- Mobile Friendly -->
                <div class="feature-card glass rounded-2xl p-8 text-center fade-in">
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-r from-indigo-500 to-blue-500 rounded-full mb-6">
                        <i class="fas fa-mobile-alt text-white text-2xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-800 mb-4">Mobile Optimized</h3>
                    <p class="text-gray-600 leading-relaxed">
                        Perfect experience on any device. Track expenses on-the-go with our responsive design.
                    </p>
                    <div class="mt-6 flex justify-center space-x-2">
                        <span class="px-3 py-1 bg-indigo-100 text-indigo-600 rounded-full text-sm">Responsive</span>
                        <span class="px-3 py-1 bg-blue-100 text-blue-600 rounded-full text-sm">Fast Loading</span>
                    </div>
                </div>

                <!-- Security -->
                <div class="feature-card glass rounded-2xl p-8 text-center fade-in">
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-r from-gray-600 to-gray-800 rounded-full mb-6">
                        <i class="fas fa-shield-alt text-white text-2xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-800 mb-4">Secure & Private</h3>
                    <p class="text-gray-600 leading-relaxed">
                        Your financial data is encrypted and secure. We prioritize your privacy above everything else.
                    </p>
                    <div class="mt-6 flex justify-center space-x-2">
                        <span class="px-3 py-1 bg-gray-100 text-gray-600 rounded-full text-sm">Encrypted</span>
                        <span class="px-3 py-1 bg-gray-100 text-gray-600 rounded-full text-sm">Private</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works Section -->
    <section id="how-it-works" class="features-bg py-20">
        <div class="max-w-7xl mx-auto px-4">
            <div class="text-center mb-16 fade-in">
                <h2 class="text-4xl lg:text-5xl font-bold text-white mb-6">
                    Get Started in 3 Simple Steps
                </h2>
                <p class="text-xl text-white opacity-90 max-w-3xl mx-auto">
                    Start tracking your expenses and managing your budget in just a few minutes.
                </p>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="text-center fade-in">
                    <div class="glass-dark rounded-full w-20 h-20 flex items-center justify-center mx-auto mb-6">
                        <span class="text-3xl font-bold text-white">1</span>
                    </div>
                    <h3 class="text-2xl font-bold text-white mb-4">Sign Up Free</h3>
                    <p class="text-white opacity-90 leading-relaxed">
                        Create your account in seconds. No credit card required, no hidden fees.
                    </p>
                </div>
                
                <div class="text-center fade-in">
                    <div class="glass-dark rounded-full w-20 h-20 flex items-center justify-center mx-auto mb-6">
                        <span class="text-3xl font-bold text-white">2</span>
                    </div>
                    <h3 class="text-2xl font-bold text-white mb-4">Add Your Expenses</h3>
                    <p class="text-white opacity-90 leading-relaxed">
                        Start logging your daily expenses with our intuitive interface and smart categories.
                    </p>
                </div>
                
                <div class="text-center fade-in">
                    <div class="glass-dark rounded-full w-20 h-20 flex items-center justify-center mx-auto mb-6">
                        <span class="text-3xl font-bold text-white">3</span>
                    </div>
                    <h3 class="text-2xl font-bold text-white mb-4">Track & Save</h3>
                    <p class="text-white opacity-90 leading-relaxed">
                        Monitor your spending patterns and achieve your financial goals with smart insights.
                    </p>
                </div>
            </div>
            
            <div class="text-center mt-12 fade-in">
                <a href="register.php" class="btn-secondary text-white px-8 py-4 rounded-full font-semibold text-lg inline-flex items-center">
                    <i class="fas fa-arrow-right mr-2"></i>
                    Start Your Journey Now
                </a>
            </div>
        </div>
    </section>



    <!-- CTA Section -->
    <section class="hero-bg py-20">
        <div class="relative z-10 max-w-7xl mx-auto px-4 text-center">
            <div class="fade-in">
                <h2 class="text-4xl lg:text-5xl font-bold text-white mb-6">
                    Ready to Take Control of Your Finances?
                </h2>
                <p class="text-xl text-white opacity-90 mb-8 max-w-3xl mx-auto">
                    Join thousands of users who have already transformed their financial habits. Start your journey today!
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="register.php" class="btn-gradient text-white px-8 py-4 rounded-full font-semibold text-lg inline-flex items-center justify-center">
                        <i class="fas fa-rocket mr-2"></i>
                        Get Started Free
                    </a>
                    <a href="login.php" class="glass-dark text-white px-8 py-4 rounded-full font-semibold text-lg inline-flex items-center justify-center">
                        <i class="fas fa-sign-in-alt mr-2"></i>
                        Already Have Account?
                    </a>
                </div>
                <p class="text-white opacity-75 mt-4 text-sm">
                    No credit card required • Free forever • Setup in 2 minutes
                </p>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white">
        <div class="max-w-7xl mx-auto px-4 py-12">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div class="md:col-span-2">
                    <div class="flex items-center mb-4">
                        <i class="fas fa-wallet text-purple-400 text-2xl mr-3"></i>
                        <h4 class="text-2xl font-bold bg-gradient-to-r from-purple-400 to-blue-400 bg-clip-text text-transparent">
                            Expance
                        </h4>
                    </div>
                    <p class="text-gray-400 mb-6 max-w-md">
                        Your smart companion for expense tracking and financial management. Take control of your money and achieve your financial goals.
                    </p>
                    <div class="flex space-x-4">
                        <a href="#" class="w-10 h-10 bg-gray-800 rounded-full flex items-center justify-center hover:bg-purple-600 transition duration-300">
                            <i class="fab fa-facebook text-white"></i>
                        </a>
                        <a href="#" class="w-10 h-10 bg-gray-800 rounded-full flex items-center justify-center hover:bg-purple-600 transition duration-300">
                            <i class="fab fa-twitter text-white"></i>
                        </a>
                        <a href="#" class="w-10 h-10 bg-gray-800 rounded-full flex items-center justify-center hover:bg-purple-600 transition duration-300">
                            <i class="fab fa-instagram text-white"></i>
                        </a>
                        <a href="#" class="w-10 h-10 bg-gray-800 rounded-full flex items-center justify-center hover:bg-purple-600 transition duration-300">
                            <i class="fab fa-linkedin text-white"></i>
                        </a>
                    </div>
                </div>
                <div>
                    <h4 class="text-lg font-semibold mb-4 text-white">Quick Links</h4>
                    <ul class="space-y-3">
                        <li><a href="#features" class="text-gray-400 hover:text-purple-400 transition duration-300">Features</a></li>
                        <li><a href="#how-it-works" class="text-gray-400 hover:text-purple-400 transition duration-300">How It Works</a></li>
                        <li><a href="#testimonials" class="text-gray-400 hover:text-purple-400 transition duration-300">Testimonials</a></li>
                        <li><a href="register.php" class="text-gray-400 hover:text-purple-400 transition duration-300">Get Started</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-lg font-semibold mb-4 text-white">Support</h4>
                    <ul class="space-y-3">
                        <li><a href="#" class="text-gray-400 hover:text-purple-400 transition duration-300">Help Center</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-purple-400 transition duration-300">Contact Us</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-purple-400 transition duration-300">Privacy Policy</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-purple-400 transition duration-300">Terms of Service</a></li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-gray-800 mt-8 pt-8 text-center">
                <p class="text-gray-400">
                    &copy; 2026 Expance. All rights reserved. Made with ❤️ for better financial management.
                </p>
            </div>
        </div>
    </footer>

    <script>
        // Mobile menu toggle
        document.getElementById('mobile-menu-btn').addEventListener('click', function() {
            const mobileMenu = document.getElementById('mobile-menu');
            mobileMenu.classList.toggle('hidden');
        });

        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Scroll animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                }
            });
        }, observerOptions);

        document.querySelectorAll('.fade-in').forEach(el => {
            observer.observe(el);
        });

        // Counter animation
        function animateCounter(element) {
            const target = parseInt(element.getAttribute('data-target'));
            const increment = target / 100;
            let current = 0;
            
            const timer = setInterval(() => {
                current += increment;
                if (current >= target) {
                    current = target;
                    clearInterval(timer);
                }
                
                if (target === 99) {
                    element.textContent = Math.floor(current) + '%';
                } else if (target >= 1000) {
                    element.textContent = (Math.floor(current / 1000)) + 'K+';
                } else {
                    element.textContent = Math.floor(current);
                }
            }, 20);
        }

        // Trigger counter animation when stats section is visible
        const statsObserver = new IntersectionObserver(function(entries) {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const counters = entry.target.querySelectorAll('.counter');
                    counters.forEach(counter => {
                        animateCounter(counter);
                    });
                    statsObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.5 });

        const statsSection = document.querySelector('.stats-bg');
        if (statsSection) {
            statsObserver.observe(statsSection);
        }

        // Navigation background on scroll
        window.addEventListener('scroll', function() {
            const nav = document.querySelector('nav');
            if (window.scrollY > 50) {
                nav.classList.add('shadow-lg');
            } else {
                nav.classList.remove('shadow-lg');
            }
        });
    </script>
</body>
</html> 