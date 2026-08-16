<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('entreprises', function (Blueprint $table) {
            // Add user_id column after id
            $table->foreignId('user_id')->after('id')->nullable()->constrained('users')->cascadeOnDelete();
        });

        // Assign all existing rows to user id=1 (first user)
        \DB::table('entreprises')->whereNull('user_id')->update(['user_id' => 1]);

        Schema::table('entreprises', function (Blueprint $table) {
            // Make non-nullable now that all rows have a value
            $table->foreignId('user_id')->after('id')->nullable(false)->change();

            // Email uniqueness is now per-user (not global)
            $table->dropUnique(['email']);
            $table->unique(['user_id', 'email']);
        });
    }

    public function down(): void
    {
        Schema::table('entreprises', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'email']);
            $table->unique(['email']);
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
    }
};
