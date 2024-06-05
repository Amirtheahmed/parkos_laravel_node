<?php

namespace Tests\Unit;

use App\Enums\PaymentStatusEnum;
use App\Models\Reservation;
use App\Repositories\EloquentReservationRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Tests\TestCase;
use Illuminate\Pagination\LengthAwarePaginator;

class ReservationRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new EloquentReservationRepository(new Reservation());
    }

    public function test_store_creates_a_new_reservation()
    {
        $reservationData = [
            'reservation_code' => 'XRP123456',
            'customer_name'    => 'John Doe',
            'customer_email'   => 'john@example.com',
            'arrival_date'     => now(),
            'departure_date'   => now()->addDays(1),
            'payment_status'   => PaymentStatusEnum::PAID
        ];

        $reservation = $this->repository->store($reservationData);

        $this->assertInstanceOf(Reservation::class, $reservation);
        $this->assertEquals('John Doe', $reservation->customer_name);
        $this->assertDatabaseHas('reservations', $reservationData);
    }

    public function test_find_one_by_id()
    {
        $reservation = Reservation::factory()->create();

        $found = $this->repository->findOneById($reservation->id);

        $this->assertNotNull($found);
        $this->assertInstanceOf(Reservation::class, $found);
        $this->assertEquals($reservation->id, $found->id);
    }

    public function test_find_one_by_criteria()
    {
        $reservation = Reservation::factory()->create(['customer_email' => 'unique@example.com']);

        $found = $this->repository->findOneBy(['customer_email' => 'unique@example.com']);

        $this->assertNotNull($found);
        $this->assertEquals('unique@example.com', $found->customer_email);
    }

    public function test_paginate_reservations()
    {
        Reservation::factory()->count(40)->create();

        $paginated = $this->repository->paginate();

        $this->assertInstanceOf(LengthAwarePaginator::class, $paginated);
        $this->assertCount(30, $paginated);
    }

    public function test_upsert_creates_new_record()
    {
        $data = [
            'reservation_code' => 'R123456',
            'customer_email' => 'new@example.com',
            'customer_name' => 'New User',
            'arrival_date' => now(),
            'departure_date' => now()->addDays(1),
            'payment_status' => 'pending'
        ];

        $reservation = $this->repository->upsert($data, ['customer_email' => 'new@example.com']);

        $this->assertInstanceOf(Reservation::class, $reservation);
        $this->assertEquals('New User', $reservation->customer_name);
        $this->assertDatabaseHas('reservations', $data);
    }

    public function test_upsert_updates_existing_record()
    {
        $existingReservation = Reservation::factory()->create(['customer_email' => 'existing@example.com']);

        $updated = $this->repository->upsert(['customer_name' => 'Updated Name'], ['customer_email' => 'existing@example.com']);

        $this->assertEquals('Updated Name', $updated->customer_name);
        $this->assertDatabaseHas('reservations', ['id' => $existingReservation->id, 'customer_name' => 'Updated Name']);
    }

    public function test_update()
    {
        $reservation = Reservation::factory()->create();
        $oldName = $reservation->customer_name;

        $updated = $this->repository->update($reservation, ['customer_name' => 'Updated Name']);

        $this->assertEquals('Updated Name', $updated->customer_name);
        $this->assertNotEquals($oldName, $updated->customer_name);
    }

    public function test_find_one_like()
    {
        $reservation = Reservation::factory()->create(['customer_name' => 'John Doe']);

        $found = $this->repository->findOneLike('%John%', 'customer_name');

        $this->assertNotNull($found);
        $this->assertEquals('John Doe', $found->customer_name);
    }

    public function test_get_by_criteria()
    {
        Reservation::factory()->count(5)->create(['payment_status' => 'pending']);

        $found = $this->repository->getBy(['payment_status' => 'pending']);

        $this->assertCount(5, $found);
        foreach ($found as $item) {
            $this->assertEquals('pending', $item->payment_status);
        }
    }

    public function test_delete_where_ids()
    {
        $reservations = Reservation::factory()->count(3)->create();

        $deleted = $this->repository->deleteWhereIds($reservations->pluck('id')->toArray());

        $this->assertTrue($deleted);
        foreach ($reservations as $reservation) {
            $this->assertSoftDeleted('reservations', ['id' => $reservation->id]);
        }
    }


    public function test_force_delete_where_ids()
    {
        $reservations = Reservation::factory()->count(3)->create();

        $deleted = $this->repository->forceDeleteWhereIds($reservations->pluck('id')->toArray());

        $this->assertTrue($deleted);
        foreach ($reservations as $reservation) {
            $this->assertModelMissing($reservation);
        }
    }
}
