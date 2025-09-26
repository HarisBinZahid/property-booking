<?php

namespace Database\Factories;

use App\Models\Availability;
use App\Models\Property;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Availability>
 */
class AvailabilityFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = Availability::class;

    public function definition(): array
    {
        $start = fake()->dateTimeBetween('+1 day', '+20 days');
        $end = (clone $start)->modify('+5 days');

        return [
            'property_id' => Property::factory(),
            'start_date' => $start->format('Y-m-d'),
            'end_date' => $end->format('Y-m-d'),
        ];
    }
}
