<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cv_sections', function (Blueprint $table) {
            $table->foreignId('user_id')->after('id')->nullable()->constrained('users')->cascadeOnDelete();
        });

        // Assign all existing rows to user id=1
        \DB::table('cv_sections')->whereNull('user_id')->update(['user_id' => 1]);

        Schema::table('cv_sections', function (Blueprint $table) {
            $table->foreignId('user_id')->after('id')->nullable(false)->change();

            // Section uniqueness is now per-user
            $table->unique(['user_id', 'section']);
        });
    }

    public function down(): void
    {
        Schema::table('cv_sections', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'section']);
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
    }
};
