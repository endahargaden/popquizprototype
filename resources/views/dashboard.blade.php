@extends('layouts.app')

@section('title', 'Dashboard - Quiz System')

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
        max-width: 1200px;
        margin: 0 auto;
        padding: 2rem;
    }

    .dashboard-header {
        text-align: center;
        margin-bottom: 3rem;
        color: white;
    }

    .dashboard-header h1 {
        font-size: 3rem;
        margin: 0 0 1rem 0;
        font-weight: 300;
    }

    .dashboard-header p {
        font-size: 1.2rem;
        margin: 0;
        opacity: 0.9;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 2rem;
        margin-bottom: 3rem;
    }

    .stat-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border-radius: 20px;
        padding: 2rem;
        text-align: center;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease;
    }

    .stat-card:hover {
        transform: translateY(-5px);
    }

    .stat-card i {
        font-size: 3rem;
        color: #667eea;
        margin-bottom: 1rem;
    }

    .stat-card h3 {
        font-size: 2.5rem;
        margin: 0 0 0.5rem 0;
        color: #333;
    }

    .stat-card p {
        color: #666;
        margin: 0;
        font-size: 1.1rem;
    }

    .search-section {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border-radius: 20px;
        padding: 2rem;
        margin-bottom: 2rem;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    }

    .search-form {
        display: flex;
        gap: 1rem;
        align-items: center;
    }

    .search-input {
        flex: 1;
        padding: 1rem;
        border: 2px solid #e1e5e9;
        border-radius: 10px;
        font-size: 1rem;
        transition: all 0.3s ease;
    }

    .search-input:focus {
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

    .btn-secondary {
        background: #6c757d;
        color: white;
    }

    .btn-secondary:hover {
        background: #5a6268;
        transform: translateY(-2px);
    }

    .btn-danger {
        background: #dc3545;
        color: white;
    }

    .btn-danger:hover {
        background: #c82333;
        transform: translateY(-2px);
    }

    .btn-success {
        background: #28a745;
        color: white;
    }

    .btn-success:hover {
        background: #218838;
        transform: translateY(-2px);
    }

    .quiz-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
        gap: 2rem;
        margin-bottom: 2rem;
    }

    .quiz-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border-radius: 20px;
        padding: 2rem;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .quiz-card:hover {
        transform: translateY(-5px);
    }

    .quiz-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }

    .quiz-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 1rem;
    }

    .quiz-title {
        font-size: 1.5rem;
        font-weight: 600;
        color: #333;
        margin: 0;
        flex: 1;
    }

    .quiz-id {
        font-size: 0.8rem;
        color: #666;
        background: #f8f9fa;
        padding: 0.25rem 0.5rem;
        border-radius: 5px;
        font-family: monospace;
    }

    .quiz-meta {
        display: flex;
        gap: 1rem;
        margin-bottom: 1.5rem;
        font-size: 0.9rem;
        color: #666;
    }

    .quiz-meta span {
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }

    .quiz-actions {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
    }

    .qr-section {
        text-align: center;
        margin: 1rem 0;
    }

    .qr-code {
        max-width: 150px;
        max-height: 150px;
        border-radius: 10px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }

    .qr-placeholder {
        width: 150px;
        height: 150px;
        background: #f8f9fa;
        border: 2px dashed #dee2e6;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #6c757d;
        font-size: 0.9rem;
        margin: 0 auto;
    }

    .pagination {
        display: flex;
        justify-content: center;
        gap: 0.5rem;
        margin-top: 2rem;
    }

    .pagination a, .pagination span {
        padding: 0.5rem 1rem;
        border-radius: 8px;
        text-decoration: none;
        transition: all 0.3s ease;
    }

    .pagination a {
        background: rgba(255, 255, 255, 0.9);
        color: #333;
    }

    .pagination a:hover {
        background: #667eea;
        color: white;
    }

    .pagination .current {
        background: #667eea;
        color: white;
    }

    .no-quizzes {
        text-align: center;
        color: white;
        font-size: 1.2rem;
        margin: 3rem 0;
    }

    .toast {
        position: fixed;
        top: 20px;
        right: 20px;
        background: #28a745;
        color: white;
        padding: 1rem 2rem;
        border-radius: 10px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        z-index: 10000;
        transform: translateX(400px);
        transition: transform 0.3s ease;
    }

    .toast.show {
        transform: translateX(0);
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

        .dashboard-header h1 {
            font-size: 2rem;
        }

        .search-form {
            flex-direction: column;
        }

        .quiz-grid {
            grid-template-columns: 1fr;
        }

        .quiz-actions {
            justify-content: center;
        }
    }
