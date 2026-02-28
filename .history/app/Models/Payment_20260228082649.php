<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $table = 'payments';

    protected $primaryKey = 'payment_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'tenant_id',
        'room_id',
        'property_id',
        'amount',
        'water_fee',
        'electricity_fee',
        'water_fee',
        'electricity_fee',
        'water_m3',
        'electricity_kwh',
        'car_count',
        'motorbike_count',
        'parking_fee',
        'payment_date',
        'payment_method',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'water_fee' => 'decimal:2',
        'electricity_fee' => 'decimal:2',
        'payment_date' => 'date',
    ];

    public function tenant()
    {
        return $this->belongsTo(\App\Models\Tenant::class, 'tenant_id', 'tenant_id');
    }

    public function room()
    {
        return $this->belongsTo(\App\Models\Room::class, 'room_id', 'room_id');
    }

    public function property()
    {
        return $this->belongsTo(\App\Models\Property::class, 'property_id', 'property_id');
    }
}
