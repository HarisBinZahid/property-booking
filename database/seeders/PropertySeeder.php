<?php

namespace Database\Seeders;

use App\Models\Property;
use App\Models\User;
use Illuminate\Database\Seeder;

class PropertySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::where('role', 'admin')->first();
        $p = Property::create(['user_id' => $admin->id, 'title' => 'Cozy Studio', 'description' => 'Walk to cafes', 'price_per_night' => 89.99, 'location' => 'Dubai Marina', 'amenities' => ['wifi', 'ac'], 'images' => ['https://picsum.photos/seed/apt/800/500']]);
        $p->availabilities()->create(['start_date' => now(), 'end_date' => now()->addMonth()]);
    }
}
