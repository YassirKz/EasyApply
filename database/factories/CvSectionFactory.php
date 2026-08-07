<?php

namespace Database\Factories;

use App\Models\CvSection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CvSection>
 */
class CvSectionFactory extends Factory
{
    protected $model = CvSection::class;

    public function definition(): array
    {
        return [
            'section' => $this->faker->unique()->randomElement([
                'profil', 'competences', 'praktikum',
                'projekterfahrung', 'ausbildung', 'langues',
                'personliche_daten', 'interessen',
            ]),
            'contenu' => $this->faker->paragraph(),
        ];
    }
}
