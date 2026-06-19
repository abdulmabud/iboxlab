<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $fillable = [
        'reference',
        'status',
        'flight_id',
        'provider',
        'carrier',
        'flight_number',
        'from',
        'to',
        'depart_at',
        'arrive_at',
        'stops',
        'price',
        'total_price',
        'currency',
        'passenger_count',
        'passengers',
    ];

    protected function casts(): array
    {
        return [
            'passengers' => 'array',
            'price' => 'float',
            'total_price' => 'float',
            'stops' => 'integer',
            'passenger_count' => 'integer',
        ];
    }
}
