<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Room extends Model
{
    protected $table = 'rooms';

    protected $primaryKey = 'room_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'property_id',
        'room_number',
        'floor',
        'price',
        'status',
    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class, 'property_id', 'property_id');
    }

    public function tenants(): HasMany
    {
        return $this->hasMany(Tenant::class, 'room_id', 'room_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'room_id', 'room_id');
    }
}
