<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class InspectionLot extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'inspection_lots';

    protected $fillable = [
        'inspection_item_id',
        'lot',
        'allocation',
        'owner',
        'condition',
        'sample_qty',
    ];

    protected $casts = [
        'sample_qty' => 'integer',
    ];

    public function inspectionItem()
    {
        return $this->belongsTo(InspectionItem::class, 'inspection_item_id');
    }

    public static function indexDefinitions(): array
    {
        return [
            ['key' => ['inspection_item_id' => 1], 'options' => ['name' => 'inspection_item_id_idx']],
            ['key' => ['lot' => 1], 'options' => ['name' => 'lot_idx']],
        ];
    }
}
