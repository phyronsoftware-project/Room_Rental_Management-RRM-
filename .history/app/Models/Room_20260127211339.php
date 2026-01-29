<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Room extends Model
{
    // ✅ table name (optional, but clear)
    protected $table = 'rooms';

    // ✅ primary key
    protected $primaryKey = 'room_id';
    public $incrementing = true;
    protected $keyType = 'int';

    // ✅ fillable columns
    protected $fillable = [
        'property_id',
        'room_number',
        'floor',
        'price',
        'status',
    ];

    // ✅ casts (price decimal)
    protected $casts = [
        'price' => 'decimal:2',
    ];

    // =========================
    // Relationships
    // =========================

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class, 'property_id', 'property_id');
    }

    // One room can have many tenants over time (history)
    // If you want only current tenant, we can create a scope later.
    public function tenants(): HasMany
    {
        return $this->hasMany(Tenant::class, 'room_id', 'room_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'room_id', 'room_id');
    }

    // public function maintenanceRequests(): HasMany
    // {
    //     return $this->hasMany(Ma::class, 'room_id', 'room_id');
    // }
}
