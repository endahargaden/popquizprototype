<?php
// Test QR code generation
$quizId = 'test123';
$quizUrl = 'http://localhost:8000/quiz/' . $quizId;
$qrCodePath = __DIR__ . '/storage/app/qr_codes/quiz_' . $quizId . '.png';

echo "Testing QR code generation...\n";
echo "Quiz URL: $quizUrl\n";
echo "QR Code Path: $qrCodePath\n";

// Ensure QR directory exists
if (!is_dir(dirname($qrCodePath))) {
    mkdir(dirname($qrCodePath), 0755, true);
    echo "Created QR codes directory\n";
}

// Try QR Server API
$qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" . urlencode($quizUrl) . "&format=png";
echo "Fetching from: $qrUrl\n";

$context = stream_context_create([
    'http' => [
        'timeout' => 15,
        'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
        'follow_location' => true,
        'max_redirects' => 3
    ]
]);

$qrImage = @file_get_contents($qrUrl, false, $context);

if ($qrImage !== false && strlen($qrImage) > 100) {
    if (file_put_contents($qrCodePath, $qrImage) !== false) {
        echo "QR code generated successfully!\n";
        echo "File size: " . strlen($qrImage) . " bytes\n";
        echo "Saved to: $qrCodePath\n";
    } else {
        echo "Failed to save QR code file\n";
    }
} else {
    echo "Failed to fetch QR code from API\n";
    echo "Response length: " . ($qrImage ? strlen($qrImage) : 0) . "\n";
}
?> 