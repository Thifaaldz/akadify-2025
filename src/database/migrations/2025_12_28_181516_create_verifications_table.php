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
    Schema::create('verifications', function (Blueprint $table) {
        $table->id();

        $table->foreignId('student_id')
            ->constrained()
            ->cascadeOnDelete();

        $table->string('ijazah_path');

        $table->enum('status', [
            'PENDING_OCR',
            'PROCESSING',
            'VERIFIED',
            'REJECTED'
        ])->default('PENDING_OCR');

        $table->json('reason')->nullable();

        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('verifications');
    }
};
