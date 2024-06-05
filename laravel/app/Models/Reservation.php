<?php

namespace App\Models;

use Database\Factories\ReservationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Reservation extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * Protected attributes.
     *
     * @var array<int, string>
     */
    protected $guarded = [];

    protected static function newFactory(): ReservationFactory
    {
        return ReservationFactory::new();
    }
}
