<?php
require_once 'auth.php';
require_once 'config.php';

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    // Redirect if not logged in
    if (!isLoggedIn()) {
        error_log("User not logged in when accessing quiz_manager.php");
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Not authenticated']);
        exit;
    }

    header('Content-Type: application/json');
    
    switch ($_POST['action']) {
        case 'create_quiz':
            $result = createQuiz($_POST['quiz_data'] ?? '');
            echo json_encode($result);
            break;
            
        case 'get_quiz_stats':
            $result = getQuizStats($_POST['quiz_id'] ?? '');
            echo json_encode($result);
            break;
            
        case 'delete_quiz':
            $result = deleteQuiz($_POST['quiz_id']);
            echo json_encode($result);
            break;
            
        case 'regenerate_qr':
            $result = regenerateQRCode($_POST['quiz_id']);
            echo json_encode($result);
            break;
            
        default:
            error_log("Invalid action in quiz_manager: " . $_POST['action']);
            echo json_encode(['success' => false, 'message' => 'Invalid action: ' . $_POST['action']]);
    }
    exit;
}

// Create a new quiz
function createQuiz($quizDataJson) {
    error_log("createQuiz called with data length: " . strlen($quizDataJson));
    
    $pdo = getDBConnection();
    if (!$pdo) {
        error_log("Database connection failed in createQuiz");
        return ['success' => false, 'message' => 'Database connection failed'];
    }
    
    try {
        $quizData = json_decode($quizDataJson, true);
        if (!$quizData || !is_array($quizData)) {
            error_log("Invalid quiz data format");
            return ['success' => false, 'message' => 'Invalid quiz data'];
        }
        
        error_log("Quiz data decoded successfully, questions count: " . count($quizData));
        
        // Validate quiz structure
        if (!validateQuizStructure($quizData)) {
            error_log("Quiz structure validation failed");
            return ['success' => false, 'message' => 'Invalid quiz structure'];
        }
        
        // Generate unique quiz ID
        $quizId = generateUniqueQuizId();
        error_log("Generated quiz ID: " . $quizId);
        
        // Generate unique filename
        $filename = generateUniqueFilename();
        $filePath = UPLOAD_DIR . $filename;
        error_log("Quiz file path: " . $filePath);
        
        // Save quiz data to file
        if (file_put_contents($filePath, json_encode($quizData, JSON_PRETTY_PRINT)) === false) {
            error_log("Failed to save quiz file to: " . $filePath);
            return ['success' => false, 'message' => 'Failed to save quiz file'];
        }
        
        // Extract title from first question or use default
        $title = extractQuizTitle($quizData);
        error_log("Quiz title: " . $title);
        
        // Save to database
        $stmt = $pdo->prepare("INSERT INTO quizzes (quiz_id, user_id, title, json_file) VALUES (?, ?, ?, ?)");
        $stmt->execute([$quizId, $_SESSION['user_id'], $title, $filename]);
        error_log("Quiz saved to database successfully");
        
        // Generate QR code
        $qrCodePath = generateQRCode($quizId);
        error_log("QR code generated: " . ($qrCodePath ?: 'failed'));
        
        // Prepare response - quiz is created successfully even if QR code fails
        $response = [
            'success' => true, 
            'message' => 'Quiz created successfully',
            'quiz_id' => $quizId,
            'url' => getQuizUrl($quizId)
        ];
        
        // Add QR code to response if it was generated successfully
        if ($qrCodePath) {
            $response['qr_code'] = $qrCodePath;
        } else {
            $response['qr_code'] = null;
            $response['message'] .= ' (QR code generation failed)';
        }
        
        return $response;
        
    } catch (Exception $e) {
        error_log("Quiz creation error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Failed to create quiz: ' . $e->getMessage()];
    }
}

// Validate quiz structure
function validateQuizStructure($data) {
    if (!is_array($data) || empty($data)) {
        return false;
    }
    
    foreach ($data as $question) {
        if (!isset($question['id']) || !isset($question['question']) || 
            !isset($question['options']) || !isset($question['correct_answer'])) {
            return false;
        }
        
        if (!is_array($question['options']) || count($question['options']) < 2) {
            return false;
        }
        
        if (!in_array($question['correct_answer'], $question['options'])) {
            return false;
        }
    }
    
    return true;
}

// Generate unique quiz ID
function generateUniqueQuizId() {
    $pdo = getDBConnection();
    do {
        $quizId = bin2hex(random_bytes(16));
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM quizzes WHERE quiz_id = ?");
        $stmt->execute([$quizId]);
    } while ($stmt->fetchColumn() > 0);
    
    return $quizId;
}

// Generate unique filename
function generateUniqueFilename() {
    $timestamp = time();
    $random = bin2hex(random_bytes(8));
    return "quiz_{$timestamp}_{$random}.json";
}

// Extract quiz title from data
function extractQuizTitle($quizData) {
    if (!empty($quizData[0]['question'])) {
        $firstQuestion = $quizData[0]['question'];
        // Use first 50 characters of first question as title
        return substr($firstQuestion, 0, 50) . (strlen($firstQuestion) > 50 ? '...' : '');
    }
    return 'Untitled Quiz';
}

// Generate QR code
function generateQRCode($quizId) {
    require_once 'qr_generator.php';
    
    $quizUrl = getQuizUrl($quizId);
    $qrCodePath = QR_DIR . "quiz_{$quizId}.png";
    
    // Ensure QR directory exists
    if (!is_dir(QR_DIR)) {
        mkdir(QR_DIR, 0755, true);
    }
    
    // Use the improved QR code generation function
    if (generateQRCodeWithAPI($quizUrl, $qrCodePath)) {
        return "qr_codes/quiz_{$quizId}.png";
    }
    
    // If all methods fail, log the error
    error_log("QR code generation failed for quiz: " . $quizId . " with URL: " . $quizUrl);
    return null;
}

// Get quiz URL
function getQuizUrl($quizId) {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $path = dirname($_SERVER['REQUEST_URI'] ?? '/popquizprototype');
    return "{$protocol}://{$host}{$path}/quiz.php?id={$quizId}";
}

// Get quiz statistics
function getQuizStats($quizId) {
    $pdo = getDBConnection();
    if (!$pdo) return ['success' => false, 'message' => 'Database connection failed'];
    
    try {
        // Get quiz info
        $stmt = $pdo->prepare("SELECT * FROM quizzes WHERE quiz_id = ? AND user_id = ?");
        $stmt->execute([$quizId, $_SESSION['user_id']]);
        $quiz = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$quiz) {
            return ['success' => false, 'message' => 'Quiz not found'];
        }
        
        // Get results
        $stmt = $pdo->prepare("SELECT * FROM quiz_results WHERE quiz_id = ? ORDER BY completed_at DESC");
        $stmt->execute([$quizId]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Calculate statistics
        $stats = calculateQuizStats($results, $quiz['json_file']);
        
        return [
            'success' => true,
            'quiz' => $quiz,
            'results' => $results,
            'stats' => $stats
        ];
        
    } catch (Exception $e) {
        error_log("Quiz stats error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Failed to get quiz statistics'];
    }
}

// Calculate quiz statistics
function calculateQuizStats($results, $jsonFile) {
    if (empty($results)) {
        return [
            'total_attempts' => 0,
            'average_score' => 0,
            'easiest_question' => null,
            'hardest_question' => null
        ];
    }
    
    // Load quiz questions
    $quizData = json_decode(file_get_contents(UPLOAD_DIR . $jsonFile), true);
    $numQuestions = count($quizData);
    
    // Calculate question difficulty
    $questionStats = array_fill(0, $numQuestions, ['correct' => 0, 'total' => 0]);
    
    foreach ($results as $result) {
        $individualScores = json_decode($result['individual_scores'], true);
        if ($individualScores) {
            foreach ($individualScores as $qIndex => $score) {
                if (isset($questionStats[$qIndex])) {
                    $questionStats[$qIndex]['total']++;
                    if ($score == 1) {
                        $questionStats[$qIndex]['correct']++;
                    }
                }
            }
        }
    }
    
    // Calculate averages
    $totalAttempts = count($results);
    $totalScores = array_sum(array_column($results, 'final_score'));
    $averageScore = $totalAttempts > 0 ? round($totalScores / $totalAttempts, 2) : 0;
    
    // Find easiest and hardest questions
    $questionDifficulties = [];
    foreach ($questionStats as $qIndex => $stats) {
        if ($stats['total'] > 0) {
            $difficulty = $stats['correct'] / $stats['total'];
            $questionDifficulties[$qIndex] = $difficulty;
        }
    }
    
    $easiestQuestion = null;
    $hardestQuestion = null;
    
    if (!empty($questionDifficulties)) {
        $easiestIndex = array_keys($questionDifficulties, max($questionDifficulties))[0];
        $hardestIndex = array_keys($questionDifficulties, min($questionDifficulties))[0];
        
        $easiestQuestion = [
            'index' => $easiestIndex + 1,
            'difficulty' => round($questionDifficulties[$easiestIndex] * 100, 1),
            'question' => $quizData[$easiestIndex]['question']
        ];
        
        $hardestQuestion = [
            'index' => $hardestIndex + 1,
            'difficulty' => round($questionDifficulties[$hardestIndex] * 100, 1),
            'question' => $quizData[$hardestIndex]['question']
        ];
    }
    
    return [
        'total_attempts' => $totalAttempts,
        'average_score' => $averageScore,
        'easiest_question' => $easiestQuestion,
        'hardest_question' => $hardestQuestion,
        'question_stats' => $questionStats
    ];
}

// Delete quiz
function deleteQuiz($quizId) {
    $pdo = getDBConnection();
    if (!$pdo) return ['success' => false, 'message' => 'Database connection failed'];
    
    try {
        // Get quiz info
        $stmt = $pdo->prepare("SELECT * FROM quizzes WHERE quiz_id = ? AND user_id = ?");
        $stmt->execute([$quizId, $_SESSION['user_id']]);
        $quiz = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$quiz) {
            return ['success' => false, 'message' => 'Quiz not found'];
        }
        
        // Delete JSON file
        $jsonFile = UPLOAD_DIR . $quiz['json_file'];
        if (file_exists($jsonFile)) {
            unlink($jsonFile);
        }
        
        // Delete QR code
        $qrFile = QR_DIR . "quiz_{$quizId}.png";
        if (file_exists($qrFile)) {
            unlink($qrFile);
        }
        
        // Delete from database
        $stmt = $pdo->prepare("DELETE FROM quizzes WHERE quiz_id = ? AND user_id = ?");
        $stmt->execute([$quizId, $_SESSION['user_id']]);
        
        return ['success' => true, 'message' => 'Quiz deleted successfully'];
        
    } catch (Exception $e) {
        error_log("Quiz deletion error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Failed to delete quiz'];
    }
}

// Regenerate QR code
function regenerateQRCode($quizId) {
    $pdo = getDBConnection();
    if (!$pdo) return ['success' => false, 'message' => 'Database connection failed'];
    
    try {
        // Check if quiz exists and belongs to user
        $stmt = $pdo->prepare("SELECT * FROM quizzes WHERE quiz_id = ? AND user_id = ?");
        $stmt->execute([$quizId, $_SESSION['user_id']]);
        $quiz = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$quiz) {
            return ['success' => false, 'message' => 'Quiz not found'];
        }
        
        // Delete existing QR code if it exists
        $qrFile = QR_DIR . "quiz_{$quizId}.png";
        if (file_exists($qrFile)) {
            unlink($qrFile);
        }
        
        // Generate new QR code
        $qrPath = generateQRCode($quizId);
        
        if ($qrPath) {
            return ['success' => true, 'message' => 'QR code regenerated successfully'];
        } else {
            return ['success' => false, 'message' => 'Failed to generate QR code'];
        }
        
    } catch (Exception $e) {
        error_log("QR regeneration error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Failed to regenerate QR code'];
    }
}
?> 