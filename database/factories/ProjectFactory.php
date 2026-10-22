<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ProjectFactory extends Factory
{
    public function definition(): array
    {
        return ['jo_no' => 'JO-'.fake()->unique()->numerify('######'), 'part_no' => '000'.fake()->unique()->numerify('######'), 'part_name' => 'Bracket Fiktif', 'model' => 'K2SJ', 'material_thickness_raw' => '1,5', 'material_width_raw' => '1.5'];
    }
}
