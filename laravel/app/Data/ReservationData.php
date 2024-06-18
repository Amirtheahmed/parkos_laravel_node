<?php

namespace App\Data;

use Illuminate\Http\Request;

class ReservationData
{
    public ?string $reservationCode;
    public ?string $customerName;
    public ?string $customerEmail;
    public ?string $arrivalDate;
    public ?string $departureDate;
    public ?string $paymentStatus;

    public static function fromRequest(Request $request): self
    {
        $dto = new self();

        $dto->reservationCode = $request->input('code');
        $dto->customerName    = $request->input('name');
        $dto->customerEmail   = $request->input('email');
        $dto->arrivalDate     = $request->input('arrival');
        $dto->departureDate   = $request->input('departure');
        $dto->paymentStatus   = $request->input('status');

        return $dto;
    }

    public function toDbArray(): array
    {
        return array_filter([
            'reservation_code' => $this->reservationCode,
            'customer_name'    => $this->customerName,
            'customer_email'   => $this->customerEmail,
            'arrival_date'     => $this->arrivalDate,
            'departure_date'   => $this->departureDate,
            'payment_status'   => $this->paymentStatus,
        ]);
    }
}
