<?php
require_once 'auth.php';

// Redirect if not logged in
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create New Quiz</title>
    <link rel="stylesheet" href="create-quiz.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="create-quiz-container">
        <div class="page-header">
            <h1 class="page-title">
                <i class="fas fa-plus-circle"></i>
                Create New Quiz
            </h1>
            <p class="page-subtitle">Upload a JSON file to create an interactive quiz</p>
        </div>
        
        <div class="user-welcome">
            <h3>
                <i class="fas fa-user-circle"></i>
                Welcome, <?php echo htmlspecialchars($user['username']); ?>!
            </h3>
            <p>Ready to create an amazing quiz for your students?</p>
        </div>
        
        <div class="json-template">
<strong>📋 JSON Template:</strong>
[
    {
        "id": 1,
        "question": "What is the capital of France?",
        "options": ["Berlin", "Madrid", "Paris", "Rome"],
        "correct_answer": "Paris"
    },
    {
        "id": 2,
        "question": "Which planet is known as the Red Planet?",
        "options": ["Earth", "Mars", "Jupiter", "Venus"],
        "correct_answer": "Mars"
    }
]
        </div>
        
        <div class="drag-drop-area" id="dragDropArea">
            <h3>
                <i class="fas fa-cloud-upload-alt"></i>
                Drag & Drop your JSON file here
            </h3>
            <p>or click the button below to browse files</p>
            <button class="upload-btn" onclick="document.getElementById('fileInput').click()">
                <i class="fas fa-folder-open"></i>
                Choose File
            </button>
            <input type="file" id="fileInput" class="file-input" accept=".json">
        </div>
        
        <div class="quiz-preview" id="quizPreview">
            <h4>
                <i class="fas fa-eye"></i>
                Quiz Preview
            </h4>
            <div id="previewContent"></div>
            <button class="create-btn" id="createBtn" disabled>
                <i class="fas fa-magic"></i>
                Create Quiz
            </button>
        </div>
        
        <div id="message"></div>
        
        <a href="dashboard.php" class="back-btn">
            <i class="fas fa-arrow-left"></i>
            Back to Dashboard
        </a>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script>
        $(document).ready(function() {
            const dragDropArea = document.getElementById('dragDropArea');
            const fileInput = document.getElementById('fileInput');
            const quizPreview = document.getElementById('quizPreview');
            const previewContent = document.getElementById('previewContent');
            const createBtn = document.getElementById('createBtn');
            
            // Drag and drop functionality
            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                dragDropArea.addEventListener(eventName, preventDefaults, false);
            });
            
            function preventDefaults(e) {
                e.preventDefault();
                e.stopPropagation();
            }
            
            ['dragenter', 'dragover'].forEach(eventName => {
                dragDropArea.addEventListener(eventName, highlight, false);
            });
            
            ['dragleave', 'drop'].forEach(eventName => {
                dragDropArea.addEventListener(eventName, unhighlight, false);
            });
            
            function highlight(e) {
                dragDropArea.classList.add('dragover');
            }
            
            function unhighlight(e) {
                dragDropArea.classList.remove('dragover');
            }
            
            dragDropArea.addEventListener('drop', handleDrop, false);
            
            function handleDrop(e) {
                const dt = e.dataTransfer;
                const files = dt.files;
                handleFiles(files);
            }
            
            fileInput.addEventListener('change', function() {
                handleFiles(this.files);
            });
            
            function handleFiles(files) {
                if (files.length > 0) {
                    const file = files[0];
                    if (file.type === 'application/json' || file.name.endsWith('.json')) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            try {
                                const quizData = JSON.parse(e.target.result);
                                displayPreview(quizData);
                            } catch (error) {
                                showMessage('Invalid JSON file. Please check the format.', 'error');
                            }
                        };
                        reader.readAsText(file);
                    } else {
                        showMessage('Please select a valid JSON file.', 'error');
                    }
                }
            }
            
            function displayPreview(quizData) {
                if (!Array.isArray(quizData) || quizData.length === 0) {
                    showMessage('Invalid quiz format. Please use the provided template.', 'error');
                    return;
                }
                
                let previewHtml = '';
                quizData.forEach((question, index) => {
                    if (question.question && question.options && question.correct_answer) {
                        previewHtml += `
                            <div class="question-item">
                                <div class="question-text">Question ${index + 1}: ${question.question}</div>
                                <ul class="options-list">
                        `;
                        question.options.forEach(option => {
                            const isCorrect = option === question.correct_answer;
                            previewHtml += `<li class="${isCorrect ? 'correct' : ''}">${option}</li>`;
                        });
                        previewHtml += '</ul></div>';
                    }
                });
                
                if (previewHtml) {
                    previewContent.innerHTML = previewHtml;
                    quizPreview.style.display = 'block';
                    createBtn.disabled = false;
                    showMessage('Quiz preview loaded successfully!', 'success');
                } else {
                    showMessage('No valid questions found in the JSON file.', 'error');
                }
            }
            
            createBtn.addEventListener('click', function() {
                const btn = this;
                const originalText = btn.innerHTML;
                
                btn.innerHTML = '<span class="loading-spinner"></span>Creating Quiz...';
                btn.disabled = true;
                
                // Get the file data
                const file = fileInput.files[0];
                if (!file) {
                    showMessage('Please select a JSON file first.', 'error');
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                    return;
                }
                
                // Read the file content and send as JSON data
                const reader = new FileReader();
                reader.onload = function(e) {
                    try {
                        const quizData = JSON.parse(e.target.result);
                        
                        // Send the quiz data as JSON string
                        $.ajax({
                            url: 'quiz_manager.php',
                            type: 'POST',
                            data: {
                                action: 'create_quiz',
                                quiz_data: JSON.stringify(quizData)
                            },
                            success: function(response) {
                                if (response.success) {
                                    showMessage('Quiz created successfully! Redirecting to dashboard...', 'success');
                                    setTimeout(function() {
                                        window.location.href = 'dashboard.php';
                                    }, 2000);
                                } else {
                                    showMessage('Error creating quiz: ' + response.message, 'error');
                                    btn.innerHTML = originalText;
                                    btn.disabled = false;
                                }
                            },
                            error: function() {
                                showMessage('Network error. Please try again.', 'error');
                                btn.innerHTML = originalText;
                                btn.disabled = false;
                            }
                        });
                    } catch (error) {
                        showMessage('Invalid JSON file. Please check the format.', 'error');
                        btn.innerHTML = originalText;
                        btn.disabled = false;
                    }
                };
                
                reader.onerror = function() {
                    showMessage('Error reading file. Please try again.', 'error');
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                };
                
                reader.readAsText(file);
            });
            
            function showMessage(message, type) {
                const messageDiv = $('#message');
                messageDiv.removeClass('message success error');
                messageDiv.addClass('message ' + type);
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
</body>
</html> 