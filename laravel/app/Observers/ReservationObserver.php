<?php

namespace App\Observers;

use App\Events\ReservationStatusUpdated;
use App\Models\Reservation;

class ReservationObserver
{
    /**
     * Handle the Reservation "updated" event.
     *
     * @param Reservation $reservation
     * @return void
     */
    public function updated(Reservation $reservation): void
    {
        if ($reservation->isDirty('payment_status') && $reservation->payment_status === 'paid') {
            event(new ReservationStatusUpdated($reservation));
        }
    }
}