</style>
@endsection

@section('content')
<div class="dashboard-header">
    <h1>Welcome, {{ $user->username }}!</h1>
    <p>Manage your quizzes and track student performance</p>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <i class="fas fa-clipboard-list"></i>
        <h3>{{ $totalQuizzes }}</h3>
        <p>Total Quizzes</p>
    </div>
    <div class="stat-card">
        <i class="fas fa-users"></i>
        <h3>{{ $quizzes->sum(function($quiz) { return $quiz->results->count(); }) }}</h3>
        <p>Total Attempts</p>
    </div>
    <div class="stat-card">
        <i class="fas fa-chart-line"></i>
        <h3>{{ $quizzes->count() > 0 ? round($quizzes->sum(function($quiz) { return $quiz->results->avg('final_score'); }) / $quizzes->count(), 1) : 0 }}</h3>
        <p>Average Score</p>
    </div>
    <div class="stat-card">
        <i class="fas fa-calendar-alt"></i>
        <h3>{{ $quizzes->where('created_at', '>=', now()->subDays(7))->count() }}</h3>
        <p>This Week</p>
    </div>
</div>

<div class="search-section">
    <form method="GET" action="{{ route('dashboard') }}" class="search-form">
        <input type="text" name="search" value="{{ $searchTerm }}" placeholder="Search quizzes by title or ID..." class="search-input">
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-search"></i>
            Search
        </button>
        @if($searchTerm)
            <a href="{{ route('dashboard') }}" class="btn btn-secondary">
                <i class="fas fa-times"></i>
                Clear
            </a>
        @endif
    </form>
</div>

@if($quizzes->count() > 0)
    <div class="quiz-grid">
        @foreach($quizzes as $quiz)
            <div class="quiz-card">
                <div class="quiz-header">
                    <h3 class="quiz-title">{{ $quiz->title }}</h3>
                    <span class="quiz-id">{{ $quiz->quiz_id }}</span>
                </div>
                
                <div class="quiz-meta">
                    <span><i class="fas fa-calendar"></i> {{ $quiz->created_at->format('M j, Y') }}</span>
                    <span><i class="fas fa-users"></i> {{ $quiz->results->count() }} attempts</span>
                    <span><i class="fas fa-star"></i> {{ $quiz->results->avg('final_score') ? round($quiz->results->avg('final_score'), 1) : 0 }} avg</span>
                </div>

                <div class="qr-section">
                    @php
                        $qrPath = storage_path('app/qr_codes/quiz_' . $quiz->quiz_id . '.png');
                    @endphp
                    @if(file_exists($qrPath))
                        <img src="{{ asset('storage/qr_codes/quiz_' . $quiz->quiz_id . '.png') }}" alt="QR Code" class="qr-code">
                    @else
                        <div class="qr-placeholder">
                            <i class="fas fa-qrcode"></i><br>
                            QR Code<br>
                            <small>Not generated</small>
                        </div>
                    @endif
                </div>

                <div class="quiz-actions">
                    <a href="{{ route('quiz.show', $quiz->quiz_id) }}" class="btn btn-primary" target="_blank">
                        <i class="fas fa-external-link-alt"></i>
                        View Quiz
                    </a>
                    <button class="btn btn-success copy-url" data-url="{{ route('quiz.show', $quiz->quiz_id) }}">
                        <i class="fas fa-copy"></i>
                        Copy URL
                    </button>
                    <button class="btn btn-secondary view-stats" data-quiz-id="{{ $quiz->quiz_id }}">
                        <i class="fas fa-chart-bar"></i>
                        Stats
                    </button>
                    @if(!file_exists($qrPath))
                        <button class="btn btn-secondary regenerate-qr" data-quiz-id="{{ $quiz->quiz_id }}">
                            <i class="fas fa-qrcode"></i>
                            Generate QR
                        </button>
                    @endif
                    <button class="btn btn-danger delete-quiz" data-quiz-id="{{ $quiz->quiz_id }}">
                        <i class="fas fa-trash"></i>
                        Delete
                    </button>
                </div>
            </div>
        @endforeach
    </div>

    @if($totalPages > 1)
        <div class="pagination">
            @for($i = 1; $i <= $totalPages; $i++)
                @if($i == $page)
                    <span class="current">{{ $i }}</span>
                @else
                    <a href="{{ route('dashboard', ['page' => $i, 'search' => $searchTerm]) }}">{{ $i }}</a>
                @endif
            @endfor
        </div>
    @endif
