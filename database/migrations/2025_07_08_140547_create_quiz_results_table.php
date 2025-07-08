<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('quiz_results', function (Blueprint $table) {
            $table->id();
            $table->string('quiz_id', 32);
            $table->string('student_number');
            $table->string('session_id');
            $table->string('client_uuid');
            $table->string('user_ip', 45);
            $table->text('user_agent');
            $table->json('answers');
            $table->integer('final_score');
            $table->json('individual_scores');
            $table->timestamps();
            
            $table->foreign('quiz_id')->references('quiz_id')->on('quizzes')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quiz_results');
    }
};
