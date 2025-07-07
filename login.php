<?php
require_once 'auth.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz System - Login/Register</title>
    <link rel="stylesheet" href="auth.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="auth-container">
        <div class="auth-header">
            <div class="auth-logo">
                <i class="fas fa-graduation-cap"></i>
            </div>
            <h1 class="auth-title">Quiz Management System</h1>
            <p class="auth-subtitle">Login or create a new account to get started</p>
        </div>
        
        <div class="auth-tabs">
            <div class="auth-tab active" data-tab="login">
                <i class="fas fa-sign-in-alt"></i>
                Login
            </div>
            <div class="auth-tab" data-tab="register">
                <i class="fas fa-user-plus"></i>
                Register
            </div>
        </div>
        
        <!-- Login Form -->
        <form id="login-form" class="auth-form active">
            <div class="form-group">
                <label for="login-username" class="form-label">
                    <i class="fas fa-user"></i>
                    Username or Email
                </label>
                <input type="text" id="login-username" name="username" class="form-input" required>
            </div>
            <div class="form-group">
                <label for="login-password" class="form-label">
                    <i class="fas fa-lock"></i>
                    Password
                </label>
                <input type="password" id="login-password" name="password" class="form-input" required>
            </div>
            <button type="submit" class="auth-btn">
                <i class="fas fa-sign-in-alt"></i>
                Login
            </button>
        </form>
        
        <!-- Register Form -->
        <form id="register-form" class="auth-form">
            <div class="form-group">
                <label for="register-username" class="form-label">
                    <i class="fas fa-user"></i>
                    Username
                </label>
                <input type="text" id="register-username" name="username" class="form-input" required>
            </div>
            <div class="form-group">
                <label for="register-email" class="form-label">
                    <i class="fas fa-envelope"></i>
                    Email
                </label>
                <input type="email" id="register-email" name="email" class="form-input" required>
            </div>
            <div class="form-group">
                <label for="register-password" class="form-label">
                    <i class="fas fa-lock"></i>
                    Password
                </label>
                <input type="password" id="register-password" name="password" class="form-input" required>
            </div>
            <button type="submit" class="auth-btn">
                <i class="fas fa-user-plus"></i>
                Register
            </button>
        </form>
        
        <div id="message"></div>
        
        <div class="auth-footer">
            <p>Create interactive quizzes and track student performance</p>
        </div>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script>
        $(document).ready(function() {
            // Tab switching
            $('.auth-tab').click(function() {
                $('.auth-tab').removeClass('active');
                $('.auth-form').removeClass('active');
                $(this).addClass('active');
                $('#' + $(this).data('tab') + '-form').addClass('active');
                $('#message').empty();
            });
            
            // Login form submission
            $('#login-form').submit(function(e) {
                e.preventDefault();
                const btn = $(this).find('.auth-btn');
                const originalText = btn.html();
                
                btn.html('<span class="loading"></span>Logging in...');
                btn.prop('disabled', true);
                
                const formData = {
                    action: 'login',
                    username: $('#login-username').val(),
                    password: $('#login-password').val()
                };
                
                $.post('auth.php', formData, function(response) {
                    if (response.success) {
                        showMessage('Login successful! Redirecting...', 'success');
                        setTimeout(function() {
                            window.location.href = 'dashboard.php';
                        }, 1000);
                    } else {
                        showMessage(response.message, 'error');
                        btn.html(originalText);
                        btn.prop('disabled', false);
                    }
                }, 'json').fail(function() {
                    showMessage('Network error. Please try again.', 'error');
                    btn.html(originalText);
                    btn.prop('disabled', false);
                });
            });
            
            // Register form submission
            $('#register-form').submit(function(e) {
                e.preventDefault();
                const btn = $(this).find('.auth-btn');
                const originalText = btn.html();
                
                btn.html('<span class="loading"></span>Creating account...');
                btn.prop('disabled', true);
                
                const formData = {
                    action: 'register',
                    username: $('#register-username').val(),
                    email: $('#register-email').val(),
                    password: $('#register-password').val()
                };
                
                $.post('auth.php', formData, function(response) {
                    if (response.success) {
                        showMessage(response.message, 'success');
                        // Switch to login tab after successful registration
                        setTimeout(function() {
                            $('.auth-tab[data-tab="login"]').click();
                            btn.html(originalText);
                            btn.prop('disabled', false);
                        }, 2000);
                    } else {
                        showMessage(response.message, 'error');
                        btn.html(originalText);
                        btn.prop('disabled', false);
                    }
                }, 'json').fail(function() {
                    showMessage('Network error. Please try again.', 'error');
                    btn.html(originalText);
                    btn.prop('disabled', false);
                });
            });
            
            function showMessage(message, type) {
                const messageDiv = $('#message');
                messageDiv.removeClass('success-message error-message-display');
                messageDiv.addClass(type === 'success' ? 'success-message' : 'error-message-display');
                messageDiv.text(message);
                messageDiv.show();
                
                if (type === 'success') {
                    setTimeout(function() {
                        messageDiv.fadeOut();
                    }, 3000);
                }
            }
        });
    </script>
    
    <style>
        .auth-tabs {
            display: flex;
            margin-bottom: 30px;
            background: #f8f9fa;
            border-radius: 15px;
            padding: 5px;
            gap: 5px;
        }
        
        .auth-tab {
            flex: 1;
            padding: 15px;
            text-align: center;
            cursor: pointer;
            border-radius: 10px;
            transition: all 0.3s ease;
            font-weight: 600;
            color: #666;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        
        .auth-tab.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }
        
        .auth-form {
            display: none;
        }
        
        .auth-form.active {
            display: block;
        }
    </style>
</body>
</html> 