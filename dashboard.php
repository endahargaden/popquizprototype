<?php
require_once 'auth.php';
require_once 'config.php';

// Redirect if not logged in
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$user = getCurrentUser();

// Debug information
error_log("Dashboard Debug - User ID: " . $_SESSION['user_id'] . ", User: " . json_encode($user));

// Pagination settings
$itemsPerPage = 6;
$currentPage = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';

// Get user's quizzes with search and pagination
$pdo = getDBConnection();
$quizzes = [];
$totalQuizzes = 0;
$totalPages = 0;

if ($pdo) {
    try {
        // Build search query
        $searchCondition = '';
        $countParams = [$_SESSION['user_id']];
        $queryParams = [$_SESSION['user_id']];
        
        if (!empty($searchTerm)) {
            $searchCondition = ' AND (title LIKE ? OR quiz_id LIKE ?)';
            $countParams[] = "%$searchTerm%";
            $countParams[] = "%$searchTerm%";
            $queryParams[] = "%$searchTerm%";
            $queryParams[] = "%$searchTerm%";
        }
        
        // Get total count for pagination
        $countStmt = $pdo->prepare("SELECT COUNT(*) FROM quizzes WHERE user_id = ?" . $searchCondition);
        $countStmt->execute($countParams);
        $totalQuizzes = $countStmt->fetchColumn();
        $totalPages = ceil($totalQuizzes / $itemsPerPage);
        
        error_log("Dashboard Debug - Total quizzes: " . $totalQuizzes . ", User ID: " . $_SESSION['user_id']);
        
        // Get quizzes for current page
        $offset = ($currentPage - 1) * $itemsPerPage;
        
        // Try a different approach for LIMIT/OFFSET to avoid MySQL compatibility issues
        $stmt = $pdo->prepare("SELECT * FROM quizzes WHERE user_id = ?" . $searchCondition . " ORDER BY created_at DESC LIMIT " . (int)$itemsPerPage . " OFFSET " . (int)$offset);
        $stmt->execute($queryParams);
        $quizzes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        error_log("Dashboard Debug - Quizzes found: " . count($quizzes));
        
        // Debug: Check if there's an error
        if (count($quizzes) === 0 && $totalQuizzes > 0) {
            error_log("Dashboard Debug - WARNING: Count shows " . $totalQuizzes . " but main query returned 0 results");
            error_log("Dashboard Debug - Search condition: " . $searchCondition);
            error_log("Dashboard Debug - Current page: " . $currentPage);
        }
        
    } catch (Exception $e) {
        error_log("Error fetching quizzes: " . $e->getMessage());
    }
} else {
    error_log("Dashboard Debug - Database connection failed");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz Dashboard</title>
    <link rel="stylesheet" href="dashboard.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="dashboard-container">
        <div class="dashboard-header">
            <div class="user-info">
                <div class="user-avatar">
                    <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                </div>
                <div class="user-details">
                    <h2>Welcome back, <?php echo htmlspecialchars($user['username']); ?>!</h2>
                    <p><?php echo htmlspecialchars($user['email']); ?></p>
                </div>
            </div>
            <div class="header-actions">
                <a href="create_quiz.php" class="create-quiz-btn">
                    <i class="fas fa-plus"></i>
                    Create New Quiz
                </a>
                <button class="logout-btn" onclick="logout()">
                    <i class="fas fa-sign-out-alt"></i>
                    Logout
                </button>
            </div>
        </div>
        
        <div class="stats-summary">
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number"><?php echo $totalQuizzes; ?></div>
                    <div class="stat-label">Total Quizzes</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo date('M Y'); ?></div>
                    <div class="stat-label">Current Month</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="stat-label">Analytics Ready</div>
                </div>
            </div>
        </div>
        
        <div class="quizzes-section">
            <div class="section-header">
                <h3 class="section-title">Your Quizzes (<?php echo $totalQuizzes; ?>)</h3>
                <div class="search-container">
                    <form method="GET" action="" style="display: flex; gap: 10px; align-items: center;">
                        <input type="text" name="search" class="search-input" placeholder="Search quizzes..." value="<?php echo htmlspecialchars($searchTerm); ?>">
                        <button type="submit" class="search-btn">
                            <i class="fas fa-search"></i>
                            Search
                        </button>
                        <?php if (!empty($searchTerm)): ?>
                            <a href="dashboard.php" class="search-btn" style="background: linear-gradient(135deg, #6c757d 0%, #495057 100%);">
                                <i class="fas fa-times"></i>
                                Clear
                            </a>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
            
            <?php if (empty($quizzes)): ?>
                <div class="empty-state">
                    <?php if (!empty($searchTerm)): ?>
                        <h3>No quizzes found!</h3>
                        <p>No quizzes match your search term "<?php echo htmlspecialchars($searchTerm); ?>".</p>
                        <a href="dashboard.php" class="create-quiz-btn">
                            <i class="fas fa-search"></i>
                            Clear Search
                        </a>
                    <?php else: ?>
                        <h3>No quizzes yet!</h3>
                        <p>Create your first quiz to get started with interactive assessments.</p>
                        <a href="create_quiz.php" class="create-quiz-btn">
                            <i class="fas fa-plus"></i>
                            Create Your First Quiz
                        </a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="quizzes-grid">
                    <?php foreach ($quizzes as $quiz): ?>
                        <div class="quiz-card">
                            <div class="quiz-header">
                                <h4 class="quiz-title"><?php echo htmlspecialchars($quiz['title']); ?></h4>
                                <div class="quiz-actions">
                                    <a href="quiz.php?id=<?php echo $quiz['quiz_id']; ?>" class="action-btn view-btn" target="_blank">
                                        <i class="fas fa-eye"></i>
                                        View
                                    </a>
                                    <button class="action-btn stats-btn" onclick="showStats('<?php echo $quiz['quiz_id']; ?>')">
                                        <i class="fas fa-chart-bar"></i>
                                        Stats
                                    </button>
                                    <button class="action-btn delete-btn" onclick="deleteQuiz('<?php echo $quiz['quiz_id']; ?>')">
                                        <i class="fas fa-trash"></i>
                                        Delete
                                    </button>
                                </div>
                            </div>
                            
                            <div class="quiz-info">
                                <p><i class="fas fa-fingerprint"></i> <strong>Quiz ID:</strong> <?php echo $quiz['quiz_id']; ?></p>
                                <p><i class="fas fa-calendar-alt"></i> <strong>Created:</strong> <?php echo date('M j, Y g:i A', strtotime($quiz['created_at'])); ?></p>
                            </div>
                            
                            <div class="qr-section">
                                <h5><i class="fas fa-qrcode"></i> QR Code for Students</h5>
                                <?php 
                                $qrPath = QR_DIR . "quiz_{$quiz['quiz_id']}.png";
                                $qrExists = file_exists($qrPath);
                                $qrSize = $qrExists ? filesize($qrPath) : 0;
                                
                                if ($qrExists && $qrSize > 100): 
                                ?>
                                    <div style="text-align: center; margin-bottom: 15px;">
                                        <img src="<?php echo $qrPath; ?>?v=<?php echo time(); ?>" alt="QR Code" class="qr-code" style="max-width: 200px; height: auto; display: inline-block;">
                                    </div>
                                <?php else: ?>
                                    <div style="padding: 20px; background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-radius: 10px; color: #666; text-align: center; border: 2px dashed #dee2e6; margin-bottom: 15px;">
                                        <i class="fas fa-qrcode" style="font-size: 2rem; color: #adb5bd; margin-bottom: 10px;"></i>
                                        <p style="margin: 0; font-weight: 600;">QR Code Not Available</p>
                                        <p style="margin: 5px 0 0 0; font-size: 0.9rem;">The QR code could not be generated</p>
                                        <button class="regenerate-qr-btn" onclick="regenerateQR('<?php echo $quiz['quiz_id']; ?>')" style="margin-top: 10px; padding: 8px 16px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 0.9rem;">
                                            <i class="fas fa-sync-alt"></i> Regenerate QR
                                        </button>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="quiz-url-container">
                                    <div class="quiz-url" id="url-<?php echo $quiz['quiz_id']; ?>">
                                        <?php 
                                        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
                                        $host = $_SERVER['HTTP_HOST'];
                                        $path = dirname($_SERVER['REQUEST_URI']);
                                        $quizUrl = "{$protocol}://{$host}{$path}/quiz.php?id={$quiz['quiz_id']}";
                                        echo $quizUrl;
                                        ?>
                                    </div>
                                    <button class="copy-btn" onclick="copyToClipboard('<?php echo $quiz['quiz_id']; ?>', '<?php echo $quizUrl; ?>')">
                                        <i class="fas fa-copy"></i>
                                        Copy Link
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                    <div class="pagination-container">
                        <?php if ($currentPage > 1): ?>
                            <a href="?page=<?php echo $currentPage - 1; ?><?php echo !empty($searchTerm) ? '&search=' . urlencode($searchTerm) : ''; ?>" class="pagination-btn">
                                <i class="fas fa-chevron-left"></i>
                                Previous
                            </a>
                        <?php endif; ?>
                        
                        <span class="pagination-info">
                            Page <?php echo $currentPage; ?> of <?php echo $totalPages; ?>
                            (<?php echo $totalQuizzes; ?> total quizzes)
                        </span>
                        
                        <?php if ($currentPage < $totalPages): ?>
                            <a href="?page=<?php echo $currentPage + 1; ?><?php echo !empty($searchTerm) ? '&search=' . urlencode($searchTerm) : ''; ?>" class="pagination-btn">
                                Next
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Stats Modal -->
    <div id="statsModal" class="stats-modal">
        <div class="stats-content">
            <span class="close" onclick="closeStats()">&times;</span>
            <div id="statsContent"></div>
        </div>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script>
        function showStats(quizId) {
            $.post('quiz_manager.php', {
                action: 'get_quiz_stats',
                quiz_id: quizId
            }, function(response) {
                if (response.success) {
                    displayStats(response);
                    $('#statsModal').show();
                } else {
                    alert('Error loading stats: ' + response.message);
                }
            }, 'json');
        }
        
        function displayStats(data) {
            const stats = data.stats;
            const quiz = data.quiz;
            const results = data.results;
            
            let html = `
                <h2 style="color: #333; margin-bottom: 20px;">📊 Quiz Statistics: ${quiz.title}</h2>
                <div class="modal-stats-grid">
                    <div class="modal-stat-card">
                        <div class="modal-stat-number">${stats.total_attempts}</div>
                        <div class="modal-stat-label">Total Attempts</div>
                    </div>
                    <div class="modal-stat-card">
                        <div class="modal-stat-number">${stats.average_score}</div>
                        <div class="modal-stat-label">Average Score</div>
                    </div>
                </div>
            `;
            
            if (stats.easiest_question) {
                html += `
                    <div class="modal-stat-card">
                        <h4 style="color: #28a745; margin-bottom: 10px;">🥇 Easiest Question</h4>
                        <p><strong>Question ${stats.easiest_question.index}:</strong> ${stats.easiest_question.question}</p>
                        <p><strong>Success Rate:</strong> ${stats.easiest_question.difficulty}%</p>
                    </div>
                `;
            }
            
            if (stats.hardest_question) {
                html += `
                    <div class="modal-stat-card">
                        <h4 style="color: #dc3545; margin-bottom: 10px;">🥉 Hardest Question</h4>
                        <p><strong>Question ${stats.hardest_question.index}:</strong> ${stats.hardest_question.question}</p>
                        <p><strong>Success Rate:</strong> ${stats.hardest_question.difficulty}%</p>
                    </div>
                `;
            }
            
            if (results.length > 0) {
                html += `
                    <h3 style="color: #333; margin: 30px 0 20px 0;">📋 Recent Results</h3>
                    <table class="results-table">
                        <thead>
                            <tr>
                                <th>👤 Student</th>
                                <th>📊 Score</th>
                                <th>📅 Completed</th>
                            </tr>
                        </thead>
                        <tbody>
                `;
                
                results.slice(0, 10).forEach(result => {
                    html += `
                        <tr>
                            <td>${result.student_number}</td>
                            <td><strong>${result.final_score}</strong></td>
                            <td>${new Date(result.completed_at).toLocaleString()}</td>
                        </tr>
                    `;
                });
                
                html += '</tbody></table>';
            }
            
            $('#statsContent').html(html);
        }
        
        function closeStats() {
            $('#statsModal').hide();
        }
        
        function deleteQuiz(quizId) {
            if (confirm('Are you sure you want to delete this quiz? This action cannot be undone.')) {
                $.post('quiz_manager.php', {
                    action: 'delete_quiz',
                    quiz_id: quizId
                }, function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert('Error deleting quiz: ' + response.message);
                    }
                }, 'json');
            }
        }
        
        function copyToClipboard(quizId, url) {
            // Add visual feedback to the button
            const copyBtn = event.target.closest('.copy-btn');
            const originalText = copyBtn.innerHTML;
            copyBtn.innerHTML = '<i class="fas fa-check"></i> Copied!';
            copyBtn.classList.add('copied');
            
            navigator.clipboard.writeText(url).then(function() {
                showCopySuccess();
                
                // Reset button after 2 seconds
                setTimeout(function() {
                    copyBtn.innerHTML = originalText;
                    copyBtn.classList.remove('copied');
                }, 2000);
            }).catch(function(err) {
                // Fallback for older browsers
                const textArea = document.createElement('textarea');
                textArea.value = url;
                document.body.appendChild(textArea);
                textArea.select();
                document.execCommand('copy');
                document.body.removeChild(textArea);
                
                showCopySuccess();
                
                // Reset button after 2 seconds
                setTimeout(function() {
                    copyBtn.innerHTML = originalText;
                    copyBtn.classList.remove('copied');
                }, 2000);
            });
        }
        
        function showCopySuccess() {
            // Remove any existing success message
            $('.copy-success').remove();
            
            // Create and show success message
            const successMsg = $('<div class="copy-success"><i class="fas fa-check-circle"></i> URL copied to clipboard successfully!</div>');
            $('body').append(successMsg);
            
            // Auto-remove after 3 seconds
            setTimeout(function() {
                successMsg.fadeOut(300, function() {
                    $(this).remove();
                });
            }, 3000);
        }
        
        function regenerateQR(quizId) {
            const btn = event.target.closest('.regenerate-qr-btn');
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generating...';
            btn.disabled = true;
            
            $.post('quiz_manager.php', {
                action: 'regenerate_qr',
                quiz_id: quizId
            }, function(response) {
                if (response.success) {
                    // Show success message
                    const successMsg = $('<div class="copy-success"><i class="fas fa-qrcode"></i> QR code regenerated successfully!</div>');
                    $('body').append(successMsg);
                    
                    // Auto-remove after 2 seconds
                    setTimeout(function() {
                        successMsg.fadeOut(300, function() {
                            $(this).remove();
                        });
                    }, 2000);
                    
                    // Reload page after a short delay
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else {
                    alert('Error regenerating QR code: ' + response.message);
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                }
            }, 'json').fail(function() {
                alert('Network error while regenerating QR code.');
                btn.innerHTML = originalText;
                btn.disabled = false;
            });
        }
        
        function logout() {
            if (confirm('Are you sure you want to logout?')) {
                $.post('auth.php', {
                    action: 'logout'
                }, function(response) {
                    if (response.success) {
                        window.location.href = 'login.php';
                    } else {
                        alert('Logout failed: ' + response.message);
                    }
                }, 'json');
            }
        }
        
        // Close modal when clicking outside
        $(window).click(function(event) {
            if (event.target == document.getElementById('statsModal')) {
                closeStats();
            }
        });
    </script>
</body>
</html> 