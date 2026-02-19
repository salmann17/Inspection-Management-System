<?php

namespace Database\Seeders;

use App\Models\MasterData;
use Illuminate\Database\Seeder;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        $records = $this->getData();

        foreach ($records as $record) {
            MasterData::updateOrCreate(
                ['type' => $record['type'], 'code' => $record['code']],
                $record
            );
        }

        $this->command->info('MasterData seeded: ' . count($records) . ' records.');
    }

    private function getData(): array
    {
        return [
            // Service Type Categories
            ['type' => 'service_type_category', 'code' => 'NEW_ARRIVAL', 'name' => 'New Arrival',  'parent_code' => null, 'is_active' => true],
            ['type' => 'service_type_category', 'code' => 'MAINTENANCE', 'name' => 'Maintenance',  'parent_code' => null, 'is_active' => true],
            ['type' => 'service_type_category', 'code' => 'ON_SPOT',     'name' => 'On Spot',      'parent_code' => null, 'is_active' => true],

            // Scope of Work → parent_code menghubungkan ke service_type_category
            ['type' => 'scope_of_work', 'code' => 'SOW-NA-001', 'name' => 'Inbound Quality Inspection',   'parent_code' => 'NEW_ARRIVAL', 'is_active' => true],
            ['type' => 'scope_of_work', 'code' => 'SOW-NA-002', 'name' => 'Documentation Verification',   'parent_code' => 'NEW_ARRIVAL', 'is_active' => true],
            ['type' => 'scope_of_work', 'code' => 'SOW-NA-003', 'name' => 'Packaging Integrity Check',    'parent_code' => 'NEW_ARRIVAL', 'is_active' => true],
            ['type' => 'scope_of_work', 'code' => 'SOW-NA-004', 'name' => 'Quantity Verification',        'parent_code' => 'NEW_ARRIVAL', 'is_active' => true],
            ['type' => 'scope_of_work', 'code' => 'SOW-MT-001', 'name' => 'Preventive Maintenance Check', 'parent_code' => 'MAINTENANCE', 'is_active' => true],
            ['type' => 'scope_of_work', 'code' => 'SOW-MT-002', 'name' => 'Corrective Repair Inspection', 'parent_code' => 'MAINTENANCE', 'is_active' => true],
            ['type' => 'scope_of_work', 'code' => 'SOW-MT-003', 'name' => 'Calibration Verification',     'parent_code' => 'MAINTENANCE', 'is_active' => true],
            ['type' => 'scope_of_work', 'code' => 'SOW-OS-001', 'name' => 'Visual Spot Check',            'parent_code' => 'ON_SPOT',     'is_active' => true],
            ['type' => 'scope_of_work', 'code' => 'SOW-OS-002', 'name' => 'Functional Spot Test',         'parent_code' => 'ON_SPOT',     'is_active' => true],
            ['type' => 'scope_of_work', 'code' => 'SOW-OS-003', 'name' => 'Safety Compliance Check',      'parent_code' => 'ON_SPOT',     'is_active' => true],

            // Inspection Types
            ['type' => 'inspection_type', 'code' => 'VISUAL',      'name' => 'Visual Inspection',      'parent_code' => null, 'is_active' => true],
            ['type' => 'inspection_type', 'code' => 'DIMENSIONAL', 'name' => 'Dimensional Inspection', 'parent_code' => null, 'is_active' => true],
            ['type' => 'inspection_type', 'code' => 'FUNCTIONAL',  'name' => 'Functional Test',        'parent_code' => null, 'is_active' => true],
            ['type' => 'inspection_type', 'code' => 'DESTRUCTIVE', 'name' => 'Destructive Test',       'parent_code' => null, 'is_active' => true],
            ['type' => 'inspection_type', 'code' => 'NDT',         'name' => 'Non-Destructive Test',   'parent_code' => null, 'is_active' => true],

            // Inspection Parameters
            ['type' => 'inspection_parameter', 'code' => 'SURFACE_FINISH',  'name' => 'Surface Finish',  'parent_code' => null, 'is_active' => true],
            ['type' => 'inspection_parameter', 'code' => 'WEIGHT',          'name' => 'Weight',          'parent_code' => null, 'is_active' => true],
            ['type' => 'inspection_parameter', 'code' => 'LENGTH',          'name' => 'Length',          'parent_code' => null, 'is_active' => true],
            ['type' => 'inspection_parameter', 'code' => 'PRESSURE_TEST',   'name' => 'Pressure Test',  'parent_code' => null, 'is_active' => true],
            ['type' => 'inspection_parameter', 'code' => 'HARDNESS',        'name' => 'Hardness',       'parent_code' => null, 'is_active' => true],
            ['type' => 'inspection_parameter', 'code' => 'CORROSION_CHECK', 'name' => 'Corrosion Check','parent_code' => null, 'is_active' => true],

            // Status References
            ['type' => 'status_reference', 'code' => 'NEW',             'name' => 'New',             'parent_code' => null, 'is_active' => true, 'meta' => ['group' => 'OPEN']],
            ['type' => 'status_reference', 'code' => 'IN_PROGRESS',     'name' => 'In Progress',     'parent_code' => null, 'is_active' => true, 'meta' => ['group' => 'OPEN']],
            ['type' => 'status_reference', 'code' => 'READY_TO_REVIEW', 'name' => 'Ready to Review', 'parent_code' => null, 'is_active' => true, 'meta' => ['group' => 'FOR_REVIEW']],
            ['type' => 'status_reference', 'code' => 'APPROVED',        'name' => 'Approved',        'parent_code' => null, 'is_active' => true, 'meta' => ['group' => 'COMPLETED']],
            ['type' => 'status_reference', 'code' => 'COMPLETED',       'name' => 'Completed',       'parent_code' => null, 'is_active' => true, 'meta' => ['group' => 'COMPLETED']],

            // Locations
            ['type' => 'location', 'code' => 'WH-A',   'name' => 'Warehouse A',          'parent_code' => null, 'is_active' => true],
            ['type' => 'location', 'code' => 'WH-B',   'name' => 'Warehouse B',          'parent_code' => null, 'is_active' => true],
            ['type' => 'location', 'code' => 'WH-C',   'name' => 'Warehouse C',          'parent_code' => null, 'is_active' => true],
            ['type' => 'location', 'code' => 'YARD-1',  'name' => 'Open Yard Section 1', 'parent_code' => null, 'is_active' => true],

            // Allocations
            ['type' => 'allocation', 'code' => 'PROJECT-ALPHA', 'name' => 'Project Alpha', 'parent_code' => null, 'is_active' => true],
            ['type' => 'allocation', 'code' => 'PROJECT-BETA',  'name' => 'Project Beta',  'parent_code' => null, 'is_active' => true],
            ['type' => 'allocation', 'code' => 'PROJECT-GAMMA', 'name' => 'Project Gamma', 'parent_code' => null, 'is_active' => true],
            ['type' => 'allocation', 'code' => 'GENERAL-STOCK', 'name' => 'General Stock', 'parent_code' => null, 'is_active' => true],

            // Owners
            ['type' => 'owner', 'code' => 'OWNER-COMPANY',  'name' => 'Company Owned',      'parent_code' => null, 'is_active' => true],
            ['type' => 'owner', 'code' => 'OWNER-CLIENT-A', 'name' => 'Client A',           'parent_code' => null, 'is_active' => true],
            ['type' => 'owner', 'code' => 'OWNER-CLIENT-B', 'name' => 'Client B',           'parent_code' => null, 'is_active' => true],
            ['type' => 'owner', 'code' => 'OWNER-VENDOR',   'name' => 'Vendor Consignment', 'parent_code' => null, 'is_active' => true],

            // Conditions
            ['type' => 'condition', 'code' => 'NEW',          'name' => 'New',          'parent_code' => null, 'is_active' => true],
            ['type' => 'condition', 'code' => 'GOOD',         'name' => 'Good',         'parent_code' => null, 'is_active' => true],
            ['type' => 'condition', 'code' => 'FAIR',         'name' => 'Fair',         'parent_code' => null, 'is_active' => true],
            ['type' => 'condition', 'code' => 'DAMAGED',      'name' => 'Damaged',      'parent_code' => null, 'is_active' => true],
            ['type' => 'condition', 'code' => 'NEEDS_REPAIR', 'name' => 'Needs Repair', 'parent_code' => null, 'is_active' => true],

            // Item Categories
            ['type' => 'item_category', 'code' => 'PIPE',       'name' => 'Pipe',                 'parent_code' => null, 'is_active' => true],
            ['type' => 'item_category', 'code' => 'VALVE',      'name' => 'Valve',                'parent_code' => null, 'is_active' => true],
            ['type' => 'item_category', 'code' => 'FITTING',    'name' => 'Fitting',              'parent_code' => null, 'is_active' => true],
            ['type' => 'item_category', 'code' => 'FLANGE',     'name' => 'Flange',               'parent_code' => null, 'is_active' => true],
            ['type' => 'item_category', 'code' => 'ELECTRICAL', 'name' => 'Electrical Component', 'parent_code' => null, 'is_active' => true],
            ['type' => 'item_category', 'code' => 'INSTRUMENT', 'name' => 'Instrument',           'parent_code' => null, 'is_active' => true],
            ['type' => 'item_category', 'code' => 'STRUCTURAL', 'name' => 'Structural Steel',     'parent_code' => null, 'is_active' => true],
        ];
    }
}
