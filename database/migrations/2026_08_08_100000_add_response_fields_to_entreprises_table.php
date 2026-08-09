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
        Schema::table('entreprises', function (Blueprint $table) {
            $table->enum('statut_reponse', [
                'en_attente',
                'refuse',
                'accepte',
                'entretien_programme',
                'en_cours',
                'relance_envoyee',
            ])->default('en_attente')->after('est_envoye');
            $table->dateTime('date_reponse')->nullable()->after('statut_reponse');
            $table->text('notes')->nullable()->after('date_reponse');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('entreprises', function (Blueprint $table) {
            $table->dropColumn(['statut_reponse', 'date_reponse', 'notes']);
        });
    }
};
