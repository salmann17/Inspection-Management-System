<?php

namespace Tests\Feature;

use Tests\TestCase;
use Tests\CleansMongoDB;
use App\Models\Inspection;

class InspectionWorkflowTest extends TestCase
{
    use CleansMongoDB;

    public function test_open_can_transition_to_for_review(): void
    {
        $inspection = Inspection::factory()->create();

        $response = $this->patchJson("/api/inspections/{$inspection->id}/status", [
            'status' => 'FOR_REVIEW',
        ]);

        $response->assertStatus(200)
                 ->assertJsonPath('workflow_status_group', 'FOR_REVIEW');

        $inspection->refresh();
        $this->assertEquals('READY_TO_REVIEW', $inspection->status);
        $this->assertEquals('FOR_REVIEW', $inspection->workflow_status_group);
    }

    public function test_for_review_can_transition_to_completed(): void
    {
        $inspection = Inspection::factory()->forReview()->create();

        $response = $this->patchJson("/api/inspections/{$inspection->id}/status", [
            'status' => 'COMPLETED',
        ]);

        $response->assertStatus(200)
                 ->assertJsonPath('workflow_status_group', 'COMPLETED');

        $inspection->refresh();
        $this->assertEquals('COMPLETED', $inspection->status);
    }

    public function test_open_cannot_transition_to_completed(): void
    {
        $inspection = Inspection::factory()->create();

        $response = $this->patchJson("/api/inspections/{$inspection->id}/status", [
            'status' => 'COMPLETED',
        ]);

        $response->assertStatus(400)
                 ->assertJsonPath('message', 'Invalid status transition.');
    }

    public function test_completed_cannot_transition(): void
    {
        $inspection = Inspection::factory()->completed()->create();

        $response = $this->patchJson("/api/inspections/{$inspection->id}/status", [
            'status' => 'OPEN',
        ]);

        $response->assertStatus(400);
    }

    public function test_for_review_cannot_go_back_to_open(): void
    {
        $inspection = Inspection::factory()->forReview()->create();

        $response = $this->patchJson("/api/inspections/{$inspection->id}/status", [
            'status' => 'OPEN',
        ]);

        $response->assertStatus(400);
    }
}
