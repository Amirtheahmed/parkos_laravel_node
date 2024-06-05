<?php

namespace App\Http\Resources;

use App\Enums\PaymentStatusEnum;
use Illuminate\Http\Resources\Json\JsonResource;

class ReservationResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'            => $this->id,
            'code'          => $this->reservation_code,
            'name'          => $this->customer_name,
            'email'         => $this->customer_email,
            'arrival'       => $this->arrival_date,
            'departure'     => $this->departure_date,
            'status'        => PaymentStatusEnum::tryFrom($this->payment_status)?->name,
            'created_at'    => $this->created_at,
            'updated_at'    => $this->updated_at,
        ];
    }
}
