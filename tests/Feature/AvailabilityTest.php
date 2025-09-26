<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use App\Models\User;
use App\Models\Property;
use App\Models\Availability;

class AvailabilityTest extends TestCase
{
    use RefreshDatabase;

    protected function signInAdmin(): User
    {
        $admin = User::factory()->admin()->create();
        Sanctum::actingAs($admin, ['*']);
        return $admin;
    }

    protected function signInGuest(): User
    {
        $guest = User::factory()->create(); // role 'guest' by default
        Sanctum::actingAs($guest, ['*']);
        return $guest;
    }

    /** @test */
    public function admin_can_list_availability_for_property()
    {
        $this->signInAdmin();
        $property = Property::factory()->create();
        Availability::factory()->for($property)->count(2)->create();

        $this->getJson("/api/properties/{$property->id}/availabilities")
             ->assertOk()
             ->assertJsonCount(2);
    }

    /** @test */
    public function guest_cannot_manage_availability()
    {
        $this->signInGuest();
        $property = Property::factory()->create();

        $payload = ['start_date' => '2025-10-01', 'end_date' => '2025-10-05'];
        $this->postJson("/api/properties/{$property->id}/availabilities", $payload)
             ->assertForbidden();

        $this->getJson("/api/properties/{$property->id}/availabilities")
             ->assertForbidden();
    }

    /** @test */
    public function admin_can_create_non_overlapping_availability()
    {
        $this->signInAdmin();
        $property = Property::factory()->create();

        $this->postJson("/api/properties/{$property->id}/availabilities", [
            'start_date' => '2025-10-01','end_date' => '2025-10-05',
        ])->assertCreated();

        $this->postJson("/api/properties/{$property->id}/availabilities", [
            'start_date' => '2025-10-07','end_date' => '2025-10-10',
        ])->assertCreated();

        $this->getJson("/api/properties/{$property->id}/availabilities")
             ->assertOk()->assertJsonCount(2);
    }

    /** @test */
    public function cannot_create_overlapping_availability()
    {
        $this->signInAdmin();
        $property = Property::factory()->create();

        $this->postJson("/api/properties/{$property->id}/availabilities", [
            'start_date' => '2025-10-01','end_date' => '2025-10-05',
        ])->assertCreated();

        // inside
        $this->postJson("/api/properties/{$property->id}/availabilities", [
            'start_date' => '2025-10-03','end_date' => '2025-10-04',
        ])->assertStatus(422);

        // envelope
        $this->postJson("/api/properties/{$property->id}/availabilities", [
            'start_date' => '2025-09-30','end_date' => '2025-10-06',
        ])->assertStatus(422);

        // touching edge (inclusive rule)
        $this->postJson("/api/properties/{$property->id}/availabilities", [
            'start_date' => '2025-10-05','end_date' => '2025-10-08',
        ])->assertStatus(422);
    }

    /** @test */
    public function rejects_invalid_date_ranges()
    {
        $this->signInAdmin();
        $property = Property::factory()->create();

        // start > end
        $this->postJson("/api/properties/{$property->id}/availabilities", [
            'start_date' => '2025-10-10','end_date' => '2025-10-05',
        ])->assertStatus(422);

        // same day (if your rule forbids it)
        $this->postJson("/api/properties/{$property->id}/availabilities", [
            'start_date' => '2025-10-10','end_date' => '2025-10-10',
        ])->assertStatus(422);
    }

    /** @test */
    public function admin_can_delete_an_availability_of_its_property()
    {
        $this->signInAdmin();
        $property = Property::factory()->create();
        $a = Availability::factory()->for($property)->create([
            'start_date' => '2025-10-10','end_date' => '2025-10-12'
        ]);

        $this->deleteJson("/api/properties/{$property->id}/availabilities/{$a->id}")
             ->assertNoContent();

        $this->getJson("/api/properties/{$property->id}/availabilities")
             ->assertOk()->assertJsonCount(0);
    }
}
