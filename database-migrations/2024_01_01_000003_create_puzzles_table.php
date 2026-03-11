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
        Schema::create('puzzles', function (Blueprint $table) {
            $table->id();
            $table->string('type', 50); // symbol_cipher, ritual_pattern, etc.
            $table->integer('sequence_order'); // 1-10
            $table->string('title', 255);
            $table->text('description');
            $table->json('solution_data'); // Stores puzzle-specific solution data
            $table->timestamps();
            
            // Index for ordering
            $table->index('sequence_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('puzzles');
    }
};
