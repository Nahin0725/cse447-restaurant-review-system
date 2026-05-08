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
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users', 'user_id')->cascadeOnDelete();
            $table->longText('restaurant_name')->nullable();     // Encrypted
            $table->longText('title')->nullable();              // Encrypted
            $table->longText('body')->nullable();               // Encrypted
            $table->string('restaurant_name_encrypted')->nullable();
            $table->string('title_encrypted')->nullable();
            $table->string('body_encrypted')->nullable();
            $table->string('city_encrypted')->nullable();
            $table->integer('review_score')->nullable();
            $table->string('city')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->longText('signature')->nullable();          // ECC signature
            $table->string('mac')->nullable();                  // HMAC value
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
