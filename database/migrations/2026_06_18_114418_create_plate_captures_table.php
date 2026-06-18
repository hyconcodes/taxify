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
        Schema::create('plate_captures', function (Blueprint $table) {
            $table->id();
            $table->string('plate_number')->nullable()->index();
            $table->string('image_path');
            $table->decimal('confidence', 5, 2)->nullable();
            $table->boolean('is_matched')->default(false);
            $table->foreignId('captured_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('captured_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plate_captures');
    }
};
