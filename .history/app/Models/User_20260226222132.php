<?php

namespace App\Models;

use Filament\Models\Contracts\HasName;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable implements HasName
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
    public function getFilamentName(): string
    {
        return (string) ($this->email ?? $this->full_name ?? 'User');
    }
    // ✅ Filament / Laravel expects "name" sometimes -> map to full_name
    public function getNameAttribute(): string
    {
        return (string) ($this->full_name ?? $this->email ?? 'User');
    }
    public function hasRole(string|array $roles): bool
    {
        $role = strtolower((string) $this->role);
        $roles = (array) $roles;

        foreach ($roles as $r) {
            if ($role === strtolower((string) $r)) {
                return true;
            }
        }

        return false;
    }
    public function getFilamentAvatarUrl(): ?string
    {
        if (blank($this->profile_image_url)) {
            return null; // Filament will show letter avatar (P)
        }

        // If already a full URL (http/https), return directly
        if (str_starts_with($this->profile_image_url, 'http')) {
            return $this->profile_image_url;
        }

        // Stored path on public disk
        return Storage::disk('public')->url($this->profile_image_url);
    }
    public function isSuperAdmin(): bool
    {
        return $this->hasRole('super_admin');
    }

    public function isManager(): bool
    {
        return $this->hasRole('manager');
    }

    public function isOwner(): bool
    {
        return $this->hasRole('owner');
    }

    // ✅ optional: roles allowed to use dashboard/admin panel
    public function canAccessDashboard(): bool
    {
        return $this->hasRole(['super_admin', 'manager', 'owner']);
    }

    // (Optional) relation if you create Property model later
    // public function property()
    // {
    //     return $this->belongsTo(Property::class, 'property_id', 'property_id');
    // }
    public function property()
    {
        return $this->belongsTo(\App\Models\Property::class, 'property_id', 'property_id');
    }
}
