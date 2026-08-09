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
        Schema::table('parametres', function (Blueprint $table) {
            $table->time('programme_envoyez')->nullable()->after('valeur');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('parametres', function (Blueprint $table) {
            $table->dropColumn('programme_envoyez');
        });
    }
};
