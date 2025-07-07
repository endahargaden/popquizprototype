<?php
/**
 * Quiz System Installation Script
 * Run this file once to set up the database and check system requirements
 */

echo "<h1>Quiz System Installation</h1>";

// Check PHP version
echo "<h2>System Requirements Check</h2>";
$php_version = phpversion();
echo "<p>PHP Version: $php_version</p>";

if (version_compare($php_version, '7.4.0', '>=')) {
    echo "<p style='color: green;'>✓ PHP version is compatible</p>";
} else {
    echo "<p style='color: red;'>✗ PHP version 7.4+ required</p>";
    exit;
}

// Check required extensions
$required_extensions = ['pdo', 'pdo_mysql', 'json', 'session'];
echo "<h3>Required Extensions:</h3>";
foreach ($required_extensions as $ext) {
    if (extension_loaded($ext)) {
        echo "<p style='color: green;'>✓ $ext extension loaded</p>";
    } else {
        echo "<p style='color: red;'>✗ $ext extension not found</p>";
        exit;
    }
}

// Check directory permissions
echo "<h2>Directory Permissions Check</h2>";
$directories = ['uploads', 'qr_codes', 'logs'];
foreach ($directories as $dir) {
    if (!file_exists($dir)) {
        if (mkdir($dir, 0755, true)) {
            echo "<p style='color: green;'>✓ Created directory: $dir</p>";
        } else {
            echo "<p style='color: red;'>✗ Failed to create directory: $dir</p>";
        }
    } else {
        if (is_writable($dir)) {
            echo "<p style='color: green;'>✓ Directory writable: $dir</p>";
        } else {
            echo "<p style='color: red;'>✗ Directory not writable: $dir</p>";
        }
    }
}

// Database setup
echo "<h2>Database Setup</h2>";

// Include config to get database settings
require_once 'config.php';

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "<p style='color: green;'>✓ Database connection successful</p>";
    
    // Create tables
    echo "<h3>Creating Database Tables:</h3>";
    
    // Users table
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(255) UNIQUE NOT NULL,
        email VARCHAR(255) UNIQUE NOT NULL,
        password_hash VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    echo "<p style='color: green;'>✓ Users table created/verified</p>";
    
    // Quizzes table
    $pdo->exec("CREATE TABLE IF NOT EXISTS quizzes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        quiz_id VARCHAR(32) UNIQUE NOT NULL,
        user_id INT NOT NULL,
        title VARCHAR(255) NOT NULL,
        json_file VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id)
    )");
    echo "<p style='color: green;'>✓ Quizzes table created/verified</p>";
    
    // Quiz results table
    $pdo->exec("CREATE TABLE IF NOT EXISTS quiz_results (
        id INT AUTO_INCREMENT PRIMARY KEY,
        quiz_id VARCHAR(32) NOT NULL,
        student_number VARCHAR(255) NOT NULL,
        session_id VARCHAR(255) NOT NULL,
        client_uuid VARCHAR(255) NOT NULL,
        user_ip VARCHAR(45) NOT NULL,
        user_agent TEXT,
        answers JSON,
        final_score INT,
        individual_scores JSON,
        completed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (quiz_id) REFERENCES quizzes(quiz_id)
    )");
    echo "<p style='color: green;'>✓ Quiz results table created/verified</p>";
    
    echo "<p style='color: green;'>✓ All database tables created successfully!</p>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>✗ Database connection failed: " . $e->getMessage() . "</p>";
    echo "<p>Please check your database credentials in config.php</p>";
    exit;
}

// Test QR code generation
echo "<h2>QR Code Generation Test</h2>";
$test_url = "http://example.com/quiz.php?id=test123";
$qr_url = "https://chart.googleapis.com/chart?chs=300x300&cht=qr&chl=" . urlencode($test_url);
$qr_image = @file_get_contents($qr_url);

if ($qr_image !== false) {
    echo "<p style='color: green;'>✓ QR code generation working</p>";
} else {
    echo "<p style='color: orange;'>⚠ QR code generation may not work (requires internet connection)</p>";
}

// Final setup instructions
echo "<h2>Installation Complete!</h2>";
echo "<div style='background: #e8f5e8; padding: 20px; border-radius: 5px;'>";
echo "<h3>Next Steps:</h3>";
echo "<ol>";
echo "<li>Delete this install.php file for security</li>";
echo "<li>Visit <a href='login.php'>login.php</a> to create your first account</li>";
echo "<li>Use the dashboard to create your first quiz</li>";
echo "<li>Share the generated QR codes with your students</li>";
echo "</ol>";
echo "</div>";

echo "<h3>System Information:</h3>";
echo "<ul>";
echo "<li><strong>Database:</strong> " . DB_NAME . " on " . DB_HOST . "</li>";
echo "<li><strong>Upload Directory:</strong> " . UPLOAD_DIR . "</li>";
echo "<li><strong>QR Code Directory:</strong> " . QR_DIR . "</li>";
echo "<li><strong>Log Directory:</strong> " . LOG_DIR . "</li>";
echo "</ul>";

echo "<p><strong>Important:</strong> Remember to delete this install.php file after successful installation!</p>";
?> 