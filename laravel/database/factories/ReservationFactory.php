<?php

namespace Database\Factories;

use App\Enums\PaymentStatusEnum;
use App\Models\Reservation;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReservationFactory extends Factory
{
    protected $model = Reservation::class;

    public function definition(): array
    {
        return [
            'reservation_code'  => $this->faker->asciify('*******'),
            'customer_name'     => $this->faker->name,
            'customer_email'    => $this->faker->email,
            'arrival_date'      => $this->faker->dateTime,
            'departure_date'    => $this->faker->dateTime,
            'payment_status'    => $this->faker->randomElement(PaymentStatusEnum::cases()),
        ];
    }
}
