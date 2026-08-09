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
        'offre_texte',
        'est_envoye',
        'date_envoi',
        'programmation_envoi',
        'statut_reponse',
        'date_reponse',
        'notes',
        'date_relance',
        'nb_relances',
    ];

    protected $casts = [
        'est_envoye'          => 'boolean',
        'date_envoi'          => 'datetime',
        'programmation_envoi' => 'datetime',
        'date_reponse'        => 'datetime',
        'date_relance'        => 'datetime',
        'nb_relances'        => 'integer',
    ];

    public function historiqueEmails()
    {
        return $this->hasMany(HistoriqueEmail::class);
    }
}
