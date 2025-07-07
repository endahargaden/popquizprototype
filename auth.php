<?php
session_start();
require_once 'config.php';

// Registration function
function registerUser($username, $email, $password) {
    $pdo = getDBConnection();
    if (!$pdo) return ['success' => false, 'message' => 'Database connection failed'];
    
    // Validate input
    if (empty($username) || empty($email) || empty($password)) {
        return ['success' => false, 'message' => 'All fields are required'];
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['success' => false, 'message' => 'Invalid email format'];
    }
    
    if (strlen($password) < 6) {
        return ['success' => false, 'message' => 'Password must be at least 6 characters'];
    }
    
    try {
        // Check if user already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        
        if ($stmt->rowCount() > 0) {
            return ['success' => false, 'message' => 'Username or email already exists'];
        }
        
        // Hash password and insert user
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)");
        $stmt->execute([$username, $email, $password_hash]);
        
        return ['success' => true, 'message' => 'Registration successful'];
    } catch (PDOException $e) {
        error_log("Registration error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Registration failed'];
    }
}

// Login function
function loginUser($username, $password) {
    $pdo = getDBConnection();
    if (!$pdo) return ['success' => false, 'message' => 'Database connection failed'];
    
    try {
        $stmt = $pdo->prepare("SELECT id, username, email, password_hash FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            return ['success' => true, 'message' => 'Login successful'];
        } else {
            return ['success' => false, 'message' => 'Invalid username/email or password'];
        }
    } catch (PDOException $e) {
        error_log("Login error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Login failed'];
    }
}

// Logout function
function logoutUser() {
    session_destroy();
    return ['success' => true, 'message' => 'Logout successful'];
}

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Get current user data
function getCurrentUser() {
    if (!isLoggedIn()) return null;
    
    $pdo = getDBConnection();
    if (!$pdo) return null;
    
    try {
        $stmt = $pdo->prepare("SELECT id, username, email, created_at FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Get user error: " . $e->getMessage());
        return null;
    }
}

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    // Only handle auth-specific actions
    $authActions = ['register', 'login', 'logout'];
    
    if (in_array($_POST['action'], $authActions)) {
        header('Content-Type: application/json');
        
        switch ($_POST['action']) {
            case 'register':
                $result = registerUser(
                    $_POST['username'] ?? '',
                    $_POST['email'] ?? '',
                    $_POST['password'] ?? ''
                );
                echo json_encode($result);
                break;
                
            case 'login':
                $result = loginUser(
                    $_POST['username'] ?? '',
                    $_POST['password'] ?? ''
                );
                echo json_encode($result);
                break;
                
            case 'logout':
                $result = logoutUser();
                echo json_encode($result);
                break;
                
            default:
                echo json_encode(['success' => false, 'message' => 'Invalid action']);
        }
        exit;
    }
    // If it's not an auth action, let other files handle it
}
?> 