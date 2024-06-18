<?php

namespace Feature;

use App\Enums\PaymentStatusEnum;
use App\Events\ReservationStatusUpdated;
use App\Jobs\SendReservationConfirmation;
use App\Models\Reservation;
use App\Models\User;
use App\Observers\ReservationObserver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Mockery;
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
        $reservationData = [
            'code'      => 'XRP123456',
            'name'      => 'John Doe',
            'email'     => 'john@example.com',
            'arrival'   => now(),
            'departure' => now()->addDays(1),
            'status'    => PaymentStatusEnum::PAID->value
        ];

        $response = $this->actingAs($user)->post('api/reservations', $reservationData);

        $response->assertCreated();
        $this->assertDatabaseHas('reservations', [
            'reservation_code' => $reservationData['code'],
            'customer_name'    => $reservationData['name'],
            'customer_email'   => $reservationData['email'],
            'arrival_date'     => $reservationData['arrival'],
            'departure_date'   => $reservationData['departure'],
            'payment_status'   => $reservationData['status'],
        ]);
    }

    public function test_show_reservation()
    {
        $user = User::factory()->create();
        $reservation = Reservation::factory()->create();

        $response = $this->actingAs($user)->get("api/reservations/{$reservation->id}");

        $response->assertOk();
        $response->assertJsonFragment([
            'code'      => $reservation->reservation_code,
            'name'      => $reservation->customer_name,
            'email'     => $reservation->customer_email,
            'arrival'   => $reservation->arrival_date,
            'departure' => $reservation->departure_date,
            'status'    => $reservation->payment_status->name
        ]);
    }

    public function test_update_reservation()
    {
        $user = User::factory()->create();
        $reservation = Reservation::factory()->create();
        $updatedData = ['name' => 'Updated Name'];

        $response = $this->actingAs($user)->post("api/reservations/{$reservation->id}", $updatedData);

        $response->assertOk();
        $this->assertDatabaseHas('reservations', [
            'customer_name' => $updatedData['name']
        ]);
    }

    public function test_delete_reservation()
    {
        $user = User::factory()->create();
        $reservation = Reservation::factory()->create();

        $response = $this->actingAs($user)->delete("api/reservations/{$reservation->id}");

        $response->assertOk();
        $this->assertSoftDeleted('reservations', ['id' => $reservation->id]);
    }

    public function test_reservation_status_update_triggers_event()
    {
        $reservation = Reservation::factory()->create(['payment_status' => PaymentStatusEnum::PENDING->value]);

        Queue::fake();
        Event::fake([
            ReservationStatusUpdated::class
        ]);

        $response = $this->post(route('reservations.update', $reservation->id), [
            'status' => PaymentStatusEnum::PAID->value
        ]);

        $response->assertOk();

        Event::assertDispatched(ReservationStatusUpdated::class);
    }

}
