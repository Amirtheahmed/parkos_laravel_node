<?php

namespace App\Http\Requests;

use App\Enums\PaymentStatusEnum;
use Illuminate\Validation\Rules\Enum;

class CreateReservationRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code'       => ['required', 'string', 'unique:reservations,reservation_code'],
            'name'       => ['required', 'string', 'max:255'],
            'email'      => ['required', 'email'],
            'arrival'    => ['required', 'date', 'after_or_equal:today'],
            'departure'  => ['required', 'date', 'after:arrival_date'],
            'status'     => ['required', new Enum(PaymentStatusEnum::class)],
        ];
    }

    public function messages(): array
    {
        return [
            'code.required'          => 'A reservation code is required.',
            'code.unique'            => 'This reservation code is already in use.',
            'name.required'          => 'Customer name is required.',
            'name.string'            => 'Customer name must be a valid string.',
            'name.max'               => 'Customer name may not be greater than 255 characters.',
            'email.required'         => 'An email address is required.',
            'email.email'            => 'The email address must be a valid email.',
            'arrival.required'       => 'An arrival date is required.',
            'arrival.date'           => 'The arrival date must be a valid date.',
            'arrival.after_or_equal' => 'The arrival date must be today or a future date.',
            'departure.required'     => 'A departure date is required.',
            'departure.date'         => 'The departure date must be a valid date.',
            'departure.after'        => 'The departure date must be after the arrival date.',
            'status.required'        => 'The payment status is required.',
            'status.enum'            => 'The selected payment status is invalid.',
        ];
    }
}
