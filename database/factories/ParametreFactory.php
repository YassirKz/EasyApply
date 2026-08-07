<?php

namespace Database\Factories;

use App\Models\Parametre;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Parametre>
 */
class ParametreFactory extends Factory
{
    protected $model = Parametre::class;

    public function definition(): array
    {
        return [
            'cle'    => $this->faker->unique()->word(),
            'valeur' => $this->faker->sentence(),
        ];
    }

    /** State: motivation letter template */
    public function modelLettre(): static
    {
        return $this->state(fn () => [
            'cle'    => 'modele_lettre',
            'valeur' => "Sehr geehrte(r) Frau/Herr [NOM_DIRECTEUR],\n\n[TEXTE_PERSONNALISE]\n\nMit freundlichen Grüßen,\nYassir Kezzi",
        ]);
    }
}
