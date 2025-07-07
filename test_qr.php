<?php
require_once 'config.php';
require_once 'qr_generator.php';

echo "<h2>QR Code Generation Test</h2>";

// Test data
$testUrl = "http://localhost/popquizprototype/quiz.php?id=test123";
$testFilename = QR_DIR . "test_qr.png";

echo "<p>Testing QR code generation for URL: " . htmlspecialchars($testUrl) . "</p>";
echo "<p>Output file: " . $testFilename . "</p>";

// Test the QR generation
if (generateQRCodeWithAPI($testUrl, $testFilename)) {
    echo "<p style='color: green;'>✅ QR code generated successfully!</p>";
    
    if (file_exists($testFilename)) {
        $fileSize = filesize($testFilename);
        echo "<p>File size: " . $fileSize . " bytes</p>";
        
        if ($fileSize > 100) {
            echo "<p style='color: green;'>✅ QR code file is valid</p>";
            echo "<img src='" . $testFilename . "' alt='Test QR Code' style='max-width: 200px; border: 1px solid #ccc;'>";
        } else {
            echo "<p style='color: orange;'>⚠️ QR code file is too small, might be corrupted</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ QR code file was not created</p>";
    }
} else {
    echo "<p style='color: red;'>❌ QR code generation failed</p>";
}

// Test directory permissions
echo "<h3>Directory Permissions Test</h3>";
echo "<p>QR Directory: " . QR_DIR . "</p>";
echo "<p>Directory exists: " . (is_dir(QR_DIR) ? 'Yes' : 'No') . "</p>";
echo "<p>Directory writable: " . (is_writable(QR_DIR) ? 'Yes' : 'No') . "</p>";

// List existing QR codes
echo "<h3>Existing QR Codes</h3>";
if (is_dir(QR_DIR)) {
    $files = scandir(QR_DIR);
    $qrFiles = array_filter($files, function($file) {
        return pathinfo($file, PATHINFO_EXTENSION) === 'png';
    });
    
    if (count($qrFiles) > 0) {
        echo "<p>Found " . count($qrFiles) . " QR code files:</p>";
        echo "<ul>";
        foreach ($qrFiles as $file) {
            $filePath = QR_DIR . $file;
            $fileSize = filesize($filePath);
            echo "<li>" . $file . " (" . $fileSize . " bytes)</li>";
        }
        echo "</ul>";
    } else {
        echo "<p>No QR code files found in directory.</p>";
    }
}

echo "<br><a href='dashboard.php'>Back to Dashboard</a>";
?> 