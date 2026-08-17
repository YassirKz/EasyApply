<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'nom',
        'fichier',
        'secteur',
        'est_defaut',
    ];

    public function user() { return $this->belongsTo(User::class); }

    protected $casts = [
        'est_defaut' => 'boolean',
    ];
}
