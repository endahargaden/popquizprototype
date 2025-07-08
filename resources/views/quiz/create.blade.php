@extends('layouts.app')

@section('title', 'Create Quiz - Quiz System')

@section('styles')
<style>
    body {
        margin: 0;
        padding: 0;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        min-height: 100vh;
    }

    .navbar {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        padding: 1rem 0;
        position: sticky;
        top: 0;
        z-index: 1000;
    }

    .nav-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 2rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .nav-brand {
        font-size: 1.5rem;
        font-weight: bold;
        color: #333;
        text-decoration: none;
    }

    .nav-brand i {
        color: #667eea;
        margin-right: 0.5rem;
    }

    .nav-menu {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .nav-link {
        color: #333;
        text-decoration: none;
        padding: 0.5rem 1rem;
        border-radius: 8px;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .nav-link:hover {
        background: rgba(102, 126, 234, 0.1);
        color: #667eea;
    }

    .logout-btn {
        background: none;
        border: none;
        cursor: pointer;
        font-size: 1rem;
    }

    .main-content {
        max-width: 800px;
        margin: 0 auto;
        padding: 2rem;
    }

    .create-header {
        text-align: center;
        margin-bottom: 3rem;
        color: white;
    }

    .create-header h1 {
        font-size: 3rem;
        margin: 0 0 1rem 0;
        font-weight: 300;
    }

    .create-header p {
        font-size: 1.2rem;
        margin: 0;
        opacity: 0.9;
    }

    .create-container {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border-radius: 20px;
        padding: 3rem;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
    }

    .upload-section {
        text-align: center;
        margin-bottom: 2rem;
    }

    .upload-area {
        border: 3px dashed #667eea;
        border-radius: 20px;
        padding: 3rem;
        background: rgba(102, 126, 234, 0.05);
        transition: all 0.3s ease;
        cursor: pointer;
        margin-bottom: 2rem;
    }

    .upload-area:hover {
        border-color: #764ba2;
        background: rgba(102, 126, 234, 0.1);
    }

    .upload-area.dragover {
        border-color: #28a745;
        background: rgba(40, 167, 69, 0.1);
    }

    .upload-icon {
        font-size: 4rem;
        color: #667eea;
        margin-bottom: 1rem;
    }

    .upload-text {
        font-size: 1.2rem;
        color: #333;
        margin-bottom: 0.5rem;
    }

    .upload-subtext {
        color: #666;
        font-size: 1rem;
    }

    .file-input {
        display: none;
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

    .btn-secondary {
        background: #6c757d;
        color: white;
    }

    .btn-secondary:hover {
        background: #5a6268;
        transform: translateY(-2px);
    }

    .preview-section {
        margin-top: 2rem;
        padding: 2rem;
        background: #f8f9fa;
        border-radius: 15px;
        display: none;
    }

    .preview-section.show {
        display: block;
    }

    .preview-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
    }

    .preview-title {
        font-size: 1.5rem;
        font-weight: 600;
        color: #333;
        margin: 0;
    }

    .preview-count {
        background: #667eea;
        color: white;
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.9rem;
        font-weight: 600;
    }

    .question-preview {
        background: white;
        border-radius: 10px;
        padding: 1.5rem;
        margin-bottom: 1rem;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    .question-text {
        font-size: 1.1rem;
        font-weight: 600;
        color: #333;
        margin-bottom: 1rem;
    }

    .options-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .option-item {
        padding: 0.75rem;
        margin-bottom: 0.5rem;
        background: #f8f9fa;
        border-radius: 8px;
        border-left: 4px solid #dee2e6;
    }

    .option-item.correct {
        background: #d4edda;
        border-left-color: #28a745;
        color: #155724;
    }

    .alert {
        padding: 1rem;
        border-radius: 10px;
        margin-bottom: 1rem;
        display: none;
    }

    .alert-success {
        background: #d4edda;
        color: #155724;
        border-left: 4px solid #28a745;
    }

    .alert-danger {
        background: #f8d7da;
        color: #721c24;
        border-left: 4px solid #dc3545;
    }

    .loading {
        display: none;
        text-align: center;
        margin: 2rem 0;
    }

    .spinner {
        border: 4px solid #f3f3f3;
        border-top: 4px solid #667eea;
        border-radius: 50%;
        width: 40px;
        height: 40px;
        animation: spin 1s linear infinite;
        margin: 0 auto 1rem;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    .template-section {
        margin-top: 2rem;
        padding: 2rem;
        background: #f8f9fa;
        border-radius: 15px;
    }

    .template-title {
        font-size: 1.3rem;
        font-weight: 600;
        color: #333;
        margin-bottom: 1rem;
        text-align: center;
    }

    .template-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
    }

    .template-card {
        background: white;
        border-radius: 10px;
        padding: 1.5rem;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
        border: 2px solid transparent;
    }

    .template-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        border-color: #667eea;
    }

    .template-icon {
        font-size: 2rem;
        color: #667eea;
        margin-bottom: 1rem;
    }

    .template-name {
        font-weight: 600;
        color: #333;
        margin-bottom: 0.5rem;
    }

    .template-desc {
        font-size: 0.9rem;
        color: #666;
    }

    @media (max-width: 768px) {
        .nav-container {
            flex-direction: column;
            gap: 1rem;
        }

        .nav-menu {
            flex-wrap: wrap;
            justify-content: center;
        }

        .main-content {
            padding: 1rem;
        }

        .create-header h1 {
            font-size: 2rem;
        }

        .create-container {
            padding: 2rem 1rem;
        }

        .upload-area {
            padding: 2rem 1rem;
        }

        .template-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endsection

@section('content')
<div class="create-header">
    <h1>Create New Quiz</h1>
    <p>Upload a JSON file or use a template to create your quiz</p>
</div>

<div class="create-container">
    <div class="alert alert-success" id="successAlert"></div>
    <div class="alert alert-danger" id="errorAlert"></div>

    <div class="upload-section">
        <div class="upload-area" id="uploadArea">
            <div class="upload-icon">
                <i class="fas fa-cloud-upload-alt"></i>
            </div>
            <div class="upload-text">Drag & Drop your JSON file here</div>
            <div class="upload-subtext">or click to browse</div>
            <input type="file" id="fileInput" class="file-input" accept=".json">
        </div>
        
        <button class="btn btn-primary" id="createQuizBtn" disabled>
            <i class="fas fa-plus"></i>
            Create Quiz
        </button>
    </div>

    <div class="loading" id="loading">
        <div class="spinner"></div>
        <p>Creating quiz...</p>
    </div>

    <div class="preview-section" id="previewSection">
        <div class="preview-header">
            <h3 class="preview-title">Quiz Preview</h3>
            <span class="preview-count" id="questionCount">0 questions</span>
        </div>
        <div id="previewContent"></div>
    </div>

    <div class="template-section">
        <h3 class="template-title">Or use a template</h3>
        <div class="template-grid">
            <div class="template-card" data-template="math">
                <div class="template-icon">
                    <i class="fas fa-calculator"></i>
                </div>
                <div class="template-name">Math Quiz</div>
                <div class="template-desc">Basic mathematics questions</div>
            </div>
            <div class="template-card" data-template="science">
                <div class="template-icon">
                    <i class="fas fa-flask"></i>
                </div>
                <div class="template-name">Science Quiz</div>
                <div class="template-desc">General science questions</div>
            </div>
            <div class="template-card" data-template="history">
                <div class="template-icon">
                    <i class="fas fa-landmark"></i>
                </div>
                <div class="template-name">History Quiz</div>
                <div class="template-desc">World history questions</div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    let quizData = null;

    // Drag and drop functionality
    const uploadArea = $('#uploadArea');
    const fileInput = $('#fileInput');

    uploadArea.on('click', function() {
        fileInput.click();
    });

    uploadArea.on('dragover', function(e) {
        e.preventDefault();
        $(this).addClass('dragover');
    });

    uploadArea.on('dragleave', function(e) {
        e.preventDefault();
        $(this).removeClass('dragover');
    });

    uploadArea.on('drop', function(e) {
        e.preventDefault();
        $(this).removeClass('dragover');
        
        const files = e.originalEvent.dataTransfer.files;
        if (files.length > 0) {
            handleFile(files[0]);
        }
    });

    fileInput.on('change', function() {
        if (this.files.length > 0) {
            handleFile(this.files[0]);
        }
    });

    function handleFile(file) {
        if (file.type !== 'application/json' && !file.name.endsWith('.json')) {
            showError('Please select a valid JSON file.');
            return;
        }

        const reader = new FileReader();
        reader.onload = function(e) {
            try {
                const data = JSON.parse(e.target.result);
                quizData = data;
                showPreview(data);
                $('#createQuizBtn').prop('disabled', false);
                showSuccess('File loaded successfully!');
            } catch (error) {
                showError('Invalid JSON file. Please check the format.');
            }
        };
        reader.readAsText(file);
    }

    function showPreview(data) {
        if (!Array.isArray(data) || data.length === 0) {
            showError('Quiz data must be an array of questions.');
            return;
        }

        $('#questionCount').text(data.length + ' questions');
        
        let previewHtml = '';
        data.slice(0, 3).forEach((question, index) => {
            previewHtml += `
                <div class="question-preview">
                    <div class="question-text">${index + 1}. ${question.question}</div>
                    <ul class="options-list">
            `;
            
            question.options.forEach(option => {
                const isCorrect = option === question.correct_answer;
                previewHtml += `
                    <li class="option-item ${isCorrect ? 'correct' : ''}">
                        ${option} ${isCorrect ? '<i class="fas fa-check"></i>' : ''}
                    </li>
                `;
            });
            
            previewHtml += `
                    </ul>
                </div>
            `;
        });

        if (data.length > 3) {
            previewHtml += `<p style="text-align: center; color: #666;">... and ${data.length - 3} more questions</p>`;
        }

        $('#previewContent').html(previewHtml);
        $('#previewSection').addClass('show');
    }

    // Create quiz functionality
    $('#createQuizBtn').on('click', function() {
        if (!quizData) {
            showError('Please upload a quiz file first.');
            return;
        }

        const btn = $(this);
        btn.prop('disabled', true);
        $('#loading').show();

        $.ajax({
            url: '{{ route("quiz.store") }}',
            method: 'POST',
            data: {
                quiz_data: JSON.stringify(quizData),
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    showSuccess('Quiz created successfully!');
                    setTimeout(() => {
                        window.location.href = '{{ route("dashboard") }}';
                    }, 2000);
                } else {
                    showError(response.message || 'Failed to create quiz.');
                }
            },
            error: function(xhr) {
                let errorMessage = 'Failed to create quiz.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                showError(errorMessage);
            },
            complete: function() {
                btn.prop('disabled', false);
                $('#loading').hide();
            }
        });
    });

    // Template functionality
    $('.template-card').on('click', function() {
        const template = $(this).data('template');
        loadTemplate(template);
    });

    function loadTemplate(template) {
        const templates = {
            math: [
                {
                    "id": "math_1",
                    "question": "What is 2 + 2?",
                    "options": ["3", "4", "5", "6"],
                    "correct_answer": "4"
                },
                {
                    "id": "math_2",
                    "question": "What is 5 × 6?",
                    "options": ["25", "30", "35", "40"],
                    "correct_answer": "30"
                },
                {
                    "id": "math_3",
                    "question": "What is the square root of 16?",
                    "options": ["2", "4", "8", "16"],
                    "correct_answer": "4"
                }
            ],
            science: [
                {
                    "id": "science_1",
                    "question": "What is the chemical symbol for water?",
                    "options": ["H2O", "CO2", "O2", "N2"],
                    "correct_answer": "H2O"
                },
                {
                    "id": "science_2",
                    "question": "Which planet is closest to the Sun?",
                    "options": ["Venus", "Mars", "Mercury", "Earth"],
                    "correct_answer": "Mercury"
                },
                {
                    "id": "science_3",
                    "question": "What is the largest organ in the human body?",
                    "options": ["Heart", "Brain", "Liver", "Skin"],
                    "correct_answer": "Skin"
                }
            ],
            history: [
                {
                    "id": "history_1",
                    "question": "In which year did World War II end?",
                    "options": ["1943", "1944", "1945", "1946"],
                    "correct_answer": "1945"
                },
                {
                    "id": "history_2",
                    "question": "Who was the first President of the United States?",
                    "options": ["Thomas Jefferson", "John Adams", "George Washington", "Benjamin Franklin"],
                    "correct_answer": "George Washington"
                },
                {
                    "id": "history_3",
                    "question": "Which ancient wonder was located in Alexandria?",
                    "options": ["Colossus of Rhodes", "Lighthouse of Alexandria", "Hanging Gardens", "Temple of Artemis"],
                    "correct_answer": "Lighthouse of Alexandria"
                }
            ]
        };

        quizData = templates[template];
        showPreview(quizData);
        $('#createQuizBtn').prop('disabled', false);
        showSuccess(`${template.charAt(0).toUpperCase() + template.slice(1)} template loaded!`);
    }

    function showSuccess(message) {
        $('#successAlert').text(message).show();
        $('#errorAlert').hide();
        setTimeout(() => {
            $('#successAlert').hide();
        }, 5000);
    }

    function showError(message) {
        $('#errorAlert').text(message).show();
        $('#successAlert').hide();
        setTimeout(() => {
            $('#errorAlert').hide();
        }, 5000);
    }
});
</script>
@endsection 