<?php
// index.php

// Start the PHP session at the very beginning
session_start();

// --- Configuration & Data Storage (Server-Side Only) ---
$questions_file_path = __DIR__ . '/edgeworth.json';
$quiz_answers_log_file = 'quiz_answers.log'; // For detailed logs
$quiz_scores_csv_file = 'quiz_scores.csv'; // For score summary

// Function to get the user's IP address
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
$user_ip = get_client_ip();
$session_id = session_id(); // Get PHP session ID
$user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown User Agent'; // Get User Agent


// Function to load questions securely
function load_questions($file_path) {
    if (!file_exists($file_path)) {
        error_log("Error: Questions file not found at " . $file_path);
        return false;
    }
    $json_content = file_get_contents($file_path);
    if ($json_content === false) {
        error_log("Error: Could not read questions file at " . $file_path);
        return false;
    }
    $questions = json_decode($json_content, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("Error: Invalid JSON in questions file: " . json_last_error_msg());
        return false;
    }
    return $questions;
}

// 2. --- Handle AJAX Requests (API Endpoints) ---
if (isset($_GET['action'])) {
    header('Content-Type: application/json');

    // Load questions for any AJAX action that needs them
    $all_questions_with_answers = load_questions($questions_file_path);
    if ($all_questions_with_answers === false) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Server error: Could not load questions.']);
        exit;
    }

    if ($_GET['action'] === 'start_quiz_session') {
        $student_number = $_POST['studentNumber'] ?? '';
        $client_uuid = $_POST['clientUuid'] ?? '';

        if (empty($student_number)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Student number is required.']);
            exit;
        }

        // Set quiz state in session
        $_SESSION['quiz_state'] = 'active';
        $_SESSION['student_number'] = $student_number;
        $_SESSION['client_uuid'] = $client_uuid;
        $_SESSION['start_time'] = time();
        // Removed $_SESSION['last_activity_time'] as inactivity timer is cancelled
        $_SESSION['user_ip'] = $user_ip;
        $_SESSION['session_id'] = $session_id;
        $_SESSION['user_agent'] = $user_agent;

        echo json_encode(['status' => 'success', 'message' => 'Quiz session started.']);
        exit;
    }

    if ($_GET['action'] === 'get_question') {
        // Before sending question, check if quiz was terminated
        if (isset($_SESSION['quiz_state']) && $_SESSION['quiz_state'] !== 'active') {
            echo json_encode(['status' => 'terminated', 'message' => 'Your quiz session has ended. Reason: ' . ($_SESSION['quiz_terminated_reason'] ?? 'Unknown')]);
            exit;
        }
        // Removed $_SESSION['last_activity_time'] update

        $question_index = isset($_GET['question_index']) ? (int)$_GET['question_index'] : 0;

        if (isset($all_questions_with_answers[$question_index])) {
            $question_data = $all_questions_with_answers[$question_index];
            unset($question_data['correct_answer']);
            echo json_encode(['status' => 'success', 'question' => $question_data]);
        } else {
            echo json_encode(['status' => 'end', 'message' => 'No more questions.']);
        }
        exit;
    }

    if ($_GET['action'] === 'validate_answer') {
        // Before validating answer, check if quiz was terminated
        if (isset($_SESSION['quiz_state']) && $_SESSION['quiz_state'] !== 'active') {
            echo json_encode(['status' => 'terminated', 'message' => 'Your quiz session has ended.']);
            exit;
        }
        // Removed $_SESSION['last_activity_time'] update

        $json_input = file_get_contents('php://input');
        $input_data = json_decode($json_input, true);

        $question_id = $input_data['questionId'] ?? null;
        $selected_answer = $input_data['selectedAnswer'] ?? null;

        $is_correct = false;
        $correct_answer_text = null;

        if ($question_id !== null && $selected_answer !== null) {
            foreach ($all_questions_with_answers as $q) {
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

        // Set quiz state in session to terminated
        $_SESSION['quiz_state'] = 'terminated';
        $_SESSION['quiz_terminated_reason'] = $termination_reason;

        // Log answers to quiz_answers.log (detailed backup)
        $student_number = $_SESSION['student_number'] ?? 'N/A';
        $client_uuid = $_SESSION['client_uuid'] ?? 'N/A';
        $session_id_log = $_SESSION['session_id'] ?? 'N/A';
        $user_ip_log = $_SESSION['user_ip'] ?? 'N/A';
        $user_agent_log = $_SESSION['user_agent'] ?? 'N/A';

        $log_entry = "--- Quiz End | Student: " . $student_number . " | Session ID: " . $session_id_log . " | Client UUID: " . $client_uuid . " | IP: " . $user_ip_log . " | User Agent: " . $user_agent_log . " | Reason: " . $termination_reason . " | Time: " . date('Y-m-d H:i:s') . " ---\n";

        $correct_answers_count = 0; // Initialize score for CSV

        foreach ($answers_to_log as $answer) {
            $actual_is_correct = false;
            foreach ($all_questions_with_answers as $q) {
                if (isset($answer['questionId']) && $q['id'] == $answer['questionId']) {
                    if (isset($answer['selectedAnswer']) && $answer['selectedAnswer'] !== 'Time Expired / No Answer' && $answer['selectedAnswer'] === $q['correct_answer']) {
                        $actual_is_correct = true;
                        $correct_answers_count++; // Increment score if correct
                    }
                    break;
                }
            }
            $answer['serverValidatedCorrect'] = $actual_is_correct;
            $log_entry .= json_encode($answer) . "\n";
        }
        $log_entry .= "---------------------------------------------------------\n\n";

        if (file_put_contents($quiz_answers_log_file, $log_entry, FILE_APPEND | LOCK_EX) === false) {
            error_log("Failed to write to quiz_answers.log. Check file permissions.");
        }


        // --- New: Log score to quiz_scores.csv ---
        $student_number_for_csv = str_replace(',', '', $student_number); // Remove commas to prevent CSV issues
        $score_data_row = $student_number_for_csv . ',' . $correct_answers_count . "\n";

        // Check if CSV file exists and create headers if not
        if (!file_exists($quiz_scores_csv_file)) {
            $header_row = "Student,QuizScore\n";
            if (file_put_contents($quiz_scores_csv_file, $header_row) === false) {
                error_log("Failed to create quiz_scores.csv with headers. Check file permissions.");
            }
        }
        // Append score data to CSV
        if (file_put_contents($quiz_scores_csv_file, $score_data_row, FILE_APPEND | LOCK_EX) === false) {
            error_log("Failed to append score to quiz_scores.csv. Check file permissions.");
        }
        // --- End New CSV Logging ---


        echo json_encode(['status' => 'success', 'message' => 'Quiz session ended and answers logged.']);
        exit;
    }
}

// 3. --- Render HTML (Initial Page Load or Terminated State) ---
$display_start_screen = true;
$display_quiz_screen = false;
$display_end_screen = false;
$end_message = '';

if (isset($_SESSION['quiz_state']) && $_SESSION['quiz_state'] === 'terminated') {
    $display_end_screen = true;
    $display_start_screen = false;
    $end_message = 'Your previous quiz session ended. Reason: ' . ($_SESSION['quiz_terminated_reason'] ?? 'Unknown');
} else if (isset($_SESSION['quiz_state']) && $_SESSION['quiz_state'] === 'active') {
    // Revert to strict refresh termination
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
    <title>Multi-Choice Quiz</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div id="start-screen" style="display: <?php echo $display_start_screen ? 'block' : 'none'; ?>;">
            <h1>Welcome to the Quiz!</h1>
            <p>Please enter your student number to begin.</p>
            <input type="text" id="student-number" placeholder="Your Student Number" required>
            <button id="start-quiz-btn">Start Quiz</button>
            <p id="student-number-error" class="error-message"></p>
        </div>

        <div id="quiz-screen" style="display: <?php echo $display_quiz_screen ? 'block' : 'none'; ?>;">
            <div id="timer">Time Left: <span id="time-left">20</span> seconds</div>
            <div id="question-container">
                <h2 id="question-text"></h2>
                <div id="options-container">
                    </div>
            </div>
            <p id="quiz-message" class="error-message"></p>
        </div>

        <div id="end-screen" style="display: <?php echo $display_end_screen ? 'block' : 'none'; ?>;">
            <h2>Quiz Ended!</h2>
            <p id="end-message"><?php echo htmlspecialchars($end_message); ?></p>
            <p>Thank you for participating.</p>
            <?php if (isset($_SESSION['student_number'])): ?>
                <p>Student Number: <?php echo htmlspecialchars($_SESSION['student_number']); ?></p>
                <p>Session ID: <?php echo htmlspecialchars(session_id()); ?></p>
                <?php if (isset($_SESSION['client_uuid'])): ?>
                    <p>Browser ID: <?php echo htmlspecialchars($_SESSION['client_uuid']); ?></p>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script>
        // Pass initial state from PHP to JavaScript
        const initialQuizState = <?php echo json_encode($_SESSION['quiz_state'] ?? 'not_started'); ?>;
        const initialEndMessage = <?php echo json_encode($end_message); ?>;
    </script>
    <script src="script.js"></script>
</body>
</html>