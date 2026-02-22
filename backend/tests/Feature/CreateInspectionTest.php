<?php

namespace Tests\Feature;

use Tests\TestCase;
use Tests\CleansMongoDB;
use App\Models\Inspection;
use App\Models\InspectionItem;

class CreateInspectionTest extends TestCase
{
    use CleansMongoDB;

    public function test_creates_inspection_with_status_open(): void
    {
        $payload = [
            'service_type'  => 'NEW_ARRIVAL',
            'scope_of_work' => 'SOW-NA-001',
            'location'      => 'Warehouse A',
            'items'         => [
                [
                    'description'  => 'Carbon Steel Pipe 6" Sch40',
                    'qty_required' => 10,
                    'lots'         => [
                        ['lot' => 'LOT-001', 'allocation' => 'Project Alpha', 'owner' => 'Company Owned', 'condition' => 'New', 'available_qty' => 50, 'sample_qty' => 5],
                    ],
                ],
            ],
        ];

        $response = $this->postJson('/api/inspections', $payload);

        $response->assertStatus(201)
                 ->assertJsonStructure(['success', 'inspection_id']);

        $inspection = Inspection::first();
        $this->assertNotNull($inspection);
        $this->assertEquals('NEW', $inspection->status);
        $this->assertEquals('OPEN', $inspection->workflow_status_group);
        $this->assertEquals(1, $inspection->total_items);
        $this->assertEquals(1, $inspection->total_lots);

        $this->assertEquals(1, InspectionItem::count());
    }

    public function test_create_requires_items(): void
    {
        $payload = [
            'service_type'  => 'NEW_ARRIVAL',
            'scope_of_work' => 'SOW-NA-001',
            'items'         => [],
        ];

        $response = $this->postJson('/api/inspections', $payload);
        $response->assertStatus(422);
    }

    public function test_create_requires_service_type(): void
    {
        $payload = [
            'scope_of_work' => 'SOW-NA-001',
            'items'         => [
                ['description' => 'Test Item', 'qty_required' => 1],
            ],
        ];

        $response = $this->postJson('/api/inspections', $payload);
        $response->assertStatus(422);
    }
}
