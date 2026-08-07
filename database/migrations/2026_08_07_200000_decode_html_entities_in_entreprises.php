<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the data migration: decode HTML entities stored in entreprise fields.
     */
    public function up(): void
    {
        // Process in chunks to avoid memory issues
        DB::table('entreprises')->orderBy('id')->chunk(100, function ($rows) {
            foreach ($rows as $row) {
                $updates = [];
                $fields = ['nom', 'directeur', 'secteur', 'texte_personnalise'];

                foreach ($fields as $field) {
                    if (!isset($row->{$field})) continue;

                    $original = (string) $row->{$field};
                    $decoded = html_entity_decode($original, ENT_QUOTES, 'UTF-8');
                    $clean = trim(strip_tags($decoded));

                    if ($clean !== $original) {
                        $updates[$field] = $clean;
                    }
                }

                if (!empty($updates)) {
                    DB::table('entreprises')->where('id', $row->id)->update($updates);
                }
            }
        });
    }

    /**
     * Reverse the migration (no-op).
     */
    public function down(): void
    {
        // Nothing to rollback safely for content-decoding.
    }
};
