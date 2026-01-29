<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Hash;

class Tenant extends Model
{
    protected $table = 'tenants';

    protected $primaryKey = 'tenant_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'room_id',
        'full_name',
        'email',
        'password',
        'phone_number',
        'age',
        'start_date',
        'end_date',
        'status',
        'payment_term',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
        'age'        => 'integer',
    ];

    // ✅ Auto hash password when set
    public function setPasswordAttribute($value): void
    {
        if (filled($value)) {
            $this->attributes['password'] = Hash::make($value);
        }
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class, 'room_id', 'room_id');
    }
}
