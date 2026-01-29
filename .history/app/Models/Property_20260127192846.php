<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Property extends Model
{
    protected $primaryKey = 'property_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = ['property_name', 'address'];
}
