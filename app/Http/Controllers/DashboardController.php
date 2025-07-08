<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $searchTerm = $request->get('search', '');
        $page = $request->get('page', 1);
        $perPage = 6;

        $query = $user->quizzes()->orderBy('created_at', 'desc');

        if ($searchTerm) {
            $query->where(function($q) use ($searchTerm) {
                $q->where('title', 'like', "%{$searchTerm}%")
                  ->orWhere('quiz_id', 'like', "%{$searchTerm}%");
            });
        }

        $totalQuizzes = $query->count();
        $quizzes = $query->skip(($page - 1) * $perPage)->take($perPage)->get();
        $totalPages = ceil($totalQuizzes / $perPage);

        return view('dashboard', compact('user', 'quizzes', 'totalQuizzes', 'totalPages', 'page', 'searchTerm'));
    }
}
