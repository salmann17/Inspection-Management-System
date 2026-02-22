<?php

namespace Tests\Feature;

use Tests\TestCase;
use Tests\CleansMongoDB;
use App\Models\Inspection;
use App\Models\InspectionCharge;

class ChargeTest extends TestCase
{
    use CleansMongoDB;

    private function chargePayload(): array
    {
        return [
            'order_no'            => 'ORD-001',
            'service_description' => 'Inspection Service Fee',
            'qty'                 => 2,
            'unit_price'          => 150.00,
        ];
    }

    public function test_allows_charge_when_charge_to_customer_true(): void
    {
        $inspection = Inspection::factory()->chargeEnabled()->create();

        $response = $this->postJson("/api/inspections/{$inspection->id}/charges", $this->chargePayload());

        $response->assertStatus(201)
                 ->assertJsonStructure(['charges']);

        $this->assertEquals(1, InspectionCharge::count());

        $charge = InspectionCharge::first();
        $this->assertEquals('ORD-001', $charge->order_no);
        $this->assertEquals(2, $charge->qty);
        $this->assertEquals(150.00, $charge->unit_price);
    }

    public function test_rejects_charge_when_charge_to_customer_false(): void
    {
        $inspection = Inspection::factory()->create(['charge_to_customer' => false]);

        $response = $this->postJson("/api/inspections/{$inspection->id}/charges", $this->chargePayload());

        $response->assertStatus(400)
                 ->assertJsonPath('message', 'Charges are not enabled for this inspection.');

        $this->assertEquals(0, InspectionCharge::count());
    }

    public function test_rejects_charge_on_completed_inspection(): void
    {
        $inspection = Inspection::factory()->completed()->chargeEnabled()->create();

        $response = $this->postJson("/api/inspections/{$inspection->id}/charges", $this->chargePayload());

        $response->assertStatus(400)
                 ->assertJsonPath('message', 'Cannot add charges to a completed inspection.');
    }

    public function test_charges_removed_when_charge_disabled_on_edit(): void
    {
        $inspection = Inspection::factory()->chargeEnabled()->create();

        InspectionCharge::create([
            'inspection_id'       => $inspection->id,
            'order_no'            => 'ORD-001',
            'service_description' => 'Fee',
            'qty'                 => 1,
            'unit_price'          => 100,
        ]);

        $this->assertEquals(1, InspectionCharge::count());

        $this->putJson("/api/inspections/{$inspection->id}", [
            'service_type'       => 'NEW_ARRIVAL',
            'scope_of_work'      => 'SOW-NA-001',
            'charge_to_customer' => false,
            'items'              => [
                ['description' => 'Item A', 'qty_required' => 1],
            ],
        ]);

        $this->assertEquals(0, InspectionCharge::count());
    }
}
