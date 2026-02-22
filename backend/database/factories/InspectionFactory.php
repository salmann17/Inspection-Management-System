<?php

namespace Database\Factories;

use App\Models\Inspection;
use Illuminate\Database\Eloquent\Factories\Factory;

class InspectionFactory extends Factory
{
    protected $model = Inspection::class;

    public function definition(): array
    {
        return [
            'request_no'                => 'INS-' . now()->format('Ymd') . '-' . str_pad(fake()->unique()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT),
            'service_type_category'     => 'NEW_ARRIVAL',
            'scope_of_work_code'        => 'SOW-NA-001',
            'location'                  => 'Warehouse A',
            'estimated_completion_date' => now()->addDays(7),
            'related_to'               => null,
            'charge_to_customer'        => false,
            'customer_name'             => null,
            'status'                    => Inspection::STATUS_NEW,
            'workflow_status_group'     => 'OPEN',
            'total_items'               => 0,
            'total_lots'                => 0,
        ];
    }

    public function forReview(): static
    {
        return $this->state([
            'status'                => Inspection::STATUS_READY_TO_REVIEW,
            'workflow_status_group' => 'FOR_REVIEW',
        ]);
    }

    public function completed(): static
    {
        return $this->state([
            'status'                => Inspection::STATUS_COMPLETED,
            'workflow_status_group' => 'COMPLETED',
        ]);
    }

    public function chargeEnabled(): static
    {
        return $this->state([
            'charge_to_customer' => true,
            'customer_name'      => 'Test Customer',
        ]);
    }
}
