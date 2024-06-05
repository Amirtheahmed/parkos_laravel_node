<?php

namespace Feature;

use App\Jobs\SendReservationConfirmation;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class ReservationControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_reservation_index()
    {
        $user = User::factory()->create();
        Reservation::factory()->count(5)->create();

        $response = $this->actingAs($user)->get('api/reservations');

        $response->assertOk();
        $response->assertJsonCount(5, 'data');
    }

    public function test_create_reservation()
    {
        $user = User::factory()->create();
        $reservationData = Reservation::factory()->make()->toArray();

        $response = $this->actingAs($user)->post('api/reservations', $reservationData);

        $response->assertCreated();
        $this->assertDatabaseHas('reservations', $reservationData);
    }

    public function test_show_reservation()
    {
        $user = User::factory()->create();
        $reservation = Reservation::factory()->create();

        $response = $this->actingAs($user)->get("api/reservations/{$reservation->id}");

        $response->assertOk();
        $response->assertJsonFragment($reservation->toArray());
    }

    public function test_update_reservation()
    {
        $user = User::factory()->create();
        $reservation = Reservation::factory()->create();
        $updatedData = ['customer_name' => 'Updated Name'];

        $response = $this->actingAs($user)->put("api/reservations/{$reservation->id}", $updatedData);

        $response->assertOk();
        $this->assertDatabaseHas('reservations', $updatedData);
    }

    public function test_delete_reservation()
    {
        $user = User::factory()->create();
        $reservation = Reservation::factory()->create();

        $response = $this->actingAs($user)->delete("api/reservations/{$reservation->id}");

        $response->assertNoContent();
        $this->assertSoftDeleted('reservations', ['id' => $reservation->id]);
    }

    public function test_reservation_status_update_triggers_event()
    {
        $reservation = Reservation::factory()->create(['payment_status' => 'pending']);

        $response = $this->patch(route('reservations.update', $reservation->id), [
            'payment_status' => 'paid'
        ]);

        $response->assertOk();
        Queue::assertPushed(SendReservationConfirmation::class);
    }

}
