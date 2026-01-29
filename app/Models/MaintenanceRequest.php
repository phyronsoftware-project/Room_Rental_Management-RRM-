<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaintenanceRequest extends Model
{
    protected $table = 'maintenancerequests';

    protected $primaryKey = 'request_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'room_id',
        'property_id',
        'issue_reported',
        'status',
        'date_reported',
        'assigned_to',
    ];

    protected $casts = [
        'date_reported' => 'datetime',
    ];

    public function room()
    {
        return $this->belongsTo(\App\Models\Room::class, 'room_id', 'room_id');
    }

    public function property()
    {
        return $this->belongsTo(\App\Models\Property::class, 'property_id', 'property_id');
    }
}
