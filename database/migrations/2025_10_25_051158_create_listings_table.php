<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('listings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('desired_skill_id')->constrained('skills')->cascadeOnDelete();
            $table->text('description')->nullable(); // what the user wants to learn
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('listings');
    }
};
