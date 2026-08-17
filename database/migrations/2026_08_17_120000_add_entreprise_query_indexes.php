<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('entreprises', function (Blueprint $table): void {
            $table->index(['user_id', 'est_envoye', 'programmation_envoi'], 'entreprises_dispatch_index');
            $table->index(['user_id', 'date_envoi'], 'entreprises_sent_at_index');
            $table->index(['user_id', 'statut_reponse'], 'entreprises_response_index');
        });
    }
    public function down(): void
    {
        Schema::table('entreprises', function (Blueprint $table): void {
            $table->dropIndex('entreprises_dispatch_index');
            $table->dropIndex('entreprises_sent_at_index');
            $table->dropIndex('entreprises_response_index');
        });
    }
};
