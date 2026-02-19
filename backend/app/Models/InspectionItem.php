<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class InspectionItem extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'inspection_items';

    protected $fillable = [
        'inspection_id',
        'item_code',
        'item_name',
        'item_category',
        'available_qty',
        'qty_required',
        'inspection_required',
        'remarks',
    ];

    protected $casts = [
        'available_qty'       => 'integer',
        'qty_required'        => 'integer',
        'inspection_required' => 'boolean',
    ];

    protected $attributes = [
        'inspection_required' => true,
    ];

    public function inspection()
    {
        return $this->belongsTo(Inspection::class, 'inspection_id');
    }

    public function lots()
    {
        return $this->hasMany(InspectionLot::class, 'inspection_item_id');
    }

    public static function indexDefinitions(): array
    {
        return [
            ['key' => ['inspection_id' => 1], 'options' => ['name' => 'inspection_id_idx']],
            ['key' => ['item_code' => 1], 'options' => ['name' => 'item_code_idx']],
        ];
    }
}
