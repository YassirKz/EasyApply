<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Parametre extends Model
{
    use HasFactory;

    protected $table = 'parametres';

    protected $fillable = [
        'user_id',
        'cle',
        'valeur',
    ];

    /**
     * Global scope: automatically filter by the authenticated user.
     */
    protected static function booted(): void
    {
        static::addGlobalScope('user', function (Builder $builder) {
            if (Auth::check()) {
                $builder->where('parametres.user_id', Auth::id());
            }
        });

        // Automatically set user_id on creation
        static::creating(function (Parametre $parametre) {
            if (empty($parametre->user_id)) {
                $parametre->user_id = Auth::id() ?? (User::first()?->id ?? User::factory()->create()->id);
            }
        });
    }

    /**
     * Relationship: a parametre belongs to a user.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
