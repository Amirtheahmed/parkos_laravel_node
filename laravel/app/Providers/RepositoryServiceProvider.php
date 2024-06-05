<?php

namespace App\Providers;

use App\Contracts\ReservationRepository;
use App\Models\Reservation;
use App\Repositories\EloquentReservationRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ReservationRepository::class, function () {
            return new EloquentReservationRepository(new Reservation());
        });
    }

    public function provides(): array
    {
        return [
            ReservationRepository::class
        ];
    }
}
