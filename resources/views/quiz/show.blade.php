<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $quiz->title }} - Quiz</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        .container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 3rem;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 800px;
            text-align: center;
        }

        .quiz-header {
            margin-bottom: 2rem;
        }

        .quiz-header h1 {
            font-size: 2.5rem;
            color: #333;
            margin-bottom: 1rem;
            font-weight: 300;
        }

        .quiz-header i {
            font-size: 3rem;
            color: #667eea;
            margin-bottom: 1rem;
        }

        .quiz-info {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            text-align: left;
        }

        .quiz-info h3 {
            color: #333;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .quiz-info p {
            color: #666;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .form-group {
            margin-bottom: 2rem;
            text-align: left;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #333;
            font-weight: 600;
        }

        .form-group input {
            width: 100%;
            padding: 1rem;
            border: 2px solid #e1e5e9;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-group input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .btn {
            padding: 1rem 2rem;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }

        .btn-primary:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1rem;
            border-left: 4px solid #dc3545;
            display: none;
        }

        .quiz-screen {
            display: none;
        }

        .quiz-screen.show {
            display: block;
        }

        .timer {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
            color: white;
            padding: 1rem 2rem;
            border-radius: 10px;
            margin-bottom: 2rem;
            font-size: 1.2rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .progress-container {
            background: #e1e5e9;
            border-radius: 10px;
            height: 10px;
            margin-bottom: 2rem;
            overflow: hidden;
        }

        .progress-bar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            height: 100%;
            transition: width 0.3s ease;
            width: 0%;
        }

        .question-container {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
        }

        .question-text {
            font-size: 1.3rem;
            color: #333;
            margin-bottom: 2rem;
            font-weight: 600;
        }

        .options-container {
            display: grid;
            gap: 1rem;
        }

        .option {
            background: white;
            border: 2px solid #e1e5e9;
            border-radius: 10px;
            padding: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: left;
            font-size: 1rem;
        }

        .option:hover {
            border-color: #667eea;
            background: rgba(102, 126, 234, 0.05);
        }

        .option.selected {
            border-color: #667eea;
            background: rgba(102, 126, 234, 0.1);
        }

        .option.correct {
            border-color: #28a745;
            background: rgba(40, 167, 69, 0.1);
        }

        .option.incorrect {
            border-color: #dc3545;
            background: rgba(220, 53, 69, 0.1);
        }

        .navigation-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-bottom: 2rem;
        }

        .nav-btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .nav-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .nav-btn:not(:disabled):hover {
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background: #5a6268;
        }

        .btn-success {
            background: #28a745;
            color: white;
        }

        .btn-success:hover {
            background: #218838;
        }

        .end-screen {
            display: none;
        }

        .end-screen.show {
            display: block;
        }

        .session-info {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 2rem;
            margin-top: 2rem;
            text-align: left;
        }

        .session-info h3 {
            color: #333;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .session-info p {
            color: #666;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .alert {
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1rem;
        }

        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }

        .alert-warning {
            background: #fff3cd;
            color: #856404;
            border-left: 4px solid #ffc107;
        }

        @media (max-width: 768px) {
            body {
                padding: 1rem;
            }

            .container {
                padding: 2rem 1rem;
            }

            .quiz-header h1 {
                font-size: 2rem;
            }

            .navigation-buttons {
                flex-direction: column;
            }

            .options-container {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div id="start-screen">
            <div class="quiz-header">
                <i class="fas fa-graduation-cap"></i>
                <h1>{{ $quiz->title }}</h1>
            </div>
            
            <div class="quiz-info">
                <h3><i class="fas fa-info-circle"></i> Quiz Information</h3>
                <p><i class="fas fa-question-circle"></i> <strong>Total Questions:</strong> {{ count($questions) }}</p>
                <p><i class="fas fa-clock"></i> <strong>Time Limit:</strong> 20 seconds per question</p>
                <p><i class="fas fa-exclamation-triangle"></i> <strong>Note:</strong> Please don't refresh the page during the quiz</p>
            </div>
            
            <p>Please enter your student number to begin the quiz.</p>
            
            <div class="form-group">
                <label for="student-number">Student Number</label>
                <input type="text" id="student-number" placeholder="Enter your student number" required>
            </div>
            
            <button id="start-quiz-btn" class="btn btn-primary">
                <i class="fas fa-play"></i>
                Start Quiz
            </button>
            
            <p id="student-number-error" class="error-message"></p>
        </div>

        <div id="quiz-screen" class="quiz-screen">
            <div class="timer">
                <i class="fas fa-clock"></i>
                Time Left: <span id="time-left">20</span> seconds
            </div>
            
            <div class="progress-container">
                <div class="progress-bar" id="progress-bar" style="width: 0%"></div>
            </div>
            
            <div class="question-container">
                <h2 id="question-text" class="question-text"></h2>
                <div id="options-container" class="options-container"></div>
            </div>
            
            <div class="navigation-buttons">
                <button class="nav-btn btn-secondary" id="prev-btn" disabled>
                    <i class="fas fa-arrow-left"></i>
                    Previous
                </button>
                <button class="nav-btn btn-success" id="next-btn">
                    Next
                    <i class="fas fa-arrow-right"></i>
                </button>
            </div>
            
            <p id="quiz-message" class="error-message"></p>
        </div>

        <div id="end-screen" class="end-screen">
            <div class="quiz-header">
                <i class="fas fa-flag-checkered"></i>
                <h1>Quiz Ended!</h1>
            </div>
            
            <p id="end-message"></p>
            <p>Thank you for participating in this quiz.</p>
            
            <div class="session-info">
                <h3><i class="fas fa-user"></i> Session Information</h3>
                <p><i class="fas fa-id-card"></i> <strong>Student Number:</strong> <span id="session-student-number"></span></p>
                <p><i class="fas fa-fingerprint"></i> <strong>Session ID:</strong> <span id="session-id"></span></p>
                <p><i class="fas fa-desktop"></i> <strong>Browser ID:</strong> <span id="browser-id"></span></p>
                <p><i class="fas fa-calendar"></i> <strong>Completed:</strong> <span id="completion-time"></span></p>
            </div>
        </div>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script>
        const quizId = "{{ $quiz->quiz_id }}";
        const totalQuestions = {{ count($questions) }};
        let currentQuestionIndex = 0;
        let answers = [];
        let timer = null;
        let timeLeft = 20;
        let clientUuid = generateUUID();

        function generateUUID() {
            return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
                const r = Math.random() * 16 | 0;
                const v = c == 'x' ? r : (r & 0x3 | 0x8);
                return v.toString(16);
            });
        }

        function showScreen(screenId) {
            $('.container > div').hide();
            $(`#${screenId}`).show();
        }

        function showError(message) {
            $('#student-number-error').text(message).show();
        }

        function hideError() {
            $('#student-number-error').hide();
        }

        function showQuizMessage(message, type = 'error') {
            const messageEl = $('#quiz-message');
            messageEl.removeClass('alert-danger alert-warning').addClass(`alert-${type}`);
            messageEl.text(message).show();
        }

        function hideQuizMessage() {
            $('#quiz-message').hide();
        }

        function updateProgress() {
            const progress = ((currentQuestionIndex + 1) / totalQuestions) * 100;
            $('#progress-bar').css('width', progress + '%');
        }

        function startTimer() {
            timeLeft = 20;
            $('#time-left').text(timeLeft);
            
            timer = setInterval(() => {
                timeLeft--;
                $('#time-left').text(timeLeft);
                
                if (timeLeft <= 0) {
                    clearInterval(timer);
                    handleTimeExpired();
                }
            }, 1000);
        }

        function clearTimer() {
            if (timer) {
                clearInterval(timer);
                timer = null;
            }
        }

        function handleTimeExpired() {
            const currentAnswer = {
                questionId: currentQuestionIndex,
                selectedAnswer: 'Time Expired / No Answer',
                timestamp: new Date().toISOString()
            };
            
            answers[currentQuestionIndex] = currentAnswer;
            
            if (currentQuestionIndex < totalQuestions - 1) {
                currentQuestionIndex++;
                loadQuestion();
            } else {
                endQuiz('Time expired on final question');
            }
        }

        function loadQuestion() {
            clearTimer();
            hideQuizMessage();
            
            $.ajax({
                url: `/quiz/${quizId}/question`,
                method: 'GET',
                data: { question_index: currentQuestionIndex },
                success: function(response) {
                    if (response.status === 'success') {
                        displayQuestion(response.question);
                        updateProgress();
                        updateNavigationButtons();
                        startTimer();
                    } else if (response.status === 'end') {
                        endQuiz('Completed');
                    } else if (response.status === 'terminated') {
                        showEndScreen(response.message);
                    }
                },
                error: function() {
                    showQuizMessage('Failed to load question. Please try again.');
                }
            });
        }

        function displayQuestion(question) {
            $('#question-text').text(question.question);
            
            const optionsContainer = $('#options-container');
            optionsContainer.empty();
            
            question.options.forEach((option, index) => {
                const optionEl = $(`<div class="option" data-option="${option}">${option}</div>`);
                
                if (answers[currentQuestionIndex] && answers[currentQuestionIndex].selectedAnswer === option) {
                    optionEl.addClass('selected');
                }
                
                optionEl.click(function() {
                    $('.option').removeClass('selected');
                    $(this).addClass('selected');
                    answers[currentQuestionIndex] = {
                        questionId: question.id,
                        selectedAnswer: option,
                        timestamp: new Date().toISOString()
                    };
                });
                
                optionsContainer.append(optionEl);
            });
        }

        function updateNavigationButtons() {
            $('#prev-btn').prop('disabled', currentQuestionIndex === 0);
            $('#next-btn').text(currentQuestionIndex === totalQuestions - 1 ? 'Finish' : 'Next');
        }

        function validateAnswer() {
            return new Promise((resolve, reject) => {
                const currentAnswer = answers[currentQuestionIndex];
                if (!currentAnswer) {
                    reject('Please select an answer');
                    return;
                }

                $.ajax({
                    url: `/quiz/${quizId}/validate`,
                    method: 'POST',
                    data: JSON.stringify({
                        questionId: currentAnswer.questionId,
                        selectedAnswer: currentAnswer.selectedAnswer
                    }),
                    contentType: 'application/json',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.status === 'validation_result') {
                            resolve(response);
                        } else if (response.status === 'terminated') {
                            reject('Quiz session terminated');
                        }
                    },
                    error: function() {
                        reject('Failed to validate answer');
                    }
                });
            });
        }

        function endQuiz(reason) {
            clearTimer();
            
            $.ajax({
                url: `/quiz/${quizId}/end`,
                method: 'POST',
                data: JSON.stringify({
                    reason: reason,
                    answers: answers
                }),
                contentType: 'application/json',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.status === 'success') {
                        showEndScreen('Quiz completed successfully');
                    } else {
                        showEndScreen('Quiz ended with errors');
                    }
                },
                error: function() {
                    showEndScreen('Failed to submit quiz results');
                }
            });
        }

        function showEndScreen(message) {
            $('#end-message').text(message);
            $('#session-student-number').text($('#student-number').val());
            $('#session-id').text('{{ session()->getId() }}');
            $('#browser-id').text(clientUuid);
            $('#completion-time').text(new Date().toLocaleString());
            showScreen('end-screen');
        }

        // Event handlers
        $('#start-quiz-btn').click(function() {
            const studentNumber = $('#student-number').val().trim();
            
            if (!studentNumber) {
                showError('Please enter your student number');
                return;
            }
            
            hideError();
            
            $.ajax({
                url: `/quiz/${quizId}/start`,
                method: 'POST',
                data: {
                    studentNumber: studentNumber,
                    clientUuid: clientUuid
                },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.status === 'success') {
                        showScreen('quiz-screen');
                        loadQuestion();
                    } else {
                        showError(response.message);
                    }
                },
                error: function(xhr) {
                    if (xhr.status === 403 && xhr.responseJSON && xhr.responseJSON.code === 'ALREADY_ATTEMPTED') {
                        showError('You have already attempted this quiz. Each student can only take a quiz once.');
                    } else {
                        showError('Failed to start quiz. Please try again.');
                    }
                }
            });
        });

        $('#next-btn').click(function() {
            if (currentQuestionIndex < totalQuestions - 1) {
                currentQuestionIndex++;
                loadQuestion();
            } else {
                endQuiz('Completed');
            }
        });

        $('#prev-btn').click(function() {
            if (currentQuestionIndex > 0) {
                currentQuestionIndex--;
                loadQuestion();
            }
        });

        // Handle page refresh/close
        window.addEventListener('beforeunload', function(e) {
            if (currentQuestionIndex > 0) {
                e.preventDefault();
                e.returnValue = 'Are you sure you want to leave? Your quiz progress will be lost.';
            }
        });

        // Handle back button
        window.addEventListener('popstate', function(e) {
            if (currentQuestionIndex > 0) {
                e.preventDefault();
                history.pushState(null, null, window.location.href);
                showQuizMessage('Please use the navigation buttons within the quiz.', 'warning');
            }
        });
    </script>
</body>
</html> 