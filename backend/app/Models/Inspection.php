<?php

namespace App\Models;

use InvalidArgumentException;
use MongoDB\Laravel\Eloquent\Model;

class Inspection extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'inspections';

    // Granular status values
    const STATUS_NEW             = 'NEW';
    const STATUS_IN_PROGRESS     = 'IN_PROGRESS';
    const STATUS_READY_TO_REVIEW = 'READY_TO_REVIEW';
    const STATUS_APPROVED        = 'APPROVED';
    const STATUS_COMPLETED       = 'COMPLETED';

    const ALLOWED_STATUSES = [
        self::STATUS_NEW,
        self::STATUS_IN_PROGRESS,
        self::STATUS_READY_TO_REVIEW,
        self::STATUS_APPROVED,
        self::STATUS_COMPLETED,
    ];

    // Workflow tab groups (OPEN / FOR_REVIEW / COMPLETED)
    const GROUP_OPEN       = 'OPEN';
    const GROUP_FOR_REVIEW = 'FOR_REVIEW';
    const GROUP_COMPLETED  = 'COMPLETED';

    // Status → Group mapping
    const STATUS_TO_GROUP = [
        'NEW'             => 'OPEN',
        'IN_PROGRESS'     => 'OPEN',
        'READY_TO_REVIEW' => 'FOR_REVIEW',
        'APPROVED'        => 'COMPLETED',
        'COMPLETED'       => 'COMPLETED',
    ];

    // Allowed transitions: from → [to, to]
    const TRANSITIONS = [
        'NEW'             => ['IN_PROGRESS', 'READY_TO_REVIEW'],
        'IN_PROGRESS'     => ['READY_TO_REVIEW'],
        'READY_TO_REVIEW' => ['APPROVED', 'IN_PROGRESS'],
        'APPROVED'        => ['COMPLETED'],
        'COMPLETED'       => [],
    ];

    protected $fillable = [
        'request_no',
        'service_type_category',
        'scope_of_work_code',
        'location',
        'estimated_completion_date',
        'related_to',
        'charge_to_customer',
        'customer_name',
        'workflow_status_group',
        'status',
        'total_items',
        'total_lots',
        'created_by',
        'submitted_at',
        'completed_at',
    ];

    protected $casts = [
        'estimated_completion_date' => 'date',
        'charge_to_customer'        => 'boolean',
        'total_items'               => 'integer',
        'total_lots'                => 'integer',
        'submitted_at'              => 'datetime',
        'completed_at'              => 'datetime',
    ];

    protected $attributes = [
        'status'                => 'NEW',
        'workflow_status_group' => 'OPEN',
        'total_items'           => 0,
        'total_lots'            => 0,
        'charge_to_customer'    => false,
    ];

    // Auto-set workflow_status_group whenever status is set; reject unknown values
    public function setStatusAttribute(string $value): void
    {
        if (!in_array($value, self::ALLOWED_STATUSES, true)) {
            throw new InvalidArgumentException("Invalid inspection status: {$value}");
        }

        $this->attributes['status']                = $value;
        $this->attributes['workflow_status_group'] = self::STATUS_TO_GROUP[$value];
    }

    public function items()
    {
        return $this->hasMany(InspectionItem::class, 'inspection_id');
    }

    public function isEditable(): bool
    {
        return $this->workflow_status_group === 'OPEN';
    }

    public static function generateRequestNo(): string
    {
        $prefix = 'INS-' . now()->format('Ymd') . '-';

        $latest = static::where('request_no', 'like', $prefix . '%')
            ->orderByDesc('request_no')
            ->first();

        $sequence = $latest
            ? (int) str_replace($prefix, '', $latest->request_no) + 1
            : 1;

        return $prefix . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    public static function indexDefinitions(): array
    {
        return [
            ['key' => ['request_no' => 1],                              'options' => ['unique' => true, 'name' => 'request_no_unique']],
            ['key' => ['status' => 1],                                  'options' => ['name' => 'status_idx']],
            ['key' => ['created_at' => -1],                             'options' => ['name' => 'created_at_idx']],
            ['key' => ['workflow_status_group' => 1, 'created_at' => -1], 'options' => ['name' => 'group_created_idx']],
            ['key' => ['service_type_category' => 1],                   'options' => ['name' => 'service_type_category_idx']],
        ];
    }
}
