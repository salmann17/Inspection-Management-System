<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class InventoryLot extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'inventory_lots';

    protected $fillable = [
        'item_code',
        'item_name',
        'item_category',
        'lot',
        'allocation',
        'owner',
        'condition',
        'available_qty',
        'warehouse_location',
    ];

    protected $casts = [
        'available_qty' => 'integer',
    ];

    public static function indexDefinitions(): array
    {
        return [
            ['key' => ['item_code' => 1, 'lot' => 1], 'options' => ['name' => 'item_code_lot_idx']],
            ['key' => ['lot' => 1], 'options' => ['name' => 'lot_idx']],
            ['key' => ['allocation' => 1], 'options' => ['name' => 'allocation_idx']],
            ['key' => ['owner' => 1], 'options' => ['name' => 'owner_idx']],
            ['key' => ['condition' => 1], 'options' => ['name' => 'condition_idx']],
        ];
    }
}
