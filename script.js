$(document).ready(function() {
    let currentQuestionIndex = 0;
    let studentNumber = '';
    let quizActive = false;
    let awayTimer = null;
    let questionTimer = null;
    const MAX_AWAY_TIME = 2000; // 2 seconds
    const MAX_QUESTION_TIME = 20000; // 20 seconds
    let answers = [];
    let clientUuid = getOrCreateClientUuid();
    let totalQuestions = 0;

    console.log("Script loaded. Initial Quiz State from PHP:", initialQuizState);
    console.log("Client UUID:", clientUuid);
    console.log("Quiz ID:", typeof quizId !== 'undefined' ? quizId : 'Not set');

    // --- Initial Page Load State Check from PHP ---
    if (initialQuizState === 'terminated') {
        $('#start-screen').hide();
        $('#quiz-screen').hide();
        $('#end-screen').show();
        $('#end-message').text(initialEndMessage);
        quizActive = false;
        console.log("Quiz already terminated by server on load. quizActive:", quizActive);
    } else {
        $('#start-screen').show();
        $('#quiz-screen').hide();
        $('#end-screen').hide();
        console.log("Quiz not terminated by server on load. quizActive:", quizActive);
    }

    // Function to get or create a unique client-side ID (UUID v4)
    function getOrCreateClientUuid() {
        let uuid = localStorage.getItem('clientUuid');
        if (!uuid) {
            uuid = 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
                var r = Math.random() * 16 | 0, v = c == 'x' ? r : (r & 0x3 | 0x8);
                return v.toString(16);
            });
            localStorage.setItem('clientUuid', uuid);
        }
        return uuid;
    }

    // --- Start Screen Logic ---
    $('#start-quiz-btn').on('click', function() {
        studentNumber = $('#student-number').val().trim();
        if (studentNumber) {
            $('#student-number-error').text('');
            
            // Show loading state
            const btn = $(this);
            const originalText = btn.html();
            btn.html('<span class="loading"></span> Starting Quiz...');
            btn.prop('disabled', true);
            
            // Determine the correct URL based on whether we're in a dynamic quiz or the original
            let startUrl = 'index.php?action=start_quiz_session';
            if (typeof quizId !== 'undefined' && quizId) {
                startUrl = 'quiz.php?action=start_quiz_session&id=' + quizId;
            }
            
            $.ajax({
                url: startUrl,
                type: 'POST',
                contentType: 'application/x-www-form-urlencoded',
                data: { studentNumber: studentNumber, clientUuid: clientUuid },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        $('#start-screen').hide();
                        $('#quiz-screen').show();
                        quizActive = true;
                        console.log("Quiz session started successfully. quizActive:", quizActive);

                        // --- FIX: Attach focus loss listener IMMEDIATELY here ---
                        startFocusLossProtection();

                        loadQuestion(currentQuestionIndex);
                    } else {
                        $('#student-number-error').text('Failed to start quiz session: ' + response.message);
                        console.error("Failed to start quiz session:", response.message);
                        btn.html(originalText);
                        btn.prop('disabled', false);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error starting quiz session:', status, error);
                    
                    // Handle specific error cases
                    if (xhr.status === 403) {
                        try {
                            const response = JSON.parse(xhr.responseText);
                            if (response.code === 'ALREADY_ATTEMPTED') {
                                $('#student-number-error').html('<i class="fas fa-exclamation-triangle"></i> ' + response.message + '<br><small>If you believe this is an error, please contact your instructor.</small>');
                            } else {
                                $('#student-number-error').text('Access denied: ' + (response.message || 'You are not authorized to take this quiz.'));
                            }
                        } catch (e) {
                            $('#student-number-error').text('Access denied: You are not authorized to take this quiz.');
                        }
                    } else {
                        $('#student-number-error').text('Network error or server issue. Please try again.');
                    }
                    
                    btn.html(originalText);
                    btn.prop('disabled', false);
                }
            });
        } else {
            $('#student-number-error').text('Please enter your student number.');
        }
    });

    // --- Load Question (AJAX call) ---
    function loadQuestion(index) {
        if (!quizActive) {
            console.log("loadQuestion called but quiz is not active. Exiting.");
            return;
        }

        // Determine the correct URL based on whether we're in a dynamic quiz or the original
        let questionUrl = 'index.php?action=get_question&question_index=' + index;
        if (typeof quizId !== 'undefined' && quizId) {
            questionUrl = 'quiz.php?action=get_question&question_index=' + index + '&id=' + quizId;
        }

        $.ajax({
            url: questionUrl,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success' && response.question) {
                    const question = response.question;
                    $('#question-text').text(question.question);
                    $('#options-container').empty();

                    $('#question-container').data('question-id', question.id);
                    $('#question-container').data('question-text', question.question);

                    question.options.forEach((option, idx) => {
                        const optionBtn = $('<button>')
                            .addClass('option-btn')
                            .text(option)
                            .data('option', option)
                            .on('click', function() {
                                selectOption($(this));
                            });
                        $('#options-container').append(optionBtn);
                    });

                    // Update progress bar
                    updateProgressBar();
                    
                    // Update navigation buttons
                    updateNavigationButtons();

                    startQuestionTimer();
                } else if (response.status === 'end') {
                    endQuiz('Quiz Completed Successfully!', 'Completed');
                } else if (response.status === 'terminated') {
                    endQuiz(response.message, 'Server Terminated');
                } else {
                    endQuiz('Error loading question: ' + (response.message || 'Unknown error.'), 'Question Load Error');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error fetching question:', status, error);
                endQuiz('Network error or server issue while fetching question.', 'Network Error');
            }
        });
    }

    // Update progress bar
    function updateProgressBar() {
        // Estimate total questions (you might want to get this from the server)
        if (totalQuestions === 0) {
            totalQuestions = 10; // Default estimate, adjust as needed
        }
        const progress = ((currentQuestionIndex + 1) / totalQuestions) * 100;
        $('#progress-bar').css('width', progress + '%');
    }

    // Update navigation buttons
    function updateNavigationButtons() {
        $('#prev-btn').prop('disabled', currentQuestionIndex === 0);
        $('#next-btn').prop('disabled', false);
    }

    // Navigation button handlers
    $('#prev-btn').on('click', function() {
        if (currentQuestionIndex > 0) {
            currentQuestionIndex--;
            loadQuestion(currentQuestionIndex);
        }
    });

    $('#next-btn').on('click', function() {
        // If no option is selected, show message
        if ($('.option-btn.selected').length === 0) {
            $('#quiz-message').text('Please select an answer before proceeding.').show();
            setTimeout(() => $('#quiz-message').fadeOut(), 3000);
            return;
        }
        
        currentQuestionIndex++;
        loadQuestion(currentQuestionIndex);
    });

    // --- Option Selection ---
    function selectOption(selectedButton) {
        if (!quizActive) return;

        $('.option-btn').removeClass('selected');
        selectedButton.addClass('selected');

        const selectedAnswer = selectedButton.data('option');
        const questionId = $('#question-container').data('question-id');
        const questionText = $('#question-container').data('question-text');

        clearTimeout(questionTimer);

        // Determine the correct URL for answer validation
        let validateUrl = 'index.php?action=validate_answer';
        if (typeof quizId !== 'undefined' && quizId) {
            validateUrl = 'quiz.php?action=validate_answer&id=' + quizId;
        }

        $.ajax({
            url: validateUrl,
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                questionId: questionId,
                selectedAnswer: selectedAnswer
            }),
            dataType: 'json',
            success: function(response) {
                if (response.status === 'terminated') {
                    endQuiz(response.message, 'Server Terminated During Answer');
                    return;
                }
                let isCorrect = false;
                if (response.status === 'validation_result') {
                    isCorrect = response.isCorrect;
                }

                answers.push({
                    studentNumber: studentNumber,
                    questionId: questionId,
                    questionText: questionText,
                    selectedAnswer: selectedAnswer,
                    isCorrect: isCorrect,
                    timestamp: new Date().toISOString()
                });

                currentQuestionIndex++;
                setTimeout(() => loadQuestion(currentQuestionIndex), 500);
            },
            error: function(xhr, status, error) {
                console.error('Error validating answer:', status, error);
                answers.push({
                    studentNumber: studentNumber,
                    questionId: questionId,
                    questionText: questionText,
                    selectedAnswer: selectedAnswer,
                    isCorrect: 'Validation Error (Client)',
                    timestamp: new Date().toISOString()
                });
                currentQuestionIndex++;
                setTimeout(() => loadQuestion(currentQuestionIndex), 500);
            }
        });
    }

    // --- Question Timer ---
    function startQuestionTimer() {
        let timeLeft = MAX_QUESTION_TIME / 1000;
        $('#time-left').text(timeLeft);

        if (questionTimer) {
            clearInterval(questionTimer);
        }

        questionTimer = setInterval(() => {
            timeLeft--;
            $('#time-left').text(timeLeft);
            if (timeLeft <= 0) {
                clearInterval(questionTimer);
                const questionId = $('#question-container').data('question-id');
                const questionText = $('#question-container').data('question-text');

                if (questionId) {
                    answers.push({
                        studentNumber: studentNumber,
                        questionId: questionId,
                        questionText: questionText,
                        selectedAnswer: 'Time Expired / No Answer',
                        isCorrect: false,
                        timestamp: new Date().toISOString()
                    });
                }
                currentQuestionIndex++;
                loadQuestion(currentQuestionIndex);
            }
        }, 1000);
    }

    // --- Unified Focus/Blur/Visibility Protection ---
    let hidden, visibilityChange;
    // Feature detection for visibility API
    if (typeof document.hidden !== "undefined") {
        hidden = "hidden";
        visibilityChange = "visibilitychange";
    } else if (typeof document.msHidden !== "undefined") {
        hidden = "msHidden";
        visibilityChange = "msvisibilitychange";
    } else if (typeof document.webkitHidden !== "undefined") {
        hidden = "webkitHidden";
        visibilityChange = "webkitvisibilitychange";
    }

    // Generic function to start the "away" timer
    function handleAwayStart() {
        console.log("Quiz window lost focus/visibility. Starting away timer. quizActive:", quizActive);
        if (!quizActive) {
            console.log("Quiz not active, ignoring away start.");
            return;
        }

        if (awayTimer) {
            // If a timer is already running (e.g., rapid alt-tab), clear and restart it
            clearTimeout(awayTimer);
            console.log("Existing away timer cleared.");
        }
        awayTimer = setTimeout(() => {
            if (quizActive) { // Double check if still active
                console.log("Away timer expired. Ending quiz.");
                endQuiz("Quiz ended due to leaving the quiz window for too long!", "Window Leave");
            }
        }, MAX_AWAY_TIME);
    }

    // Generic function to clear the "away" timer
    function handleAwayEnd() {
        console.log("Quiz window gained focus/visibility. Clearing away timer. quizActive:", quizActive);
        if (awayTimer) {
            clearTimeout(awayTimer);
            awayTimer = null;
            console.log("Away timer cleared successfully.");
        }
    }

    // Named functions for event listeners (needed for removeEventListener)
    const onVisibilityChange = function() {
        if (document[hidden]) {
            handleAwayStart();
        } else {
            handleAwayEnd();
        }
    };

    const onWindowBlur = function() {
        handleAwayStart();
    };

    const onWindowFocus = function() {
        handleAwayEnd();
    };

    function startFocusLossProtection() {
        // Attach visibilitychange listener
        if (typeof document.addEventListener !== "undefined" && visibilityChange) {
            document.addEventListener(visibilityChange, onVisibilityChange, false);
            console.log("Attached document visibilitychange listener.");
        } else {
            console.warn("Visibility Change API not fully supported.");
        }

        // Attach window blur/focus listeners
        if (typeof window.addEventListener !== "undefined") {
            window.addEventListener('blur', onWindowBlur, false);
            window.addEventListener('focus', onWindowFocus, false);
            console.log("Attached window blur/focus listeners.");
        } else {
            console.warn("Window blur/focus events not fully supported.");
        }
    }

    function stopFocusLossProtection() {
        if (typeof document.removeEventListener !== "undefined" && visibilityChange) {
            document.removeEventListener(visibilityChange, onVisibilityChange, false);
        }
        if (typeof window.removeEventListener !== "undefined") {
            window.removeEventListener('blur', onWindowBlur, false);
            window.removeEventListener('focus', onWindowFocus, false);
        }
    }

    // --- End Quiz ---
    function endQuiz(message, reason) {
        if (!quizActive) {
            console.log("endQuiz called but quiz is not active. Reason:", reason);
            return;
        }
        console.log("Ending quiz. Reason:", reason);

        quizActive = false;
        clearInterval(questionTimer);
        clearTimeout(awayTimer); // Clear away timer here too
        stopFocusLossProtection(); // Stop all listeners

        $('#quiz-screen').hide();
        $('#end-screen').show();
        $('#end-message').text(message);

        submitAnswersToServer(answers, reason);
    }

    // --- Submit Answers to Server (PHP) ---
    function submitAnswersToServer(data, reason) {
        // Determine the correct URL for ending the quiz session
        let endUrl = 'index.php?action=end_quiz_session';
        if (typeof quizId !== 'undefined' && quizId) {
            endUrl = 'quiz.php?action=end_quiz_session&id=' + quizId;
        }

        $.ajax({
            url: endUrl,
            type: 'POST',
            async: false,
            contentType: 'application/json',
            data: JSON.stringify({ answers: data, reason: reason, studentNumber: studentNumber }),
            success: function(response) {
                console.log('Quiz session ended and answers logged:', response);
            },
            error: function(xhr, status, error) {
                console.error('Error ending quiz session or saving answers:', status, error, xhr.responseText);
            }
        });
    }

    // --- Handle Page Unload / Refresh ---
    $(window).on('beforeunload', function() {
        if (quizActive) {
            console.log("Page unloading while quiz is active. Attempting to save answers.");
            submitAnswersToServer(answers, 'Page Unload/Refresh');
            return "Your quiz progress might be lost if you leave.";
        }
        console.log("Page unloading. Quiz not active or already handled.");
    });
});