<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom',
        'fichier',
        'est_defaut',
    ];

    protected $casts = [
        'est_defaut' => 'boolean',
    ];
}
