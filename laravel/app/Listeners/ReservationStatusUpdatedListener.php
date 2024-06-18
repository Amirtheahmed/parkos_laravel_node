<?php

namespace App\Listeners;

use App\Events\ReservationStatusUpdated;
use App\Jobs\SendReservationConfirmation;
use Illuminate\Contracts\Queue\ShouldQueue;

class ReservationStatusUpdatedListener
{

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {

    }

    /**
     * Handle the event.
     *
     * @param ReservationStatusUpdated $event
     * @return void
     */
    public function handle(ReservationStatusUpdated $event): void
    {
        SendReservationConfirmation::dispatch($event->reservation);
    }
}