@else
    <div class="no-quizzes">
        <i class="fas fa-clipboard-list" style="font-size: 4rem; margin-bottom: 1rem; opacity: 0.5;"></i>
        <h2>No quizzes found</h2>
        <p>{{ $searchTerm ? 'Try adjusting your search terms.' : 'Create your first quiz to get started!' }}</p>
        @if(!$searchTerm)
            <a href="{{ route('quiz.create') }}" class="btn btn-primary" style="margin-top: 1rem;">
                <i class="fas fa-plus"></i>
                Create Quiz
            </a>
        @endif
    </div>
@endif

<!-- Stats Modal -->
<div id="statsModal" class="modal" style="display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5);">
    <div class="modal-content" style="background-color: white; margin: 5% auto; padding: 2rem; border-radius: 20px; width: 80%; max-width: 800px; max-height: 80vh; overflow-y: auto;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h2 id="statsTitle">Quiz Statistics</h2>
            <button onclick="closeStatsModal()" style="background: none; border: none; font-size: 1.5rem; cursor: pointer;">&times;</button>
        </div>
        <div id="statsContent"></div>
    </div>
</div>

<div id="toast" class="toast"></div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Copy URL functionality
    $('.copy-url').click(function() {
        const url = $(this).data('url');
        navigator.clipboard.writeText(url).then(function() {
            showToast('Quiz URL copied to clipboard!');
        }).catch(function() {
            // Fallback for older browsers
            const textArea = document.createElement('textarea');
            textArea.value = url;
            document.body.appendChild(textArea);
            textArea.select();
            document.execCommand('copy');
            document.body.removeChild(textArea);
            showToast('Quiz URL copied to clipboard!');
        });
    });

    // View stats functionality
    $('.view-stats').click(function() {
        const quizId = $(this).data('quiz-id');
        $.ajax({
            url: '{{ route("quiz.stats") }}',
            method: 'POST',
            data: {
                quiz_id: quizId,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    displayStats(response);
                } else {
                    showToast('Failed to load statistics', 'error');
                }
            },
            error: function() {
                showToast('Failed to load statistics', 'error');
            }
        });
    });

    // Regenerate QR code
    $('.regenerate-qr').click(function() {
        const quizId = $(this).data('quiz-id');
        const button = $(this);
        
        button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Generating...');
        
        $.ajax({
            url: '{{ route("quiz.regenerate-qr") }}',
            method: 'POST',
            data: {
                quiz_id: quizId,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    showToast(response.message || 'Failed to generate QR code', 'error');
                }
            },
            error: function() {
                showToast('Failed to generate QR code', 'error');
            },
            complete: function() {
                button.prop('disabled', false).html('<i class="fas fa-qrcode"></i> Generate QR');
            }
        });
    });

    // Delete quiz functionality
    $('.delete-quiz').click(function() {
        if (confirm('Are you sure you want to delete this quiz? This action cannot be undone.')) {
            const quizId = $(this).data('quiz-id');
            const card = $(this).closest('.quiz-card');
            
            $.ajax({
                url: '{{ route("quiz.delete") }}',
                method: 'DELETE',
                data: {
                    quiz_id: quizId,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        card.fadeOut(300, function() {
                            $(this).remove();
                            if ($('.quiz-card').length === 0) {
                                location.reload();
                            }
                        });
                        showToast('Quiz deleted successfully');
                    } else {
                        showToast(response.message || 'Failed to delete quiz', 'error');
                    }
                },
                error: function() {
                    showToast('Failed to delete quiz', 'error');
                }
            });
        }
    });
});

