<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop all existing tables
        Schema::dropIfExists('admin_actions');
        Schema::dropIfExists('admin_limits');
        Schema::dropIfExists('mac_records');
        Schema::dropIfExists('posted_reviews');
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('secure_sessions');
        Schema::dropIfExists('user_activities');
        Schema::dropIfExists('reviews');
        Schema::dropIfExists('otps');
        Schema::dropIfExists('user_profiles');
        Schema::dropIfExists('key_pairs');
        Schema::dropIfExists('two_factor_tokens');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
        Schema::dropIfExists('jobs');
        Schema::dropIfExists('cache');

        // 1. Users Table
        Schema::create('users', function (Blueprint $table) {
            $table->id('user_id');
            $table->longText('username')->unique();  // Encrypted
            $table->longText('email')->unique();     // Encrypted
            $table->string('email_hash')->unique();  // For lookups
            $table->longText('contact_info');        // Encrypted
            $table->string('contact_hash')->unique(); // For lookups
            $table->longText('password_hash');       // Hashed + salted
            $table->string('password_salt');
            $table->enum('role', ['admin', 'user'])->default('user');
            $table->timestamps();
        });

        // 2. OTP / Two-Factor Table
        Schema::create('otps', function (Blueprint $table) {
            $table->id('otp_id');
            $table->foreignId('user_id')->constrained('users', 'user_id')->cascadeOnDelete();
            $table->string('otp_code', 64)->index();   // SHA-256 hashed
            $table->dateTime('generated_at');
            $table->dateTime('expiry_time')->index();
            $table->boolean('is_used')->default(false)->index();
            $table->timestamps();

            $table->index(['user_id', 'otp_code', 'is_used', 'expiry_time'], 'otp_user_code_status_expiry_idx');
        });

        // 3. Key Management Table
        Schema::create('key_pairs', function (Blueprint $table) {
            $table->id('key_id');
            $table->enum('key_type', ['rsa', 'ecc']);
            $table->longText('public_key');         // Encrypted
            $table->longText('private_key');        // Encrypted
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('expiry_date')->nullable();
            $table->enum('status', ['active', 'rotated'])->default('active');
        });

        // 4. User Profile Table
        Schema::create('user_profiles', function (Blueprint $table) {
            $table->id('profile_id');
            $table->foreignId('user_id')->constrained('users', 'user_id')->cascadeOnDelete();
            $table->longText('profile_data');  // Encrypted
            $table->timestamps();
        });

        // 5. Reviews Table
        Schema::create('reviews', function (Blueprint $table) {
            $table->id('review_id');
            $table->foreignId('user_id')->constrained('users', 'user_id')->cascadeOnDelete();
            $table->longText('review_text');   // Encrypted
            $table->integer('review_score');
            $table->string('location')->nullable();
            $table->string('city');
            $table->dateTime('created_at')->useCurrent();
            $table->enum('status', ['pending', 'approved', 'wait', 'rejected'])->default('pending');
            $table->integer('edit_count')->default(0);
            $table->integer('max_edit_limit')->default(3);
            $table->dateTime('updated_at')->useCurrent();
        });

        // 6. User Activity Table
        Schema::create('user_activities', function (Blueprint $table) {
            $table->id('activity_id');
            $table->foreignId('user_id')->constrained('users', 'user_id')->cascadeOnDelete();
            $table->integer('remaining_reviews')->default(5);
            $table->dateTime('last_post_time')->nullable();
            $table->dateTime('cooldown_end_time')->nullable();
            $table->timestamps();
        });

        // 7. Admin Actions Table
        Schema::create('admin_actions', function (Blueprint $table) {
            $table->id('action_id');
            $table->foreignId('admin_id')->constrained('users', 'user_id')->cascadeOnDelete();
            $table->foreignId('review_id')->constrained('reviews', 'review_id')->cascadeOnDelete();
            $table->enum('status', ['approved', 'rejected']);
            $table->dateTime('action_time')->useCurrent();
            $table->timestamps();
        });

        // 8. Admin Limit Table
        Schema::create('admin_limits', function (Blueprint $table) {
            $table->id('admin_limit_id');
            $table->foreignId('admin_id')->constrained('users', 'user_id')->cascadeOnDelete();
            $table->integer('current_pending_count')->default(0);
            $table->integer('max_pending_limit')->default(5);
            $table->enum('status', ['active', 'wait'])->default('active');
            $table->timestamps();
        });

        // 9. MAC Table (Integrity)
        Schema::create('mac_records', function (Blueprint $table) {
            $table->id('mac_id');
            $table->integer('reference_id');
            $table->longText('mac_value');
            $table->enum('algorithm', ['hmac', 'cbcmac']);
            $table->dateTime('created_at')->useCurrent();
        });

        // 10. Session Table
        Schema::create('session_table', function (Blueprint $table) {
            $table->id('session_id');
            $table->foreignId('user_id')->constrained('users', 'user_id')->cascadeOnDelete();
            $table->longText('token');  // Encrypted
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('expiry_time');
            $table->dateTime('last_activity')->useCurrent();
        });

        // 11. Posted Reviews Table
        Schema::create('posted_reviews', function (Blueprint $table) {
            $table->id('post_id');
            $table->foreignId('review_id')->constrained('reviews', 'review_id')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users', 'user_id')->cascadeOnDelete();
            $table->longText('encrypted_review');  // Encrypted
            $table->dateTime('posted_at')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posted_reviews');
        Schema::dropIfExists('session_table');
        Schema::dropIfExists('mac_records');
        Schema::dropIfExists('admin_limits');
        Schema::dropIfExists('admin_actions');
        Schema::dropIfExists('user_activities');
        Schema::dropIfExists('reviews');
        Schema::dropIfExists('user_profiles');
        Schema::dropIfExists('key_pairs');
        Schema::dropIfExists('otps');
        Schema::dropIfExists('users');
    }
};
