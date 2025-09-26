<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\Property;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Booking>
 */
class BookingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = Booking::class;

    public function definition(): array
    {
        $start = fake()->dateTimeBetween('+2 days', '+10 days');
        $end = (clone $start)->modify('+2 days');

        return [
            'property_id' => Property::factory(),
            'guest_id' => User::factory(), // role guest by default
            'start_date' => $start->format('Y-m-d'),
            'end_date' => $end->format('Y-m-d'),
            'status' => 'pending',
        ];
    }
}
