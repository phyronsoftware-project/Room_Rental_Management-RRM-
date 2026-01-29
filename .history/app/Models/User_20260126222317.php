<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    // ✅ Custom primary key
    protected $primaryKey = 'user_id';
    public $incrementing = true;
    protected $keyType = 'int';

    // ✅ Your columns
    protected $fillable = [
        'property_id',
        'full_name',
        'email',
        'password',
        'role',
        'profile_image_url',
        'otp_code',
        'otp_expiry',
    ];

    protected $hidden = [
        'password',
        'remember_token', // (optional) if your table doesn't have this column, it's OK to keep hidden
    ];

    protected $casts = [
        'otp_expiry' => 'datetime',
        'password' => 'hashed',
    ];

    // ✅ Filament / Laravel expects "name" sometimes -> map to full_name
    public function getNameAttribute(): string
    {
        return (string) ($this->full_name ?? $this->email ?? 'User');
    }

    // (Optional) relation if you create Property model later
    // public function property()
    // {
    //     return $this->belongsTo(Property::class, 'property_id', 'property_id');
    // }
}
