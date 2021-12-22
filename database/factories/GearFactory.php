<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class GearFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'user_id' => User::all()->random()->id,
            'name' => $this->faker->word(),
            'serial_number' => $this->faker->numberBetween(1, 1000000000),
            'unit_price' => $this->faker->numberBetween(5, 100),
            'long_term' => $this->faker->boolean(),
            'lent' => 0
        ];
    }
}
