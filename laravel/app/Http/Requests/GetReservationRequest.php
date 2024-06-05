<?php

namespace App\Http\Requests;

class GetReservationRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [];
    }
}
