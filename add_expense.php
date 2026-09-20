<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $description = trim($_POST['description']);
    $amount = floatval($_POST['amount']);
    $category = trim($_POST['category']);
    $date = $_POST['date'];
    $receipt = '';

    // Handle receipt upload
    if (isset($_FILES['receipt']) && $_FILES['receipt']['error'] == 0) {
        // Expanded list of allowed file types
        $allowed = [
            // Images
            'jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'tiff', 'tif', 'svg',
            // Documents
            'pdf', 'doc', 'docx', 'txt', 'rtf',
            // Spreadsheets
            'xls', 'xlsx', 'csv',
            // Other common formats
            'zip', 'rar'
        ];
        
        $filename = $_FILES['receipt']['name'];
        $filetype = pathinfo($filename, PATHINFO_EXTENSION);
        $filesize = $_FILES['receipt']['size'];
        $max_size = 50 * 1024 * 1024; // 50MB

        // Validate file type and size
        if (in_array(strtolower($filetype), $allowed) && $filesize <= $max_size) {
            $newname = uniqid() . '.' . $filetype;
            $upload_path = 'uploads/receipts/' . $newname;
            
            if (!file_exists('uploads/receipts')) {
                mkdir('uploads/receipts', 0777, true);
            }

            if (move_uploaded_file($_FILES['receipt']['tmp_name'], $upload_path)) {
                $receipt = $upload_path;
            } else {
                $error = "Failed to upload receipt. Please try again.";
            }
        } else {
            if ($filesize > $max_size) {
                $error = "File size too large. Maximum allowed size is 50MB.";
            } else {
                $error = "Invalid file type. Please upload images, PDF, Word, Excel, or text files.";
            }
        }
    }

    // Validate input
    if (empty($description) || empty($amount) || empty($category) || empty($date)) {
        $error = "All fields are required";
    } elseif ($amount <= 0) {
        $error = "Amount must be greater than 0";
    } else {
        // Insert expense
        $stmt = $conn->prepare("INSERT INTO expenses (user_id, description, amount, category, date, receipt) VALUES (?, ?, ?, ?, ?, ?)");
        if ($stmt->execute([$_SESSION['user_id'], $description, $amount, $category, $date, $receipt])) {
            $success = "Expense added successfully!";
        } else {
            $error = "Failed to add expense. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Expense - Expance</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Custom gradient backgrounds */
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .expense-bg {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            min-height: 100vh;
            position: relative;
        }
        
        .expense-bg::before {
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
            margin-bottom: 2rem;
        }
        
        .input-group input:focus + label,
        .input-group input:not(:placeholder-shown) + label,
        .input-group select:focus + label,
        .input-group select:not([value=""]) + label {
            transform: translateY(-25px) scale(0.8);
            color: #4facfe;
        }
        
        .input-group label {
            position: absolute;
            left: 12px;
            top: 12px;
            transition: all 0.3s ease;
            pointer-events: none;
            color: #9ca3af;
            background: white;
            padding: 0 4px;
            z-index: 1;
        }
        
        .input-group input,
        .input-group select {
            width: 100%;
            padding: 12px;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            font-size: 16px;
            transition: all 0.3s ease;
            background: white;
            position: relative;
            z-index: 0;
        }
        
        .input-group input:focus,
        .input-group select:focus {
            border-color: #4facfe;
            box-shadow: 0 0 0 3px rgba(79, 172, 254, 0.1);
            outline: none;
        }
        
        /* Special styling for file upload input group */
        .file-input-group {
            position: relative;
            margin-bottom: 2rem;
        }
        
        .file-input-group .file-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #374151;
            font-size: 14px;
        }
        
        /* Button hover effects */
        .btn-gradient {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            transition: all 0.3s ease;
        }
        
        .btn-gradient:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(79, 172, 254, 0.4);
        }
        
        /* Category dropdown styling */
        .category-select optgroup {
            font-weight: bold;
            color: #374151;
            background-color: #f9fafb;
            padding: 8px 12px;
            font-size: 14px;
        }
        
        .category-select option {
            padding: 10px 15px;
            font-weight: normal;
            color: #1f2937;
            background-color: white;
        }
        
        .category-select option:hover {
            background-color: #eff6ff;
            color: #1d4ed8;
        }
        
        /* File upload styling */
        .file-upload-container {
            width: 100%;
            margin-bottom: 1rem;
        }
        
        .file-upload {
            position: relative;
            display: block;
            width: 100%;
        }
        
        .file-upload input[type=file] {
            position: absolute;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
            z-index: 2;
        }
        
        .file-upload-label {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 30px 20px;
            border: 3px dashed #d1d5db;
            border-radius: 12px;
            background: #f9fafb;
            transition: all 0.3s ease;
            min-height: 140px;
            cursor: pointer;
            position: relative;
            z-index: 1;
        }
        
        .file-upload:hover .file-upload-label,
        .file-upload-label:hover {
            border-color: #4facfe;
            background: #eff6ff;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(79, 172, 254, 0.15);
        }
        
        .file-upload-label.dragover {
            border-color: #10b981;
            background: #f0fdf4;
            transform: scale(1.02);
            box-shadow: 0 8px 25px rgba(16, 185, 129, 0.15);
        }
        
        .file-upload-label.has-file {
            border-color: #10b981;
            background: #f0fdf4;
            border-style: solid;
        }
        
        .upload-icon {
            font-size: 3rem;
            color: #9ca3af;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
        }
        
        .file-upload:hover .upload-icon {
            color: #4facfe;
            transform: scale(1.1);
        }
        
        .upload-text {
            text-align: center;
        }
        
        .upload-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.5rem;
        }
        
        .upload-subtitle {
            font-size: 0.9rem;
            color: #6b7280;
            margin-bottom: 1rem;
        }
        
        .upload-formats {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 1rem;
            font-size: 0.75rem;
            color: #9ca3af;
        }
        
        .format-item {
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }
        
        /* File preview styles */
        .file-preview {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 16px;
            background: #f0fdf4;
            border: 2px solid #10b981;
            border-radius: 12px;
            margin-top: 12px;
        }
        
        .file-info {
            display: flex;
            align-items: center;
            flex: 1;
        }
        
        .file-icon {
            width: 48px;
            height: 48px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 16px;
            font-size: 20px;
            flex-shrink: 0;
        }
        
        .file-icon.pdf { background: #fee2e2; color: #dc2626; }
        .file-icon.image { background: #dbeafe; color: #2563eb; }
        .file-icon.doc { background: #fef3c7; color: #d97706; }
        .file-icon.excel { background: #d1fae5; color: #059669; }
        .file-icon.archive { background: #f3e8ff; color: #7c3aed; }
        .file-icon.other { background: #e5e7eb; color: #6b7280; }
        
        .file-details h4 {
            font-weight: 600;
            color: #374151;
            margin-bottom: 4px;
        }
        
        .file-details p {
            font-size: 0.875rem;
            color: #6b7280;
        }
        
        .remove-file {
            background: #fee2e2;
            color: #dc2626;
            border: none;
            border-radius: 8px;
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            flex-shrink: 0;
        }
        
        .remove-file:hover {
            background: #fecaca;
            transform: scale(1.1);
        }
        
        /* Upload progress bar */
        .upload-progress {
            width: 100%;
            height: 6px;
            background: #e5e7eb;
            border-radius: 3px;
            overflow: hidden;
            margin-top: 12px;
        }
        
        .upload-progress-bar {
            height: 100%;
            background: linear-gradient(90deg, #10b981, #059669);
            transition: width 0.3s ease;
            width: 0%;
        }
        
        /* File validation messages */
        .file-validation {
            margin-top: 12px;
            padding: 12px 16px;
            border-radius: 8px;
            font-size: 0.875rem;
            display: none;
        }
        
        .file-validation.error {
            background: #fee2e2;
            color: #dc2626;
            border: 1px solid #fecaca;
        }
        
        .file-validation.success {
            background: #f0fdf4;
            color: #059669;
            border: 1px solid #bbf7d0;
        }
        
        /* Receipt tips styling */
        .receipt-tips {
            margin-top: 16px;
            padding: 16px;
            background: #eff6ff;
            border-radius: 12px;
            border-left: 4px solid #3b82f6;
        }
        
        .receipt-tips h4 {
            font-size: 0.875rem;
            font-weight: 600;
            color: #1e40af;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        
        .receipt-tips ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .receipt-tips li {
            font-size: 0.75rem;
            color: #1e40af;
            margin-bottom: 4px;
            padding-left: 16px;
            position: relative;
        }
        
        .receipt-tips li::before {
            content: '✓';
            position: absolute;
            left: 0;
            color: #10b981;
            font-weight: bold;
        }
        
        /* Success animation */
        .success-animation {
            animation: successPulse 0.6s ease-in-out;
        }
        
        @keyframes successPulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
        
        /* Quick amount buttons */
        .quick-amount {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 8px 16px;
            margin: 4px;
            background: #f3f4f6;
            border: 1px solid #d1d5db;
            border-radius: 20px;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .quick-amount:hover {
            background: #4facfe;
            color: white;
            transform: translateY(-2px);
        }
    </style>
</head>
<body class="expense-bg">
    <!-- Navigation -->
    <nav class="relative z-10 bg-transparent">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center">
                    <div class="float">
                        <i class="fas fa-plus-circle text-white text-2xl mr-3"></i>
                    </div>
                    <a href="dashboard.php" class="text-2xl font-bold text-white hover:text-yellow-300 transition duration-300">
                        Daily Expense Tracker 
                    </a>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="dashboard.php" class="text-white hover:text-yellow-300 transition duration-300 font-medium">
                        <i class="fas fa-home mr-2"></i>Dashboard
                    </a>
                    <a href="logout.php" class="text-white hover:text-red-300 transition duration-300 font-medium">
                        <i class="fas fa-sign-out-alt mr-2"></i>Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="relative z-10 min-h-screen flex items-center justify-center py-12 px-4">
        <div class="max-w-4xl w-full">
            <!-- Welcome Section -->
            <div class="text-center mb-8">
                <div class="float">
                    <div class="inline-flex items-center justify-center w-20 h-20 bg-white rounded-full shadow-lg mb-4">
                        <i class="fas fa-plus-circle text-4xl text-blue-500"></i>
                    </div>
                </div>
                <h1 class="text-4xl font-bold text-white mb-2">Add New Expense</h1>
                <p class="text-white opacity-90">Track your spending with ease and precision</p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Main Form -->
                <div class="lg:col-span-2">
                    <div class="glass rounded-2xl p-8 shadow-2xl">
                        <?php if ($success): ?>
                            <div class="bg-green-50 border-l-4 border-green-400 p-4 mb-6 rounded-r-lg success-animation">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <i class="fas fa-check-circle text-green-400 text-xl"></i>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-green-700 font-medium"><?php echo $success; ?></p>
                                        <p class="text-green-600 text-sm mt-1">Your expense has been recorded successfully.</p>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if ($error): ?>
                            <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-6 rounded-r-lg">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <i class="fas fa-exclamation-circle text-red-400 text-xl"></i>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-red-700 font-medium"><?php echo $error; ?></p>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" enctype="multipart/form-data" id="expenseForm">
                            <!-- Description -->
                            <div class="input-group">
                                <input type="text" id="description" name="description" required placeholder=" ">
                                <label for="description">
                                    <i class="fas fa-edit mr-2"></i>What did you spend on?
                                </label>
                            </div>

                            <!-- Amount with Quick Buttons -->
                            <div class="input-group">
                                <input type="number" id="amount" name="amount" step="0.01" required placeholder=" ">
                                <label for="amount">
                                    <i class="fas fa-rupee-sign mr-2"></i>Amount (₹)
                                </label>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-600 mb-2">Quick amounts:</p>
                                    <div class="flex flex-wrap">
                                        <span class="quick-amount" onclick="setAmount(50)">₹50</span>
                                        <span class="quick-amount" onclick="setAmount(100)">₹100</span>
                                        <span class="quick-amount" onclick="setAmount(200)">₹200</span>
                                        <span class="quick-amount" onclick="setAmount(500)">₹500</span>
                                        <span class="quick-amount" onclick="setAmount(1000)">₹1000</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Category -->
                            <div class="input-group">
                                <select id="category" name="category" required class="category-select">
                                    <option value="">Select a category</option>
                                    
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
                                <label for="category">
                                    <i class="fas fa-tags mr-2"></i>Category
                                </label>
                            </div>

                            <!-- Date -->
                            <div class="input-group">
                                <input type="date" id="date" name="date" required value="<?php echo date('Y-m-d'); ?>">
                                <label for="date">
                                    <i class="fas fa-calendar mr-2"></i>Date
                                </label>
                            </div>

                            <!-- Receipt Upload -->
                            <div class="file-input-group">
                                <label class="file-label">
                                    <i class="fas fa-receipt mr-2"></i>Upload Receipt (Optional)
                                </label>
                                
                                <div class="file-upload-container">
                                    <div class="file-upload" id="fileUpload">
                                        <input type="file" id="receipt" name="receipt" accept="image/*,.pdf,.doc,.docx,.txt,.rtf,.xls,.xlsx,.csv,.zip,.rar">
                                        <label for="receipt" class="file-upload-label" id="fileUploadLabel">
                                            <div class="upload-icon">
                                                <i class="fas fa-cloud-upload-alt"></i>
                                            </div>
                                            <div class="upload-text">
                                                <div class="upload-title">Drop your receipt here</div>
                                                <div class="upload-subtitle">
                                                    or <span style="color: #4facfe; font-weight: 600;">click to browse</span>
                                                </div>
                                                <div class="upload-formats">
                                                    <div class="format-item">
                                                        <i class="fas fa-image"></i>
                                                        <span>Images</span>
                                                    </div>
                                                    <div class="format-item">
                                                        <i class="fas fa-file-pdf"></i>
                                                        <span>PDF</span>
                                                    </div>
                                                    <div class="format-item">
                                                        <i class="fas fa-file-word"></i>
                                                        <span>Word</span>
                                                    </div>
                                                    <div class="format-item">
                                                        <i class="fas fa-file-excel"></i>
                                                        <span>Excel</span>
                                                    </div>
                                                    <div class="format-item">
                                                        <i class="fas fa-file-alt"></i>
                                                        <span>Text</span>
                                                    </div>
                                                    <div class="format-item">
                                                        <i class="fas fa-file-archive"></i>
                                                        <span>Archive</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </label>
                                    </div>
                                    
                                    <!-- File Preview -->
                                    <div id="filePreview" style="display: none;"></div>
                                    
                                    <!-- Upload Progress -->
                                    <div id="uploadProgress" class="upload-progress" style="display: none;">
                                        <div class="upload-progress-bar" id="uploadProgressBar"></div>
                                    </div>
                                    
                                    <!-- File Validation Messages -->
                                    <div id="fileValidation" class="file-validation"></div>
                                    
                                    <!-- Receipt Tips -->
                                    <div class="receipt-tips">
                                        <h4>
                                            <i class="fas fa-info-circle"></i>
                                            Receipt Upload Tips
                                        </h4>
                                        <ul>
                                            <li>All image formats supported (JPG, PNG, GIF, BMP, WebP, TIFF, SVG)</li>
                                            <li>Documents: PDF, Word (DOC/DOCX), Text (TXT/RTF)</li>
                                            <li>Spreadsheets: Excel (XLS/XLSX), CSV</li>
                                            <li>Archives: ZIP, RAR files</li>
                                            <li>Maximum file size: 50MB</li>
                                            <li>Ensure receipt is clear and readable</li>
                                            <li>Include full receipt showing amount and date</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <div class="text-center pt-6">
                                <button type="submit" class="btn-gradient text-white font-bold py-4 px-8 rounded-xl text-lg">
                                    <i class="fas fa-plus-circle mr-2"></i>
                                    Add Expense
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Sidebar with Tips and Quick Actions -->
                <div class="space-y-6">
                    <!-- Quick Tips -->
                    <div class="glass rounded-2xl p-6 shadow-2xl">
                        <h3 class="text-lg font-bold text-gray-800 mb-4">
                            <i class="fas fa-lightbulb text-yellow-500 mr-2"></i>
                            Quick Tips
                        </h3>
                        <div class="space-y-3 text-sm text-gray-600">
                            <div class="flex items-start">
                                <i class="fas fa-check-circle text-green-500 mr-2 mt-1"></i>
                                <span>Be specific in descriptions for better tracking</span>
                            </div>
                            <div class="flex items-start">
                                <i class="fas fa-check-circle text-green-500 mr-2 mt-1"></i>
                                <span>Upload receipts for important purchases</span>
                            </div>
                            <div class="flex items-start">
                                <i class="fas fa-check-circle text-green-500 mr-2 mt-1"></i>
                                <span>Choose the right category for better reports</span>
                            </div>
                            <div class="flex items-start">
                                <i class="fas fa-check-circle text-green-500 mr-2 mt-1"></i>
                                <span>Record expenses immediately to avoid forgetting</span>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="glass rounded-2xl p-6 shadow-2xl">
                        <h3 class="text-lg font-bold text-gray-800 mb-4">
                            <i class="fas fa-bolt text-purple-500 mr-2"></i>
                            Quick Actions
                        </h3>
                        <div class="space-y-2">
                            <a href="dashboard.php" class="block w-full text-center py-2 px-4 bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 transition duration-300">
                                <i class="fas fa-home mr-2"></i>Dashboard
                            </a>
                            <a href="budget.php" class="block w-full text-center py-2 px-4 bg-green-100 text-green-700 rounded-lg hover:bg-green-200 transition duration-300">
                                <i class="fas fa-piggy-bank mr-2"></i>Set Budget
                            </a>
                            <a href="reports.php" class="block w-full text-center py-2 px-4 bg-purple-100 text-purple-700 rounded-lg hover:bg-purple-200 transition duration-300">
                                <i class="fas fa-chart-line mr-2"></i>View Reports
                            </a>
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
                <p class="text-white opacity-75">&copy; 2026 Expance. All rights reserved. Made with ❤️ for better financial management.</p>
            </div>
        </div>
    </footer>

    <script>
        // Quick amount setter
        function setAmount(amount) {
            document.getElementById('amount').value = amount;
            document.getElementById('amount').focus();
        }

        // Enhanced form interactions
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('expenseForm');
            const inputs = form.querySelectorAll('input, select');

            // Animate form elements on load
            const formElements = document.querySelectorAll('.input-group, .btn-gradient');
            formElements.forEach((element, index) => {
                element.style.opacity = '0';
                element.style.transform = 'translateY(20px)';
                element.style.transition = 'all 0.6s ease';
                
                setTimeout(() => {
                    element.style.opacity = '1';
                    element.style.transform = 'translateY(0)';
                }, index * 100);
            });

            // Enhanced file upload functionality
            const fileInput = document.getElementById('receipt');
            const fileUpload = document.getElementById('fileUpload');
            const fileUploadLabel = document.getElementById('fileUploadLabel');
            const filePreview = document.getElementById('filePreview');
            const uploadProgress = document.getElementById('uploadProgress');
            const uploadProgressBar = document.getElementById('uploadProgressBar');
            const fileValidation = document.getElementById('fileValidation');
            
            let selectedFile = null;
            
            // File validation function
            function validateFile(file) {
                const maxSize = 50 * 1024 * 1024; // 50MB
                const allowedTypes = [
                    // Images
                    'image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/bmp', 
                    'image/webp', 'image/tiff', 'image/svg+xml',
                    // Documents
                    'application/pdf', 'application/msword', 
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'text/plain', 'application/rtf',
                    // Spreadsheets
                    'application/vnd.ms-excel', 
                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                    'text/csv',
                    // Archives
                    'application/zip', 'application/x-rar-compressed', 'application/x-zip-compressed'
                ];
                
                if (!allowedTypes.includes(file.type)) {
                    showValidationMessage('Please select a valid file type (Images, PDF, Word, Excel, Text, or Archive files)', 'error');
                    return false;
                }
                
                if (file.size > maxSize) {
                    showValidationMessage('File size must be less than 50MB', 'error');
                    return false;
                }
                
                showValidationMessage('File is valid and ready to upload', 'success');
                return true;
            }
            
            // Show validation messages
            function showValidationMessage(message, type) {
                fileValidation.textContent = message;
                fileValidation.className = `file-validation ${type}`;
                fileValidation.style.display = 'block';
                
                if (type === 'success') {
                    setTimeout(() => {
                        fileValidation.style.display = 'none';
                    }, 3000);
                }
            }
            
            // Format file size
            function formatFileSize(bytes) {
                if (bytes === 0) return '0 Bytes';
                const k = 1024;
                const sizes = ['Bytes', 'KB', 'MB', 'GB'];
                const i = Math.floor(Math.log(bytes) / Math.log(k));
                return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
            }
            
            // Get file icon based on type
            function getFileIcon(fileType) {
                if (fileType.startsWith('image/')) {
                    return '<i class="fas fa-image"></i>';
                } else if (fileType === 'application/pdf') {
                    return '<i class="fas fa-file-pdf"></i>';
                } else if (fileType.includes('word') || fileType.includes('document')) {
                    return '<i class="fas fa-file-word"></i>';
                } else if (fileType.includes('excel') || fileType.includes('spreadsheet') || fileType === 'text/csv') {
                    return '<i class="fas fa-file-excel"></i>';
                } else if (fileType === 'text/plain' || fileType === 'application/rtf') {
                    return '<i class="fas fa-file-alt"></i>';
                } else if (fileType.includes('zip') || fileType.includes('rar')) {
                    return '<i class="fas fa-file-archive"></i>';
                }
                return '<i class="fas fa-file"></i>';
            }
            
            // Show file preview
            function showFilePreview(file) {
                let fileIconClass = 'other';
                
                if (file.type.startsWith('image/')) {
                    fileIconClass = 'image';
                } else if (file.type === 'application/pdf') {
                    fileIconClass = 'pdf';
                } else if (file.type.includes('word') || file.type.includes('document')) {
                    fileIconClass = 'doc';
                } else if (file.type.includes('excel') || file.type.includes('spreadsheet') || file.type === 'text/csv') {
                    fileIconClass = 'excel';
                }
                
                filePreview.innerHTML = `
                    <div class="file-preview">
                        <div class="file-info">
                            <div class="file-icon ${fileIconClass}">
                                ${getFileIcon(file.type)}
                            </div>
                            <div class="file-details">
                                <h4>${file.name}</h4>
                                <p>${formatFileSize(file.size)} • ${file.type.split('/')[1].toUpperCase()}</p>
                            </div>
                        </div>
                        <button type="button" class="remove-file" onclick="removeFile()">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                `;
                
                filePreview.style.display = 'block';
                fileUploadLabel.classList.add('has-file');
                
                // Update upload label
                fileUploadLabel.innerHTML = `
                    <div class="upload-icon">
                        <i class="fas fa-check-circle text-green-500"></i>
                    </div>
                    <div class="upload-text">
                        <div class="upload-title text-green-700">Receipt uploaded successfully!</div>
                        <div class="upload-subtitle">
                            Click to change or drag a new file
                        </div>
                    </div>
                `;
            }
            
            // Remove file function
            window.removeFile = function() {
                selectedFile = null;
                fileInput.value = '';
                filePreview.style.display = 'none';
                fileUploadLabel.classList.remove('has-file');
                fileValidation.style.display = 'none';
                
                // Reset upload label
                fileUploadLabel.innerHTML = `
                    <div class="upload-icon">
                        <i class="fas fa-cloud-upload-alt"></i>
                    </div>
                    <div class="upload-text">
                        <div class="upload-title">Drop your receipt here</div>
                        <div class="upload-subtitle">
                            or <span style="color: #4facfe; font-weight: 600;">click to browse</span>
                        </div>
                        <div class="upload-formats">
                            <div class="format-item">
                                <i class="fas fa-image"></i>
                                <span>Images</span>
                            </div>
                            <div class="format-item">
                                <i class="fas fa-file-pdf"></i>
                                <span>PDF</span>
                            </div>
                            <div class="format-item">
                                <i class="fas fa-file-word"></i>
                                <span>Word</span>
                            </div>
                            <div class="format-item">
                                <i class="fas fa-file-excel"></i>
                                <span>Excel</span>
                            </div>
                            <div class="format-item">
                                <i class="fas fa-file-alt"></i>
                                <span>Text</span>
                            </div>
                            <div class="format-item">
                                <i class="fas fa-file-archive"></i>
                                <span>Archive</span>
                            </div>
                        </div>
                    </div>
                `;
            };
            
            // Simulate upload progress (for visual feedback)
            function simulateUploadProgress() {
                uploadProgress.style.display = 'block';
                let progress = 0;
                
                const interval = setInterval(() => {
                    progress += Math.random() * 30;
                    if (progress >= 100) {
                        progress = 100;
                        clearInterval(interval);
                        setTimeout(() => {
                            uploadProgress.style.display = 'none';
                        }, 500);
                    }
                    uploadProgressBar.style.width = progress + '%';
                }, 100);
            }
            
            // File input change event
            fileInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file && validateFile(file)) {
                    selectedFile = file;
                    simulateUploadProgress();
                    setTimeout(() => {
                        showFilePreview(file);
                    }, 1000);
                } else {
                    this.value = '';
                }
            });
            
            // Drag and drop functionality
            fileUploadLabel.addEventListener('dragover', function(e) {
                e.preventDefault();
                this.classList.add('dragover');
            });
            
            fileUploadLabel.addEventListener('dragleave', function(e) {
                e.preventDefault();
                this.classList.remove('dragover');
            });
            
            fileUploadLabel.addEventListener('drop', function(e) {
                e.preventDefault();
                this.classList.remove('dragover');
                
                const files = e.dataTransfer.files;
                if (files.length > 0) {
                    const file = files[0];
                    if (validateFile(file)) {
                        fileInput.files = files;
                        selectedFile = file;
                        simulateUploadProgress();
                        setTimeout(() => {
                            showFilePreview(file);
                        }, 1000);
                    }
                }
            });

            // Form validation
            form.addEventListener('submit', function(e) {
                let isValid = true;
                
                inputs.forEach(input => {
                    if (input.hasAttribute('required') && !input.value.trim()) {
                        input.style.borderColor = '#ef4444';
                        input.style.boxShadow = '0 0 0 3px rgba(239, 68, 68, 0.1)';
                        isValid = false;
                    }
                });
                
                if (!isValid) {
                    e.preventDefault();
                    alert('Please fill in all required fields!');
                }
            });

            // Reset border colors on input
            inputs.forEach(input => {
                input.addEventListener('input', function() {
                    if (this.value.trim()) {
                        this.style.borderColor = '#10b981';
                        this.style.boxShadow = '0 0 0 3px rgba(16, 185, 129, 0.1)';
                    }
                });
            });
        });
    </script>
</body>
</html>