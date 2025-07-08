<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuizResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'quiz_id',
        'student_number',
        'session_id',
        'client_uuid',
        'user_ip',
        'user_agent',
        'answers',
        'final_score',
        'individual_scores',
    ];

    protected $casts = [
        'answers' => 'array',
        'individual_scores' => 'array',
    ];

    public function quiz()
    {
        return $this->belongsTo(Quiz::class, 'quiz_id', 'quiz_id');
    }
}
