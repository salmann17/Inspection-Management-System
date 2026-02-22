<?php

namespace Tests\Feature;

use Tests\TestCase;
use Tests\CleansMongoDB;
use App\Models\Inspection;

class EditInspectionTest extends TestCase
{
    use CleansMongoDB;

    private function updatePayload(): array
    {
        return [
            'service_type'       => 'MAINTENANCE',
            'scope_of_work'      => 'SOW-MT-001',
            'location'           => 'Warehouse B',
            'charge_to_customer' => false,
            'items'              => [
                ['description' => 'Updated Item', 'qty_required' => 5],
            ],
        ];
    }

    public function test_allows_edit_when_status_open(): void
    {
        $inspection = Inspection::factory()->create();

        $response = $this->putJson("/api/inspections/{$inspection->id}", $this->updatePayload());

        $response->assertStatus(200)
                 ->assertJsonPath('service_type', 'MAINTENANCE');

        $inspection->refresh();
        $this->assertEquals('MAINTENANCE', $inspection->service_type_category);
    }

    public function test_rejects_edit_when_status_for_review(): void
    {
        $inspection = Inspection::factory()->forReview()->create();

        $response = $this->putJson("/api/inspections/{$inspection->id}", $this->updatePayload());

        $response->assertStatus(400)
                 ->assertJsonPath('message', 'Inspection cannot be modified in current status.');
    }

    public function test_rejects_edit_when_status_completed(): void
    {
        $inspection = Inspection::factory()->completed()->create();

        $response = $this->putJson("/api/inspections/{$inspection->id}", $this->updatePayload());

        $response->assertStatus(400)
                 ->assertJsonPath('message', 'Inspection cannot be modified in current status.');
    }

    public function test_edit_returns_404_for_nonexistent(): void
    {
        $response = $this->putJson('/api/inspections/000000000000000000000000', $this->updatePayload());
        $response->assertStatus(404);
    }
}
