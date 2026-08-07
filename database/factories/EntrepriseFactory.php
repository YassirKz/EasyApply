<?php

namespace Database\Factories;

use App\Models\Entreprise;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Entreprise>
 */
class EntrepriseFactory extends Factory
{
    protected $model = Entreprise::class;

    public function definition(): array
    {
        $prefixes = ['Herr', 'Frau', 'Mr.', 'Mrs.', ''];
        $prefix   = $this->faker->randomElement($prefixes);
        $lastName = $this->faker->lastName();

        return [
            'nom'               => $this->faker->company() . ' GmbH',
            'email'             => $this->faker->unique()->safeEmail(),
            'directeur'         => trim("{$prefix} {$lastName}"),
            'telephone'         => $this->faker->phoneNumber(),
            'secteur'           => $this->faker->randomElement(['IT', 'Finanzen', 'Logistik', 'Gesundheit', 'Handel']),
            'texte_personnalise'  => null,
            'est_envoye'         => false,
            'date_envoi'         => null,
            'programmation_envoi' => null,
        ];
    }

    /** State: already sent */
    public function envoye(): static
    {
        return $this->state(fn () => [
            'est_envoye'          => true,
            'date_envoi'          => now(),
            'programmation_envoi' => null,
        ]);
    }

    /** State: scheduled for future or past date */
    public function programme(?\Illuminate\Support\Carbon $at = null): static
    {
        return $this->state(fn () => [
            'est_envoye'          => false,
            'programmation_envoi' => $at ?? now()->addDay(),
        ]);
    }

    /** State: with custom AI text */
    public function avecTexte(): static
    {
        return $this->state(fn () => [
            'texte_personnalise' => 'Das innovative Unternehmen begeistert mich sehr.',
        ]);
    }

    /** State: with female director */
    public function directriceFemme(): static
    {
        return $this->state(fn () => [
            'directeur' => 'Frau ' . $this->faker->lastName(),
        ]);
    }

    /** State: with male director */
    public function directeurHomme(): static
    {
        return $this->state(fn () => [
            'directeur' => 'Herr ' . $this->faker->lastName(),
        ]);
    }
}
