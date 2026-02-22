<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class MasterData extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'master_data';

    protected $fillable = [
        'type',
        'code',
        'name',
        'description',
        'parent_code',
        'meta',
        'is_active',
    ];

    protected $casts = [
        'meta'      => 'array',
        'is_active' => 'boolean',
    ];

    protected $attributes = [
        'is_active' => true,
    ];

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public static function indexDefinitions(): array
    {
        return [
            ['key' => ['type' => 1, 'code' => 1], 'options' => ['unique' => true, 'name' => 'type_code_unique']],
            ['key' => ['type' => 1], 'options' => ['name' => 'type_idx']],
            ['key' => ['parent_code' => 1], 'options' => ['name' => 'parent_code_idx']],
        ];
    }
}