function displayStats(data) {
    const quiz = data.quiz;
    const stats = data.stats;
    const results = data.results;
    
    let content = `
        <div style="margin-bottom: 2rem;">
            <h3>${quiz.title}</h3>
            <p><strong>Quiz ID:</strong> ${quiz.quiz_id}</p>
            <p><strong>Created:</strong> ${new Date(quiz.created_at).toLocaleDateString()}</p>
        </div>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 2rem;">
            <div style="background: #f8f9fa; padding: 1rem; border-radius: 10px; text-align: center;">
                <h4>${stats.total_attempts}</h4>
                <p>Total Attempts</p>
            </div>
            <div style="background: #f8f9fa; padding: 1rem; border-radius: 10px; text-align: center;">
                <h4>${stats.average_score}</h4>
                <p>Average Score</p>
            </div>
        </div>
    `;
    
    if (stats.easiest_question) {
        content += `
            <div style="background: #e8f5e8; padding: 1rem; border-radius: 10px; margin-bottom: 1rem;">
                <h4><i class="fas fa-thumbs-up"></i> Easiest Question</h4>
                <p><strong>Question ${stats.easiest_question.index}:</strong> ${stats.easiest_question.question}</p>
                <p><strong>Success Rate:</strong> ${stats.easiest_question.difficulty}%</p>
            </div>
        `;
    }
    
    if (stats.hardest_question) {
        content += `
            <div style="background: #ffe8e8; padding: 1rem; border-radius: 10px; margin-bottom: 1rem;">
                <h4><i class="fas fa-thumbs-down"></i> Hardest Question</h4>
                <p><strong>Question ${stats.hardest_question.index}:</strong> ${stats.hardest_question.question}</p>
                <p><strong>Success Rate:</strong> ${stats.hardest_question.difficulty}%</p>
            </div>
        `;
    }
    
    if (results.length > 0) {
        content += `
            <div style="margin-top: 2rem;">
                <h4>Recent Attempts</h4>
                <div style="max-height: 300px; overflow-y: auto;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="background: #f8f9fa;">
                                <th style="padding: 0.5rem; text-align: left; border-bottom: 1px solid #dee2e6;">Student</th>
                                <th style="padding: 0.5rem; text-align: left; border-bottom: 1px solid #dee2e6;">Score</th>
                                <th style="padding: 0.5rem; text-align: left; border-bottom: 1px solid #dee2e6;">Date</th>
                            </tr>
                        </thead>
                        <tbody>
        `;
        
        results.slice(0, 10).forEach(result => {
            content += `
                <tr>
                    <td style="padding: 0.5rem; border-bottom: 1px solid #dee2e6;">${result.student_number}</td>
                    <td style="padding: 0.5rem; border-bottom: 1px solid #dee2e6;">${result.final_score}</td>
                    <td style="padding: 0.5rem; border-bottom: 1px solid #dee2e6;">${new Date(result.created_at).toLocaleDateString()}</td>
                </tr>
            `;
        });
        
        content += `
                        </tbody>
                    </table>
                </div>
            </div>
        `;
    }
    
    $('#statsTitle').text(`Statistics - ${quiz.title}`);
    $('#statsContent').html(content);
    $('#statsModal').show();
}

function closeStatsModal() {
    $('#statsModal').hide();
}

function showToast(message, type = 'success') {
    const toast = $('#toast');
    toast.text(message);
    toast.removeClass('success error').addClass(type);
    toast.addClass('show');
    
    setTimeout(() => {
        toast.removeClass('show');
    }, 3000);
}

// Close modal when clicking outside
$(window).click(function(event) {
    if (event.target == document.getElementById('statsModal')) {
        closeStatsModal();
    }
});
</script>
@endsection 