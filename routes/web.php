<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\QuizController;
use App\Http\Controllers\QuizManagerController;
use Illuminate\Support\Facades\Route;

// Authentication routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected routes
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/create-quiz', [QuizManagerController::class, 'showCreateForm'])->name('quiz.create');
    Route::post('/create-quiz', [QuizManagerController::class, 'store'])->name('quiz.store');
    Route::post('/quiz/stats', [QuizManagerController::class, 'getStats'])->name('quiz.stats');
    Route::delete('/quiz/delete', [QuizManagerController::class, 'delete'])->name('quiz.delete');
    Route::post('/quiz/regenerate-qr', [QuizManagerController::class, 'regenerateQR'])->name('quiz.regenerate-qr');
});

// Quiz taking routes (public)
Route::get('/quiz/{quizId}', [QuizController::class, 'show'])->name('quiz.show');
Route::post('/quiz/{quizId}/start', [QuizController::class, 'startSession'])->name('quiz.start');
Route::get('/quiz/{quizId}/question', [QuizController::class, 'getQuestion'])->name('quiz.question');
Route::post('/quiz/{quizId}/validate', [QuizController::class, 'validateAnswer'])->name('quiz.validate');
Route::post('/quiz/{quizId}/end', [QuizController::class, 'endSession'])->name('quiz.end');

// Redirect root to dashboard if authenticated, otherwise to login
Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});
