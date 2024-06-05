<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ListReservationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'page' => ['integer', 'min:1'],
            'limit' => ['integer', 'between:1,100'],
        ];
    }
}
