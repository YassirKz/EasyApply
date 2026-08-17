<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('documents', function (Blueprint $table) {
            $table->foreignId('user_id')->after('id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('secteur')->nullable()->after('fichier');
            $table->index(['user_id', 'secteur']);
        });
    }
    public function down(): void { Schema::table('documents', function (Blueprint $table) { $table->dropIndex(['user_id','secteur']); $table->dropForeign(['user_id']); $table->dropColumn(['user_id','secteur']); }); }
};
