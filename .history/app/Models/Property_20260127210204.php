<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Property extends Model
{
    protected $primaryKey = 'property_id';
    public $incrementing = true;
    protected $keyType = 'int';
    protected $fillable = ['name', 'address', 'image_url'];
    public $timestamps = false;
    protected $casts = ['created_at' => 'datetime'];
    protected $fillable = ['property_name', 'address', 'image_url'];
    public $timestamps = false; // ព្រោះ table មានតែ created_at (មិនមាន updated_at)
    protected $casts = ['created_at' => 'datetime'];
}
