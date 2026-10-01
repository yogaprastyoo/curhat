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
        Schema::create('counseling_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('curhat_id')->constrained()->cascadeOnDelete();
            $table->foreignId('counselor_id')->constrained('users')->restrictOnDelete();
            $table->date('date');
            $table->time('start_time');
            $table->string('location')->nullable();
            $table->enum('status', ['scheduled', 'completed', 'cancelled'])->default('scheduled');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('counseling_schedules');
    }
};
