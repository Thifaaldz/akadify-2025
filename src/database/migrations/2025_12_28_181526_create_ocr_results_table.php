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
    Schema::create('ocr_results', function (Blueprint $table) {
        $table->id();

        $table->foreignId('verification_id')
            ->constrained()
            ->cascadeOnDelete();

        $table->longText('raw_text');

        $table->string('nisn')->nullable();
        $table->string('nama')->nullable();
        $table->string('tahun_lulus')->nullable();
        $table->string('sekolah')->nullable();

        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ocr_results');
    }
};
