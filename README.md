# Laravel Quiz Management System

A comprehensive quiz management system built with Laravel, featuring professor registration/login, quiz creation via drag-and-drop JSON upload, unique quiz URLs with QR code generation, student quiz taking, and detailed analytics.

## Features

### For Professors/Administrators
- **User Authentication**: Secure registration and login system
- **Quiz Creation**: Drag-and-drop JSON file upload with validation
- **Quiz Templates**: Pre-built templates for Math, Science, and History quizzes
- **Unique Quiz URLs**: Each quiz gets a unique identifier and shareable URL
- **QR Code Generation**: Automatic QR code generation for easy quiz sharing
- **Dashboard**: Modern dashboard with quiz statistics and management
- **Search & Pagination**: Find and manage quizzes efficiently
- **Analytics**: Detailed quiz statistics including:
  - Total attempts
  - Average scores
  - Easiest and hardest questions
  - Individual question performance
- **Quiz Management**: Delete quizzes and regenerate QR codes

### For Students
- **Quiz Taking**: Modern, responsive quiz interface
- **Timer**: 20-second timer per question
- **Progress Tracking**: Visual progress bar
- **Navigation**: Previous/Next question navigation
- **Session Management**: Prevents multiple attempts per student
- **Responsive Design**: Works on desktop and mobile devices

## System Requirements

- PHP 8.1 or higher
- MySQL 5.7 or higher
- Composer
- Web server (Apache/Nginx)

## Installation

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd quiz-system-laravel
   ```

2. **Install dependencies**
   ```bash
   composer install
   ```

3. **Environment setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Configure database**
   Edit `.env` file and set your database credentials:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=quiz_system_laravel
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   ```

5. **Run migrations**
   ```bash
   php artisan migrate
   ```

6. **Create storage links**
   ```bash
   php artisan storage:link
   ```

7. **Set permissions** (Linux/Mac)
   ```bash
   chmod -R 775 storage bootstrap/cache
   ```

8. **Start the development server**
   ```bash
   php artisan serve
   ```

## Database Structure

### Users Table
- `id` - Primary key
- `username` - Unique username
- `email` - Unique email address
- `password_hash` - Hashed password
- `created_at`, `updated_at` - Timestamps

### Quizzes Table
- `id` - Primary key
- `quiz_id` - Unique 32-character identifier
- `user_id` - Foreign key to users table
- `title` - Quiz title
- `json_file` - Path to JSON file containing questions
- `created_at`, `updated_at` - Timestamps

### Quiz Results Table
- `id` - Primary key
- `quiz_id` - Foreign key to quizzes table
- `student_number` - Student identifier
- `session_id` - PHP session ID
- `client_uuid` - Browser/client identifier
- `user_ip` - Student's IP address
- `user_agent` - Browser user agent
- `answers` - JSON array of student answers
- `final_score` - Total correct answers
- `individual_scores` - JSON array of per-question scores
- `created_at`, `updated_at` - Timestamps

## Quiz JSON Format

Quizzes are stored as JSON files with the following structure:

```json
[
  {
    "id": "question_1",
    "question": "What is 2 + 2?",
    "options": ["3", "4", "5", "6"],
    "correct_answer": "4"
  },
  {
    "id": "question_2",
    "question": "What is the capital of France?",
    "options": ["London", "Berlin", "Paris", "Madrid"],
    "correct_answer": "Paris"
  }
]
```

## API Endpoints

### Authentication
- `GET /login` - Login page
- `POST /login` - Login form submission
- `GET /register` - Registration page
- `POST /register` - Registration form submission
- `POST /logout` - Logout

### Dashboard
- `GET /dashboard` - Professor dashboard
- `GET /create-quiz` - Quiz creation page
- `POST /create-quiz` - Create new quiz
- `POST /quiz/stats` - Get quiz statistics
- `DELETE /quiz/delete` - Delete quiz
- `POST /quiz/regenerate-qr` - Regenerate QR code

### Student Quiz Taking
- `GET /quiz/{quizId}` - Quiz taking page
- `POST /quiz/{quizId}/start` - Start quiz session
- `GET /quiz/{quizId}/question` - Get question
- `POST /quiz/{quizId}/validate` - Validate answer
- `POST /quiz/{quizId}/end` - End quiz session

## Security Features

- **CSRF Protection**: All forms protected against CSRF attacks
- **Input Validation**: Comprehensive validation on all inputs
- **SQL Injection Prevention**: Uses Laravel's Eloquent ORM
- **Session Security**: Secure session management
- **File Upload Security**: Validates JSON files and restricts file types
- **Access Control**: Middleware-based authentication

## File Structure

```
quiz-system-laravel/
├── app/
│   ├── Http/Controllers/
│   │   ├── AuthController.php
│   │   ├── DashboardController.php
│   │   ├── QuizController.php
│   │   └── QuizManagerController.php
│   └── Models/
│       ├── User.php
│       ├── Quiz.php
│       └── QuizResult.php
├── database/migrations/
│   ├── create_users_table.php
│   ├── create_quizzes_table.php
│   └── create_quiz_results_table.php
├── resources/views/
│   ├── layouts/
│   │   └── app.blade.php
│   ├── auth/
│   │   ├── login.blade.php
│   │   └── register.blade.php
│   ├── quiz/
│   │   ├── create.blade.php
│   │   └── show.blade.php
│   └── dashboard.blade.php
├── routes/
│   └── web.php
└── storage/
    ├── app/
    │   ├── uploads/     # Quiz JSON files
    │   └── qr_codes/    # Generated QR codes
    └── public/          # Public storage
```

## Usage

### For Professors

1. **Register/Login**: Create an account or log in
2. **Create Quiz**: 
   - Upload a JSON file with quiz questions
   - Or use one of the provided templates
3. **Share Quiz**: Copy the generated URL or QR code
4. **Monitor Results**: View statistics and student performance

### For Students

1. **Access Quiz**: Use the provided URL or scan QR code
2. **Enter Student Number**: Provide your student identifier
3. **Take Quiz**: Answer questions within the time limit
4. **Submit**: Quiz automatically submits when completed

## Customization

### Adding New Quiz Templates

Edit `resources/views/quiz/create.blade.php` and add new templates to the JavaScript templates object.

### Modifying Quiz Timer

Change the `timeLeft` variable in `resources/views/quiz/show.blade.php`.

### Styling

All styles are included inline in the Blade templates. Modify the CSS within the `<style>` tags to customize the appearance.

## Troubleshooting

### Common Issues

1. **QR Codes Not Generating**
   - Check internet connection (uses external APIs)
   - Verify storage permissions
   - Check Laravel storage configuration

2. **File Upload Issues**
   - Verify `storage/app/uploads` directory exists
   - Check file permissions
   - Ensure JSON file format is correct

3. **Database Connection**
   - Verify database credentials in `.env`
   - Ensure MySQL service is running
   - Check database exists

### Logs

Check Laravel logs in `storage/logs/laravel.log` for detailed error information.

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## Support

For support and questions, please create an issue in the repository or contact the development team.
