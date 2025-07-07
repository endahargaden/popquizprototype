<?php
require_once 'config.php';

// Get quiz ID from URL
$quiz_id = $_GET['id'] ?? '';
if (empty($quiz_id)) {
    die('Quiz ID is required');
}

// Get quiz information from database
$pdo = getDBConnection();
$quiz = null;
$questions = null;
if ($pdo) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM quizzes WHERE quiz_id = ?");
        $stmt->execute([$quiz_id]);
        $quiz = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($quiz) {
            $json_file_path = UPLOAD_DIR . $quiz['json_file'];
            if (file_exists($json_file_path)) {
                $json_content = file_get_contents($json_file_path);
                $questions = json_decode($json_content, true);
            }
        }
    } catch (Exception $e) {
        error_log("Error loading quiz: " . $e->getMessage());
    }
}
if (!$quiz || !$questions) {
    die('Quiz not found or invalid');
}

session_start();

$user_ip = get_client_ip();
$session_id = session_id();
$user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown User Agent';

function get_client_ip() {
    $ipaddress = '';
    if (isset($_SERVER['HTTP_CLIENT_IP']))
        $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
    else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
    else if(isset($_SERVER['HTTP_X_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
    else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
        $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
    else if(isset($_SERVER['HTTP_FORWARDED']))
        $ipaddress = $_SERVER['HTTP_FORWARDED'];
    else if(isset($_SERVER['REMOTE_ADDR']))
        $ipaddress = $_SERVER['REMOTE_ADDR'];
    else
        $ipaddress = 'UNKNOWN';
    return $ipaddress;
}

// Handle AJAX requests
if (isset($_GET['action'])) {
    header('Content-Type: application/json');
    if ($_GET['action'] === 'start_quiz_session') {
        $student_number = $_POST['studentNumber'] ?? '';
        $client_uuid = $_POST['clientUuid'] ?? '';
        if (empty($student_number)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Student number is required.']);
            exit;
        }
        // Check if student has already attempted this specific quiz
        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM quiz_results WHERE quiz_id = ? AND student_number = ?");
            $stmt->execute([$quiz_id, $student_number]);
            $attemptCount = $stmt->fetchColumn();
            if ($attemptCount > 0) {
                http_response_code(403);
                echo json_encode([
                    'status' => 'error', 
                    'message' => 'You have already attempted this quiz. Each student can only take a quiz once.',
                    'code' => 'ALREADY_ATTEMPTED'
                ]);
                exit;
            }
        } catch (Exception $e) {
            error_log("Error checking quiz attempt: " . $e->getMessage());
        }
        $_SESSION['quiz_state'] = 'active';
        $_SESSION['student_number'] = $student_number;
        $_SESSION['client_uuid'] = $client_uuid;
        $_SESSION['quiz_id'] = $quiz_id;
        $_SESSION['start_time'] = time();
        $_SESSION['user_ip'] = $user_ip;
        $_SESSION['session_id'] = $session_id;
        $_SESSION['user_agent'] = $user_agent;
        echo json_encode(['status' => 'success', 'message' => 'Quiz session started.']);
        exit;
    }
    if ($_GET['action'] === 'get_question') {
        if (isset($_SESSION['quiz_state']) && $_SESSION['quiz_state'] !== 'active') {
            echo json_encode(['status' => 'terminated', 'message' => 'Your quiz session has ended. Reason: ' . ($_SESSION['quiz_terminated_reason'] ?? 'Unknown')]);
            exit;
        }
        $question_index = isset($_GET['question_index']) ? (int)$_GET['question_index'] : 0;
        if (isset($questions[$question_index])) {
            $question_data = $questions[$question_index];
            unset($question_data['correct_answer']);
            echo json_encode(['status' => 'success', 'question' => $question_data]);
        } else {
            echo json_encode(['status' => 'end', 'message' => 'No more questions.']);
        }
        exit;
    }
    if ($_GET['action'] === 'validate_answer') {
        if (isset($_SESSION['quiz_state']) && $_SESSION['quiz_state'] !== 'active') {
            echo json_encode(['status' => 'terminated', 'message' => 'Your quiz session has ended.']);
            exit;
        }
        $json_input = file_get_contents('php://input');
        $input_data = json_decode($json_input, true);
        $question_id = $input_data['questionId'] ?? null;
        $selected_answer = $input_data['selectedAnswer'] ?? null;
        $is_correct = false;
        $correct_answer_text = null;
        if ($question_id !== null && $selected_answer !== null) {
            foreach ($questions as $q) {
                if ($q['id'] == $question_id) {
                    $correct_answer_text = $q['correct_answer'];
                    if ($selected_answer === $q['correct_answer']) {
                        $is_correct = true;
                    }
                    break;
                }
            }
        }
        echo json_encode([
            'status' => 'validation_result',
            'isCorrect' => $is_correct,
            'correctAnswerText' => $correct_answer_text
        ]);
        exit;
    }
    if ($_GET['action'] === 'end_quiz_session') {
        $json_input = file_get_contents('php://input');
        $data = json_decode($json_input, true);
        $termination_reason = $data['reason'] ?? 'Completed';
        $answers_to_log = $data['answers'] ?? [];
        $_SESSION['quiz_state'] = 'terminated';
        $_SESSION['quiz_terminated_reason'] = $termination_reason;
        $correct_answers_count = 0;
        $num_questions = count($questions);
        $question_scores = array_fill(0, $num_questions, 0);
        $question_id_to_index_map = [];
        foreach ($questions as $idx => $q) {
            $question_id_to_index_map[$q['id']] = $idx;
        }
        foreach ($answers_to_log as $answer) {
            $actual_is_correct = false;
            $question_id = $answer['questionId'] ?? null;
            if ($question_id !== null && isset($question_id_to_index_map[$question_id])) {
                $q_index = $question_id_to_index_map[$question_id];
                $q = $questions[$q_index];
                if (isset($answer['selectedAnswer']) && $answer['selectedAnswer'] !== 'Time Expired / No Answer' && $answer['selectedAnswer'] === $q['correct_answer']) {
                    $actual_is_correct = true;
                    $correct_answers_count++;
                    $question_scores[$q_index] = 1;
                }
            }
            $answer['serverValidatedCorrect'] = $actual_is_correct;
        }
        $student_number = $_SESSION['student_number'] ?? 'N/A';
        $client_uuid = $_SESSION['client_uuid'] ?? 'N/A';
        $session_id_log = $_SESSION['session_id'] ?? 'N/A';
        $user_ip_log = $_SESSION['user_ip'] ?? 'N/A';
        $user_agent_log = $_SESSION['user_agent'] ?? 'N/A';
        try {
            $stmt = $pdo->prepare("INSERT INTO quiz_results (quiz_id, student_number, session_id, client_uuid, user_ip, user_agent, answers, final_score, individual_scores) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $quiz_id,
                $student_number,
                $session_id_log,
                $client_uuid,
                $user_ip_log,
                $user_agent_log,
                json_encode($answers_to_log),
                $correct_answers_count,
                json_encode($question_scores)
            ]);
        } catch (Exception $e) {
            error_log("Error saving quiz results: " . $e->getMessage());
        }
        echo json_encode(['status' => 'success', 'message' => 'Quiz session ended and answers logged.']);
        exit;
    }
}

$display_start_screen = true;
$display_quiz_screen = false;
$display_end_screen = false;
$end_message = '';
if (isset($_SESSION['quiz_state']) && $_SESSION['quiz_state'] === 'terminated') {
    $display_end_screen = true;
    $display_start_screen = false;
    $end_message = 'Your previous quiz session ended. Reason: ' . ($_SESSION['quiz_terminated_reason'] ?? 'Unknown');
} else if (isset($_SESSION['quiz_state']) && $_SESSION['quiz_state'] === 'active') {
    $_SESSION['quiz_state'] = 'terminated';
    $_SESSION['quiz_terminated_reason'] = 'Quiz terminated because the page was refreshed.';
    $display_end_screen = true;
    $display_start_screen = false;
    $end_message = 'Your quiz was terminated because the page was refreshed.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($quiz['title']); ?> - Quiz</title>
    <link rel="stylesheet" href="quiz.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <div id="start-screen" style="display: <?php echo $display_start_screen ? 'block' : 'none'; ?>;">
            <h1>
                <i class="fas fa-graduation-cap"></i>
                <?php echo htmlspecialchars($quiz['title']); ?>
            </h1>
            <div class="quiz-info">
                <h3><i class="fas fa-info-circle"></i> Quiz Information</h3>
                <p><i class="fas fa-question-circle"></i> <strong>Total Questions:</strong> <?php echo count($questions); ?></p>
                <p><i class="fas fa-clock"></i> <strong>Time Limit:</strong> 20 seconds per question</p>
                <p><i class="fas fa-exclamation-triangle"></i> <strong>Note:</strong> Please don't refresh the page during the quiz</p>
            </div>
            <p>Please enter your student number to begin the quiz.</p>
            <input type="text" id="student-number" placeholder="Enter your student number" required>
            <button id="start-quiz-btn">
                <i class="fas fa-play"></i>
                Start Quiz
            </button>
            <p id="student-number-error" class="error-message"></p>
        </div>
        <div id="quiz-screen" style="display: <?php echo $display_quiz_screen ? 'block' : 'none'; ?>;">
            <div id="timer">
                <i class="fas fa-clock"></i>
                Time Left: <span id="time-left">20</span> seconds
            </div>
            <div class="progress-container">
                <div class="progress-bar" id="progress-bar" style="width: 0%"></div>
            </div>
            <div id="question-container">
                <h2 id="question-text"></h2>
                <div id="options-container"></div>
            </div>
            <div class="navigation-buttons">
                <button class="nav-btn" id="prev-btn" disabled>
                    <i class="fas fa-arrow-left"></i>
                    Previous
                </button>
                <button class="nav-btn" id="next-btn">
                    Next
                    <i class="fas fa-arrow-right"></i>
                </button>
            </div>
            <p id="quiz-message" class="error-message"></p>
        </div>
        <div id="end-screen" style="display: <?php echo $display_end_screen ? 'block' : 'none'; ?>;">
            <h2>
                <i class="fas fa-flag-checkered"></i>
                Quiz Ended!
            </h2>
            <p id="end-message"><?php echo htmlspecialchars($end_message); ?></p>
            <p>Thank you for participating in this quiz.</p>
            <?php if (isset($_SESSION['student_number'])): ?>
                <div class="session-info">
                    <h3><i class="fas fa-user"></i> Session Information</h3>
                    <p><i class="fas fa-id-card"></i> <strong>Student Number:</strong> <?php echo htmlspecialchars($_SESSION['student_number']); ?></p>
                    <p><i class="fas fa-fingerprint"></i> <strong>Session ID:</strong> <?php echo htmlspecialchars(session_id()); ?></p>
                    <?php if (isset($_SESSION['client_uuid'])): ?>
                        <p><i class="fas fa-desktop"></i> <strong>Browser ID:</strong> <?php echo htmlspecialchars($_SESSION['client_uuid']); ?></p>
                    <?php endif; ?>
                    <p><i class="fas fa-calendar"></i> <strong>Completed:</strong> <?php echo date('M j, Y g:i A'); ?></p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script>
        const initialQuizState = <?php echo json_encode($_SESSION['quiz_state'] ?? 'not_started'); ?>;
        const initialEndMessage = <?php echo json_encode($end_message); ?>;
        const quizId = <?php echo json_encode($quiz_id); ?>;
    </script>
    <script src="script.js"></script>
</body>
</html> 