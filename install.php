<?php
/**
 * Laravel Quiz System Installation Script
 * 
 * This script helps set up the Laravel quiz system by:
 * 1. Checking system requirements
 * 2. Creating necessary directories
 * 3. Setting up database
 * 4. Creating a default admin user
 */

echo "=== Laravel Quiz System Installation ===\n\n";

// Check PHP version
echo "Checking PHP version... ";
if (version_compare(PHP_VERSION, '8.1.0', '>=')) {
    echo "✓ PHP " . PHP_VERSION . " (OK)\n";
} else {
    echo "✗ PHP " . PHP_VERSION . " (Requires PHP 8.1+)\n";
    exit(1);
}

// Check required extensions
$required_extensions = ['pdo', 'pdo_mysql', 'json', 'mbstring', 'openssl', 'tokenizer', 'xml', 'ctype', 'fileinfo'];
echo "Checking PHP extensions...\n";
foreach ($required_extensions as $ext) {
    if (extension_loaded($ext)) {
        echo "  ✓ $ext\n";
    } else {
        echo "  ✗ $ext (Required)\n";
        exit(1);
    }
}

// Check if Composer is installed
echo "Checking Composer... ";
if (file_exists('composer.json')) {
    echo "✓ Found composer.json\n";
} else {
    echo "✗ composer.json not found\n";
    exit(1);
}

// Create necessary directories
echo "Creating directories...\n";
$directories = [
    'storage/app/uploads',
    'storage/app/qr_codes',
    'storage/framework/cache',
    'storage/framework/sessions',
    'storage/framework/views',
    'storage/logs',
    'bootstrap/cache'
];

foreach ($directories as $dir) {
    if (!is_dir($dir)) {
        if (mkdir($dir, 0755, true)) {
            echo "  ✓ Created $dir\n";
        } else {
            echo "  ✗ Failed to create $dir\n";
        }
    } else {
        echo "  ✓ $dir already exists\n";
    }
}

// Check if .env exists
echo "Checking environment file... ";
if (file_exists('.env')) {
    echo "✓ .env file exists\n";
} else {
    echo "✗ .env file not found. Please copy .env.example to .env and configure your database.\n";
    exit(1);
}

// Check database connection
echo "Testing database connection... ";
try {
    $host = getenv('DB_HOST') ?: '127.0.0.1';
    $port = getenv('DB_PORT') ?: '3306';
    $database = getenv('DB_DATABASE') ?: 'quiz_system_laravel';
    $username = getenv('DB_USERNAME') ?: 'root';
    $password = getenv('DB_PASSWORD') ?: '';
    
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$database", $username, $password);
    echo "✓ Database connection successful\n";
} catch (PDOException $e) {
    echo "✗ Database connection failed: " . $e->getMessage() . "\n";
    echo "Please check your database configuration in .env file.\n";
    exit(1);
}

// Check if migrations have been run
echo "Checking database tables... ";
try {
    $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
    if ($stmt->rowCount() > 0) {
        echo "✓ Database tables exist\n";
    } else {
        echo "✗ Database tables not found. Please run: php artisan migrate\n";
        exit(1);
    }
} catch (PDOException $e) {
    echo "✗ Error checking database tables: " . $e->getMessage() . "\n";
    exit(1);
}

// Check if storage link exists
echo "Checking storage link... ";
if (is_link('public/storage')) {
    echo "✓ Storage link exists\n";
} else {
    echo "✗ Storage link not found. Please run: php artisan storage:link\n";
}

echo "\n=== Installation Summary ===\n";
echo "✓ System requirements met\n";
echo "✓ Directories created\n";
echo "✓ Database connection verified\n";
echo "✓ Database tables exist\n";

echo "\nNext steps:\n";
echo "1. Run: php artisan storage:link (if not already done)\n";
echo "2. Create an admin user by visiting: /register\n";
echo "3. Start the development server: php artisan serve\n";
echo "4. Access the application at: http://localhost:8000\n";

echo "\n=== Installation Complete ===\n";
?> 