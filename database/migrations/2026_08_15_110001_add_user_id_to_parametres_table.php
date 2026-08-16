<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('parametres', function (Blueprint $table) {
            $table->foreignId('user_id')->after('id')->nullable()->constrained('users')->cascadeOnDelete();
        });

        // Assign all existing rows to user id=1
        \DB::table('parametres')->whereNull('user_id')->update(['user_id' => 1]);

        Schema::table('parametres', function (Blueprint $table) {
            $table->foreignId('user_id')->after('id')->nullable(false)->change();

            // Replace global unique on 'cle' with per-user unique (user_id, cle)
            $table->dropUnique(['cle']);
            $table->unique(['user_id', 'cle']);
        });
    }

    public function down(): void
    {
        Schema::table('parametres', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'cle']);
            $table->unique(['cle']);
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
    }
};
