<?php

namespace Database\Factories;

use App\Models\Property;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Property>
 */
class PropertyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = Property::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory()->admin(),
            'title' => fake()->streetName().' Apt',
            'description' => fake()->paragraph(),
            'price_per_night' => fake()->randomFloat(2, 20, 300),
            'location' => fake()->city(),
            'amenities' => ['wifi','ac'],
            'images' => ['https://picsum.photos/seed/'.fake()->uuid().'/800/500'],
        ];
    }
}
