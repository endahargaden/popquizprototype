<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use App\Models\QuizResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class QuizManagerController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function showCreateForm()
    {
        return view('quiz.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'quiz_data' => 'required|json',
        ]);

        $quizData = json_decode($request->quiz_data, true);
        
        if (!$this->validateQuizStructure($quizData)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid quiz structure'
            ]);
        }

        $quizId = $this->generateUniqueQuizId();
        $filename = $this->generateUniqueFilename();
        $filePath = 'uploads/' . $filename;

        // Ensure uploads directory exists
        if (!Storage::exists('uploads')) {
            Storage::makeDirectory('uploads');
        }

        $jsonContent = json_encode($quizData, JSON_PRETTY_PRINT);

        try {
            if (!Storage::put($filePath, $jsonContent)) {
                Log::error('Failed to save quiz file: ' . $filePath);
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to save quiz file'
                ]);
            }

            // Verify file was created
            if (!Storage::exists($filePath)) {
                Log::error('Quiz file was not created: ' . $filePath);
                return response()->json([
                    'success' => false,
                    'message' => 'Quiz file was not created'
                ]);
            }

            $title = $this->extractQuizTitle($quizData);

            $quiz = Quiz::create([
                'quiz_id' => $quizId,
                'user_id' => Auth::id(),
                'title' => $title,
                'json_file' => $filename,
            ]);

            $qrCodePath = $this->generateQRCode($quizId);

            Log::info('Quiz created successfully', [
                'quiz_id' => $quizId,
                'filename' => $filename,
                'qr_code' => $qrCodePath
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Quiz created successfully' . ($qrCodePath ? '' : ' (QR code generation failed)'),
                'quiz_id' => $quizId,
                'url' => route('quiz.show', $quizId),
                'qr_code' => $qrCodePath,
            ]);

        } catch (\Exception $e) {
            Log::error('Error creating quiz: ' . $e->getMessage());
            
            // Clean up any partially created files
            if (Storage::exists($filePath)) {
                Storage::delete($filePath);
            }
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while creating the quiz: ' . $e->getMessage()
            ]);
        }
    }

    public function getStats(Request $request)
    {
        $request->validate([
            'quiz_id' => 'required|string',
        ]);

        $quiz = Quiz::where('quiz_id', $request->quiz_id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $results = QuizResult::where('quiz_id', $request->quiz_id)
            ->orderBy('created_at', 'desc')
            ->get();

        $stats = $this->calculateQuizStats($results, $quiz->json_file);

        return response()->json([
            'success' => true,
            'quiz' => $quiz,
            'results' => $results,
            'stats' => $stats,
        ]);
    }

    public function delete(Request $request)
    {
        $request->validate([
            'quiz_id' => 'required|string',
        ]);

        $quiz = Quiz::where('quiz_id', $request->quiz_id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        // Delete JSON file
        Storage::delete('uploads/' . $quiz->json_file);

        // Delete QR code
        $qrFile = storage_path('app/qr_codes/quiz_' . $quiz->quiz_id . '.png');
        if (file_exists($qrFile)) {
            unlink($qrFile);
        }

        $quiz->delete();

        return response()->json([
            'success' => true,
            'message' => 'Quiz deleted successfully'
        ]);
    }

    public function regenerateQR(Request $request)
    {
        $request->validate([
            'quiz_id' => 'required|string',
        ]);

        $quiz = Quiz::where('quiz_id', $request->quiz_id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        // Delete existing QR code
        $qrFile = storage_path('app/qr_codes/quiz_' . $quiz->quiz_id . '.png');
        if (file_exists($qrFile)) {
            unlink($qrFile);
        }

        $qrPath = $this->generateQRCode($quiz->quiz_id);

        return response()->json([
            'success' => (bool)$qrPath,
            'message' => $qrPath ? 'QR code regenerated successfully' : 'Failed to generate QR code'
        ]);
    }

    private function validateQuizStructure($data)
    {
        if (!is_array($data) || empty($data)) {
            return false;
        }

        foreach ($data as $question) {
            if (!isset($question['id']) || !isset($question['question']) || 
                !isset($question['options']) || !isset($question['correct_answer'])) {
                return false;
            }

            if (!is_array($question['options']) || count($question['options']) < 2) {
                return false;
            }

            if (!in_array($question['correct_answer'], $question['options'])) {
                return false;
            }
        }

        return true;
    }

    private function generateUniqueQuizId()
    {
        do {
            $quizId = Str::random(32);
        } while (Quiz::where('quiz_id', $quizId)->exists());

        return $quizId;
    }

    private function generateUniqueFilename()
    {
        $timestamp = time();
        $random = Str::random(16);
        return "quiz_{$timestamp}_{$random}.json";
    }

    private function extractQuizTitle($quizData)
    {
        if (!empty($quizData[0]['question'])) {
            $firstQuestion = $quizData[0]['question'];
            return Str::limit($firstQuestion, 50);
        }
        return 'Untitled Quiz';
    }

    private function generateQRCode($quizId)
    {
        $quizUrl = route('quiz.show', $quizId);
        $qrCodePath = 'qr_codes/quiz_' . $quizId . '.png';

        // Ensure QR directory exists
        if (!Storage::exists('qr_codes')) {
            Storage::makeDirectory('qr_codes');
        }

        // Try multiple QR code generation methods
        $qrImage = null;
        $context = stream_context_create([
            'http' => [
                'timeout' => 15,
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'follow_location' => true,
                'max_redirects' => 3
            ]
        ]);

        // Method 1: QR Server API
        $qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" . urlencode($quizUrl) . "&format=png";
        $qrImage = @file_get_contents($qrUrl, false, $context);

        // Method 2: Google Charts API (fallback)
        if ($qrImage === false || strlen($qrImage) < 100) {
            $qrUrl2 = "https://chart.googleapis.com/chart?chs=300x300&cht=qr&chl=" . urlencode($quizUrl) . "&choe=UTF-8";
            $qrImage = @file_get_contents($qrUrl2, false, $context);
        }

        // Method 3: QR Code Monkey API (another fallback)
        if ($qrImage === false || strlen($qrImage) < 100) {
            $qrUrl3 = "https://www.qrcode-monkey.com/api/qr/custom?size=300&data=" . urlencode($quizUrl);
            $qrImage = @file_get_contents($qrUrl3, false, $context);
        }

        // If external APIs work, save the image using Laravel Storage
        if ($qrImage !== false && strlen($qrImage) > 100) {
            try {
                if (Storage::put($qrCodePath, $qrImage)) {
                    return $qrCodePath;
                }
            } catch (\Exception $e) {
                Log::error('Failed to save QR code: ' . $e->getMessage());
            }
        }

        // If all external APIs fail, create a simple text-based QR code placeholder
        Log::warning('External QR code generation failed for quiz: ' . $quizId);
        return null;
    }

    private function calculateQuizStats($results, $jsonFile)
    {
        if ($results->isEmpty()) {
            return [
                'total_attempts' => 0,
                'average_score' => 0,
                'easiest_question' => null,
                'hardest_question' => null
            ];
        }

        $filePath = 'uploads/' . $jsonFile;
        if (!Storage::exists($filePath)) {
            Log::error('Quiz file not found for stats: ' . $filePath);
            return [
                'total_attempts' => 0,
                'average_score' => 0,
                'easiest_question' => null,
                'hardest_question' => null
            ];
        }

        $quizData = json_decode(Storage::get($filePath), true);
        if (!$quizData) {
            Log::error('Invalid quiz data for stats: ' . $filePath);
            return [
                'total_attempts' => 0,
                'average_score' => 0,
                'easiest_question' => null,
                'hardest_question' => null
            ];
        }

        $numQuestions = count($quizData);

        $questionStats = array_fill(0, $numQuestions, ['correct' => 0, 'total' => 0]);

        foreach ($results as $result) {
            $individualScores = $result->individual_scores;
            if ($individualScores) {
                foreach ($individualScores as $qIndex => $score) {
                    if (isset($questionStats[$qIndex])) {
                        $questionStats[$qIndex]['total']++;
                        if ($score == 1) {
                            $questionStats[$qIndex]['correct']++;
                        }
                    }
                }
            }
        }

        $totalAttempts = $results->count();
        $totalScores = $results->sum('final_score');
        $averageScore = $totalAttempts > 0 ? round($totalScores / $totalAttempts, 2) : 0;

        $questionDifficulties = [];
        foreach ($questionStats as $qIndex => $stats) {
            if ($stats['total'] > 0) {
                $difficulty = $stats['correct'] / $stats['total'];
                $questionDifficulties[$qIndex] = $difficulty;
            }
        }

        $easiestQuestion = null;
        $hardestQuestion = null;

        if (!empty($questionDifficulties)) {
            $easiestIndex = array_keys($questionDifficulties, max($questionDifficulties))[0];
            $hardestIndex = array_keys($questionDifficulties, min($questionDifficulties))[0];

            $easiestQuestion = [
                'index' => $easiestIndex + 1,
                'difficulty' => round($questionDifficulties[$easiestIndex] * 100, 1),
                'question' => $quizData[$easiestIndex]['question']
            ];

            $hardestQuestion = [
                'index' => $hardestIndex + 1,
                'difficulty' => round($questionDifficulties[$hardestIndex] * 100, 1),
                'question' => $quizData[$hardestIndex]['question']
            ];
        }

        return [
            'total_attempts' => $totalAttempts,
            'average_score' => $averageScore,
            'easiest_question' => $easiestQuestion,
            'hardest_question' => $hardestQuestion,
            'question_stats' => $questionStats
        ];
    }
}
