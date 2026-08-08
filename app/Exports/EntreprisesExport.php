<?php

namespace App\Exports;

use App\Models\Entreprise;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class EntreprisesExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return Entreprise::orderBy('nom')->get();
    }

    public function headings(): array
    {
        return [
            'Nom',
            'Email',
            'Directeur',
            'Téléphone',
            'Secteur',
            'Statut',
            'Date envoi',
            'Créé le',
            'Mis à jour le',
        ];
    }

    public function map($entreprise): array
    {
        return [
            $entreprise->nom,
            $entreprise->email,
            $entreprise->directeur,
            $entreprise->telephone,
            $entreprise->secteur,
            $entreprise->est_envoye ? 'Envoyé' : 'En attente',
            $entreprise->date_envoi?->format('Y-m-d H:i:s'),
            $entreprise->created_at?->format('Y-m-d H:i:s'),
            $entreprise->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
