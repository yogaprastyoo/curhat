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
        Schema::create('curhats', function (Blueprint $table) {
            $table->id();
            $table->string('curhat_number')->unique();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('curhat_categories')->restrictOnDelete();
            $table->string('title');
            $table->longText('message');
            $table->enum('privacy_scope', ['counselor', 'counselor_psychologist'])->default('counselor');
            $table->enum('counseling_request', ['no', 'yes', 'unsure'])->default('no');
            $table->enum('status', [
                'submitted',
                'reviewed',
                'responded',
                'scheduled',
                'in_counseling',
                'completed'
            ])->default('submitted');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('curhats');
    }
};
