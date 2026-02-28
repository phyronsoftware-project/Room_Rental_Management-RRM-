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
    public static function forProperty(?int $propertyId): self
    {
        return static::query()
            ->where('property_id', $propertyId)
            ->first()
            ?? static::query()->whereNull('property_id')->first()
            ?? new static([
                'electricity_unit_price' => 0,
                'water_unit_price' => 0,
                'car_parking_price' => 0,
                'motorbike_parking_price' => 0,
            ]);
    }
}
