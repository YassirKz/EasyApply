<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Entreprise extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
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
        'nb_relances'         => 'integer',
    ];

    /**
     * Global scope: automatically filter by the authenticated user.
     * Every query on Entreprise is scoped to the current user.
     */
    protected static function booted(): void
    {
        static::addGlobalScope('user', function (Builder $builder) {
            if (Auth::check()) {
                $builder->where('entreprises.user_id', Auth::id());
            }
        });

        // Automatically set user_id on creation
        static::creating(function (Entreprise $entreprise) {
            if (empty($entreprise->user_id)) {
                $entreprise->user_id = Auth::id() ?? (User::first()?->id ?? User::factory()->create()->id);
            }
        });
    }

    /**
     * Relationship: an entreprise belongs to a user.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship: an entreprise has many historique emails.
     */
    public function historiqueEmails()
    {
        return $this->hasMany(HistoriqueEmail::class);
    }
}
