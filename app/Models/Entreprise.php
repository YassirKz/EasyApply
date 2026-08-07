<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Entreprise extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom',
        'email',
        'directeur',
        'telephone',
        'secteur',
        'texte_personnalise',
        'est_envoye',
        'date_envoi',
        'programmation_envoi',
    ];

    protected $casts = [
        'est_envoye'          => 'boolean',
        'date_envoi'          => 'datetime',
        'programmation_envoi' => 'datetime',
    ];
}
