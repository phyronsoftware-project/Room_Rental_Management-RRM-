<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BillingSetting extends Model
{
    protected $table = 'billing_settings';
    protected $primaryKey = 'setting_id';

    protected $fillable = [
        'property_id',
        'electricity_unit_price',
        'water_unit_price',
        'car_parking_price',
        'motorbike_parking_price',
    ];

    protected $casts = [
        'electricity_unit_price' => 'decimal:2',
        'water_unit_price' => 'decimal:2',
        'car_parking_price' => 'decimal:2',
        'motorbike_parking_price' => 'decimal:2',
    ];

    public function property()
    {
        return $this->belongsTo(Property::class, 'property_id', 'property_id');
    }

    // ✅ Helper: get setting for property, fallback global
    public static function singleton(): self
    {
        return static::query()->first()
            ?? static::query()->create([
                'property_id' => null,
                'water_unit_price' => 0,
                'electricity_unit_price' => 0,
                'car_parking_price' => 0,
                'motorbike_parking_price' => 0,
            ]);
    }
}
