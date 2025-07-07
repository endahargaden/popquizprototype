# Quiz Management System

A comprehensive quiz management system that allows professors to create, manage, and analyze quizzes with QR code access for students.

## Features

### For Professors (Authenticated Users)
1. **User Registration & Login** - Secure authentication system
2. **Quiz Creation** - Drag & drop JSON file upload with validation
3. **Quiz Management Dashboard** - View all created quizzes
4. **QR Code Generation** - Automatic QR code creation for each quiz
5. **Quiz Statistics** - Detailed analytics including:
   - Average overall score
   - Easiest and hardest questions
   - Individual question performance
   - Student attempt history
6. **Quiz Deletion** - Remove quizzes and associated data

### For Students
1. **QR Code Access** - Scan QR code to access quiz
2. **Secure Quiz Taking** - Anti-cheating measures:
   - Window focus/blur detection
   - Page visibility monitoring
   - Session management
   - Time limits per question
3. **Real-time Feedback** - Immediate answer validation
4. **Session Protection** - Prevents page refresh/back navigation

## System Architecture

### Database Tables
- **users** - Professor accounts and authentication
- **quizzes** - Quiz metadata and file references
- **quiz_results** - Student responses and scores

### File Structure
```
├── config.php              # Database and system configuration
├── auth.php                # Authentication system
├── login.php               # Login/registration page
├── dashboard.php           # Professor dashboard
├── create_quiz.php         # Quiz creation interface
├── quiz_manager.php        # Quiz management backend
├── quiz.php                # Dynamic quiz page (for students)
├── index.php               # Original quiz page (legacy)
├── script.js               # Frontend JavaScript
├── style.css               # Styling
├── edgeworth.json          # Sample quiz data
├── uploads/                # Quiz JSON files
├── qr_codes/               # Generated QR codes
└── logs/                   # System logs
```

## Setup Instructions

### 1. Database Setup
1. Create a MySQL database named `quiz_system`
2. Update database credentials in `config.php`:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'quiz_system');
   define('DB_USER', 'your_username');
   define('DB_PASS', 'your_password');
   ```
3. The system will automatically create required tables on first run

### 2. File Permissions
Ensure the following directories are writable:
```bash
chmod 755 uploads/
chmod 755 qr_codes/
chmod 755 logs/
```

### 3. Web Server Configuration
- PHP 7.4+ required
- MySQL/MariaDB database
- Apache/Nginx web server
- Enable PHP sessions and JSON extensions

## Usage Guide

### For Professors

#### 1. Registration & Login
- Visit `login.php` to create an account or sign in
- Use email or username to login

#### 2. Creating a Quiz
1. Click "Create New Quiz" from dashboard
2. Prepare a JSON file with quiz questions (see format below)
3. Drag & drop or select the JSON file
4. Preview the quiz structure
5. Click "Create Quiz" to generate unique URL and QR code

#### 3. Managing Quizzes
- View all quizzes in the dashboard
- Access quiz statistics and results
- Download QR codes for student access
- Delete quizzes when no longer needed

### For Students

#### 1. Taking a Quiz
1. Scan the QR code provided by professor
2. Enter student number
3. Answer questions within time limits
4. Complete all questions to finish

#### 2. Quiz Rules
- Stay on the quiz page (no switching tabs/windows)
- Answer within 20 seconds per question
- No page refresh or back navigation allowed

## JSON Quiz Format

```json
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
```

### JSON Requirements
- Array of question objects
- Each question must have: `id`, `question`, `options`, `correct_answer`
- `options` must be an array with at least 2 choices
- `correct_answer` must match one of the options exactly

## Security Features

### Anti-Cheating Measures
- **Window Focus Detection** - Monitors if student switches away from quiz
- **Page Visibility API** - Detects tab switching and browser minimization
- **Session Management** - Prevents multiple simultaneous attempts
- **Time Limits** - Enforces per-question time constraints
- **IP Tracking** - Logs student IP addresses for audit trail

### Data Protection
- Password hashing using PHP's `password_hash()`
- SQL injection prevention with prepared statements
- XSS protection with `htmlspecialchars()`
- CSRF protection through session validation

## Analytics & Reporting

### Quiz Statistics
- **Overall Performance** - Average scores across all attempts
- **Question Analysis** - Success rates for individual questions
- **Difficulty Assessment** - Automatic identification of easiest/hardest questions
- **Student Tracking** - Individual student performance history

### Data Export
- Results stored in database for analysis
- Individual question scores tracked
- Timestamp and session information logged
- User agent and IP address tracking

## API Endpoints

### Authentication
- `POST auth.php?action=register` - User registration
- `POST auth.php?action=login` - User login
- `POST auth.php?action=logout` - User logout

### Quiz Management
- `POST quiz_manager.php?action=create_quiz` - Create new quiz
- `POST quiz_manager.php?action=get_quiz_stats` - Get quiz statistics
- `POST quiz_manager.php?action=delete_quiz` - Delete quiz

### Quiz Taking
- `POST quiz.php?action=start_quiz_session` - Start quiz session
- `GET quiz.php?action=get_question` - Get question by index
- `POST quiz.php?action=validate_answer` - Validate student answer
- `POST quiz.php?action=end_quiz_session` - End quiz and save results

## Troubleshooting

### Common Issues

1. **Database Connection Error**
   - Check database credentials in `config.php`
   - Ensure MySQL service is running
   - Verify database exists

2. **File Upload Issues**
   - Check directory permissions (755 for uploads/, qr_codes/, logs/)
   - Verify PHP file upload settings
   - Check available disk space

3. **QR Code Generation Fails**
   - Ensure internet connection (uses Google Charts API)
   - Check qr_codes/ directory permissions
   - Verify URL generation is working

4. **Quiz Not Loading**
   - Check JSON file format
   - Verify file exists in uploads/ directory
   - Check database for quiz record

### Error Logs
- Check PHP error logs for detailed error messages
- System logs stored in `logs/` directory
- Database errors logged to PHP error log

## Future Enhancements

### Planned Features
- **SimpleSAML Integration** - Enterprise authentication
- **Bulk Quiz Import** - Multiple quiz creation
- **Advanced Analytics** - Detailed performance reports
- **Quiz Templates** - Pre-built question sets
- **Export Options** - CSV/Excel result downloads
- **Mobile App** - Native mobile quiz taking

### Technical Improvements
- **Caching System** - Improved performance
- **API Rate Limiting** - Security enhancement
- **Real-time Updates** - Live quiz monitoring
- **Offline Support** - Local quiz storage

## Support

For technical support or feature requests, please contact the development team.

## License

This project is proprietary software. All rights reserved. 