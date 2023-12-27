<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Diagnosis;
use App\Models\Patient;
use Faker\Factory as Faker;

class DiagnosesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        Diagnosis::factory()->count(5)->create()->each(function ($diagnosis) use ($faker) {
            $patientIds = Patient::pluck('id')->shuffle()->take(rand(1, 3))->toArray();

            $diagnosis->patients()->attach($patientIds, [
                'observation' => $faker->paragraph(rand(1, 3)),
                'creation_date' => now()->subDays(rand(1, 365))->format('Y-m-d H:i:s'),
            ]);
        });
    }
}
