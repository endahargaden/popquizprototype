<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use App\Models\QuizResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class QuizController extends Controller
{
    public function show($quizId)
    {
        $quiz = Quiz::where('quiz_id', $quizId)->firstOrFail();
        
        $jsonPath = storage_path('app/uploads/' . $quiz->json_file);
        if (!file_exists($jsonPath)) {
            abort(404, 'Quiz file not found');
        }

        $questions = json_decode(file_get_contents($jsonPath), true);
        if (!$questions) {
            abort(500, 'Invalid quiz data');
        }

        return view('quiz.show', compact('quiz', 'questions'));
    }

    public function startSession(Request $request, $quizId)
    {
        $request->validate([
            'studentNumber' => 'required|string',
            'clientUuid' => 'required|string',
        ]);

        $quiz = Quiz::where('quiz_id', $quizId)->firstOrFail();
        
        // Check if student has already attempted this quiz
        $existingAttempt = QuizResult::where('quiz_id', $quizId)
            ->where('student_number', $request->studentNumber)
            ->first();

        if ($existingAttempt) {
            return response()->json([
                'status' => 'error',
                'message' => 'You have already attempted this quiz. Each student can only take a quiz once.',
                'code' => 'ALREADY_ATTEMPTED'
            ], 403);
        }

        // Store session data
        session([
            'quiz_state' => 'active',
            'student_number' => $request->studentNumber,
            'client_uuid' => $request->clientUuid,
            'quiz_id' => $quizId,
            'start_time' => time(),
            'user_ip' => $request->ip(),
            'session_id' => session()->getId(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Quiz session started.'
        ]);
    }

    public function getQuestion(Request $request, $quizId)
    {
        if (session('quiz_state') !== 'active') {
            return response()->json([
                'status' => 'terminated',
                'message' => 'Your quiz session has ended. Reason: ' . (session('quiz_terminated_reason') ?? 'Unknown')
            ]);
        }

        $quiz = Quiz::where('quiz_id', $quizId)->firstOrFail();
        $jsonPath = storage_path('app/uploads/' . $quiz->json_file);
        $questions = json_decode(file_get_contents($jsonPath), true);

        $questionIndex = $request->get('question_index', 0);

        if (isset($questions[$questionIndex])) {
            $questionData = $questions[$questionIndex];
            unset($questionData['correct_answer']);
            
            return response()->json([
                'status' => 'success',
                'question' => $questionData
            ]);
        }

        return response()->json([
            'status' => 'end',
            'message' => 'No more questions.'
        ]);
    }

    public function validateAnswer(Request $request, $quizId)
    {
        if (session('quiz_state') !== 'active') {
            return response()->json([
                'status' => 'terminated',
                'message' => 'Your quiz session has ended.'
            ]);
        }

        $request->validate([
            'questionId' => 'required',
            'selectedAnswer' => 'required',
        ]);

        $quiz = Quiz::where('quiz_id', $quizId)->firstOrFail();
        $jsonPath = storage_path('app/uploads/' . $quiz->json_file);
        $questions = json_decode(file_get_contents($jsonPath), true);

        $isCorrect = false;
        $correctAnswerText = null;

        foreach ($questions as $question) {
            if ($question['id'] == $request->questionId) {
                $correctAnswerText = $question['correct_answer'];
                if ($request->selectedAnswer === $question['correct_answer']) {
                    $isCorrect = true;
                }
                break;
            }
        }

        return response()->json([
            'status' => 'validation_result',
            'isCorrect' => $isCorrect,
            'correctAnswerText' => $correctAnswerText
        ]);
    }

    public function endSession(Request $request, $quizId)
    {
        $request->validate([
            'reason' => 'required|string',
            'answers' => 'required|array',
        ]);

        $quiz = Quiz::where('quiz_id', $quizId)->firstOrFail();
        $jsonPath = storage_path('app/uploads/' . $quiz->json_file);
        $questions = json_decode(file_get_contents($jsonPath), true);

        session(['quiz_state' => 'terminated']);
        session(['quiz_terminated_reason' => $request->reason]);

        $correctAnswersCount = 0;
        $numQuestions = count($questions);
        $questionScores = array_fill(0, $numQuestions, 0);

        $questionIdToIndexMap = [];
        foreach ($questions as $idx => $q) {
            $questionIdToIndexMap[$q['id']] = $idx;
        }

        foreach ($request->answers as $answer) {
            $questionId = $answer['questionId'] ?? null;
            
            if ($questionId !== null && isset($questionIdToIndexMap[$questionId])) {
                $qIndex = $questionIdToIndexMap[$questionId];
                $q = $questions[$qIndex];

                if (isset($answer['selectedAnswer']) && 
                    $answer['selectedAnswer'] !== 'Time Expired / No Answer' && 
                    $answer['selectedAnswer'] === $q['correct_answer']) {
                    $correctAnswersCount++;
                    $questionScores[$qIndex] = 1;
                }
            }
        }

        QuizResult::create([
            'quiz_id' => $quizId,
            'student_number' => session('student_number', 'N/A'),
            'session_id' => session('session_id', 'N/A'),
            'client_uuid' => session('client_uuid', 'N/A'),
            'user_ip' => session('user_ip', 'N/A'),
            'user_agent' => session('user_agent', 'N/A'),
            'answers' => $request->answers,
            'final_score' => $correctAnswersCount,
            'individual_scores' => $questionScores,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Quiz session ended and answers logged.'
        ]);
    }
}
