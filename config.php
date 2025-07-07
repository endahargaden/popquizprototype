<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'quiz_system');
define('DB_USER', 'root');
define('DB_PASS', '');

// System configuration
define('UPLOAD_DIR', __DIR__ . '/uploads/');
define('QR_DIR', __DIR__ . '/qr_codes/');
define('LOG_DIR', __DIR__ . '/logs/');

// Create directories if they don't exist
if (!file_exists(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0755, true);
if (!file_exists(QR_DIR)) mkdir(QR_DIR, 0755, true);
if (!file_exists(LOG_DIR)) mkdir(LOG_DIR, 0755, true);

// Database connection function
function getDBConnection() {
    try {
        $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch(PDOException $e) {
        error_log("Database connection failed: " . $e->getMessage());
        return false;
    }
}

// Initialize database tables
function initializeDatabase() {
    $pdo = getDBConnection();
    if (!$pdo) return false;
    
    // Users table
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(255) UNIQUE NOT NULL,
        email VARCHAR(255) UNIQUE NOT NULL,
        password_hash VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
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
    
    return true;
}

// Call initialization
initializeDatabase();
?> 