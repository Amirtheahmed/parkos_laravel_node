<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Enums\PaymentStatusEnum;
use Illuminate\Validation\Rules\Enum;

class UpdateReservationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'     => ['sometimes', 'string'],
            'email'    => ['sometimes', 'email'],
            'arrival'  => ['sometimes', 'date', 'after_or_equal:today'],
            'departure'=> ['sometimes', 'date', 'after:arrival'],
            'status'   => ['sometimes', new Enum(PaymentStatusEnum::class)],
        ];
    }

    public function messages(): array
    {
        return [
            'name.string' => 'Customer name must be a valid string.',
            'email.email' => 'The email must be a valid email address.',
            'arrival.date' => 'The arrival date must be a valid date.',
            'arrival.after_or_equal' => 'The arrival date must be today or a future date.',
            'departure.date' => 'The departure date must be a valid date.',
            'departure.after' => 'The departure date must be after the arrival date.',
            'status.enum' => 'The selected payment status is invalid.',
        ];
    }
}
