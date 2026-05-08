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
        Schema::table('posts', function (Blueprint $table) {
            if (!Schema::hasColumn('posts', 'signature')) {
                $table->json('signature')->nullable()->after('status');
            }
            if (!Schema::hasColumn('posts', 'mac')) {
                $table->string('mac')->nullable()->after('signature');
            }
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn(['signature', 'mac']);
        });
    }
};
