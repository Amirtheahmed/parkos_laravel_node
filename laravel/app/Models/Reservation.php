<?php

namespace App\Models;

use App\Enums\PaymentStatusEnum;
use App\Events\ReservationStatusUpdated;
use Database\Factories\ReservationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;

class Reservation extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * Protected attributes.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'reservation_code',
        'customer_name',
        'customer_email',
        'arrival_date',
        'departure_date',
        'payment_status',
    ];

    protected $dates = [
        'arrival_date',
        'departure_date',
        'updated_at',
        'created_at'
    ];

    protected static function newFactory(): ReservationFactory
    {
        return ReservationFactory::new();
    }
}
