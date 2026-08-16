<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class CvSection extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'section',
        'contenu',
    ];

    /**
     * Global scope: automatically filter by the authenticated user.
     */
    protected static function booted(): void
    {
        static::addGlobalScope('user', function (Builder $builder) {
            if (Auth::check()) {
                $builder->where('cv_sections.user_id', Auth::id());
            }
        });

        // Automatically set user_id on creation
        static::creating(function (CvSection $cvSection) {
            if (empty($cvSection->user_id)) {
                $cvSection->user_id = Auth::id() ?? (User::first()?->id ?? User::factory()->create()->id);
            }
        });
    }

    /**
     * Relationship: a cv section belongs to a user.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
