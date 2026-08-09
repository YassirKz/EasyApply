<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistoriqueEmail extends Model
{
    use HasFactory;

    protected $table = 'historique_emails';

    protected $fillable = [
        'entreprise_id',
        'type',
        'objet',
        'contenu',
        'date_envoi',
        'statut',
    ];

    protected $casts = [
        'date_envoi' => 'datetime',
    ];

    public function entreprise()
    {
        return $this->belongsTo(Entreprise::class);
    }
}
