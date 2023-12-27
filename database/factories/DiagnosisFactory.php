<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Diagnosis;

class DiagnosisFactory extends Factory
{
    protected $model = Diagnosis::class;

    public function definition()
    {
        return [
            'name' => $this->faker->sentence(3),
            'description' => $this->faker->text,
        ];
    }
}
