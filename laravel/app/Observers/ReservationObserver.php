<?php

namespace App\Observers;

use App\Enums\PaymentStatusEnum;
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
        //dd($reservation->wasChanged('payment_status'), (int)$reservation->refresh()->payment_status);
        if (
            $reservation->wasChanged('payment_status') &&
            (int)$reservation->refresh()->payment_status === PaymentStatusEnum::PAID->value
        ) {
            event(new ReservationStatusUpdated($reservation));
        }
    }
}
