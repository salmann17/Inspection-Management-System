<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class InspectionCharge extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'inspection_charges';

    protected $fillable = [
        'inspection_id',
        'order_no',
        'service_description',
        'qty',
        'unit_price',
    ];

    protected $casts = [
        'qty'        => 'integer',
        'unit_price' => 'float',
    ];

    public function inspection()
    {
        return $this->belongsTo(Inspection::class, 'inspection_id');
    }
}
