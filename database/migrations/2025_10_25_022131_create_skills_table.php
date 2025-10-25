<?php

// database/migrations/xxxx_xx_xx_xxxxxx_create_skills_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('skills', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            // parent skill id (subcategory → parent)
            $table->foreignId('skill_id')->nullable()
                ->constrained('skills')
                ->nullOnDelete();
            $table->string('emoji', 8)->nullable();
            $table->string('icon_path')->nullable();     // store emoji, asset path, or icon name
            $table->boolean('is_it_popular')->default(false);
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('skills');
    }
};
