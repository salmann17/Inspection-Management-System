<?php

namespace Database\Seeders;

use App\Models\Inspection;
use App\Models\InspectionItem;
use App\Models\InspectionLot;
use Illuminate\Database\Seeder;

class InspectionSeeder extends Seeder
{
    public function run(): void
    {
        // Clean existing data
        InspectionLot::truncate();
        InspectionItem::truncate();
        Inspection::truncate();

        $inspections = $this->getInspections();

        foreach ($inspections as $data) {
            $items = $data['items'];
            unset($data['items']);

            $inspection = Inspection::create($data);

            $totalItems = 0;
            $totalLots  = 0;

            foreach ($items as $itemData) {
                $lots = $itemData['lots'];
                unset($itemData['lots']);

                $itemData['inspection_id'] = $inspection->_id;
                $item = InspectionItem::create($itemData);
                $totalItems++;

                foreach ($lots as $lotData) {
                    $lotData['inspection_item_id'] = $item->_id;
                    InspectionLot::create($lotData);
                    $totalLots++;
                }
            }

            $inspection->update([
                'total_items' => $totalItems,
                'total_lots'  => $totalLots,
            ]);
        }

        $this->command->info('Inspections seeded: ' . count($inspections) . ' records across all statuses.');
    }

    // 10 inspections: NEW=2, IN_PROGRESS=2, READY_TO_REVIEW=3, APPROVED=1, COMPLETED=2
    private function getInspections(): array
    {
        return [
            // NEW (2)
            [
                'request_no'               => 'INS-20260210-0001',
                'service_type_category'    => 'NEW_ARRIVAL',
                'scope_of_work_code'       => 'SOW-NA-001',
                'estimated_completion_date' => now()->addDays(14),
                'related_to'               => null,
                'charge_to_customer'       => false,
                'customer_name'            => null,
                'status'                   => Inspection::STATUS_NEW,
                'created_by'               => 'admin',
                'submitted_at'             => null,
                'completed_at'             => null,
                'created_at'               => now()->subDays(9),
                'updated_at'               => now()->subDays(9),
                'items' => [
                    [
                        'item_code' => 'PIP-001', 'item_name' => 'Carbon Steel Pipe 6" Sch40', 'item_category' => 'PIPE',
                        'available_qty' => 120, 'qty_required' => 10, 'inspection_required' => true, 'remarks' => null,
                        'lots' => [
                            ['lot' => 'LOT-00001', 'allocation' => 'PROJECT-ALPHA', 'owner' => 'OWNER-COMPANY', 'condition' => 'NEW', 'sample_qty' => 5],
                            ['lot' => 'LOT-00002', 'allocation' => 'PROJECT-ALPHA', 'owner' => 'OWNER-COMPANY', 'condition' => 'NEW', 'sample_qty' => 5],
                        ],
                    ],
                    [
                        'item_code' => 'VLV-001', 'item_name' => 'Gate Valve 6" 150#', 'item_category' => 'VALVE',
                        'available_qty' => 30, 'qty_required' => 3, 'inspection_required' => true, 'remarks' => 'Check body casting',
                        'lots' => [
                            ['lot' => 'LOT-00008', 'allocation' => 'PROJECT-ALPHA', 'owner' => 'OWNER-CLIENT-A', 'condition' => 'NEW', 'sample_qty' => 3],
                        ],
                    ],
                ],
            ],

            [
                'request_no'               => 'INS-20260211-0001',
                'service_type_category'    => 'ON_SPOT',
                'scope_of_work_code'       => 'SOW-OS-001',
                'estimated_completion_date' => now()->addDays(7),
                'related_to'               => null,
                'charge_to_customer'       => true,
                'customer_name'            => 'PT Indo Energy',
                'status'                   => Inspection::STATUS_NEW,
                'created_by'               => 'inspector_01',
                'submitted_at'             => null,
                'completed_at'             => null,
                'created_at'               => now()->subDays(8),
                'updated_at'               => now()->subDays(8),
                'items' => [
                    [
                        'item_code' => 'ELC-001', 'item_name' => 'Junction Box Explosion Proof', 'item_category' => 'ELECTRICAL',
                        'available_qty' => 15, 'qty_required' => 2, 'inspection_required' => true, 'remarks' => null,
                        'lots' => [
                            ['lot' => 'LOT-00020', 'allocation' => 'PROJECT-BETA', 'owner' => 'OWNER-CLIENT-B', 'condition' => 'GOOD', 'sample_qty' => 2],
                        ],
                    ],
                ],
            ],

            // IN_PROGRESS (2)
            [
                'request_no'               => 'INS-20260207-0001',
                'service_type_category'    => 'MAINTENANCE',
                'scope_of_work_code'       => 'SOW-MT-001',
                'estimated_completion_date' => now()->addDays(5),
                'related_to'               => 'WO-2026-0044',
                'charge_to_customer'       => false,
                'customer_name'            => null,
                'status'                   => Inspection::STATUS_IN_PROGRESS,
                'created_by'               => 'inspector_02',
                'submitted_at'             => null,
                'completed_at'             => null,
                'created_at'               => now()->subDays(12),
                'updated_at'               => now()->subDays(5),
                'items' => [
                    [
                        'item_code' => 'VLV-002', 'item_name' => 'Ball Valve 4" 300#', 'item_category' => 'VALVE',
                        'available_qty' => 20, 'qty_required' => 5, 'inspection_required' => true, 'remarks' => 'Verify seat ring',
                        'lots' => [
                            ['lot' => 'LOT-00009', 'allocation' => 'PROJECT-GAMMA', 'owner' => 'OWNER-COMPANY', 'condition' => 'FAIR', 'sample_qty' => 3],
                            ['lot' => 'LOT-00010', 'allocation' => 'PROJECT-GAMMA', 'owner' => 'OWNER-COMPANY', 'condition' => 'GOOD', 'sample_qty' => 2],
                        ],
                    ],
                    [
                        'item_code' => 'FIT-001', 'item_name' => 'Elbow 90° 6" Sch40', 'item_category' => 'FITTING',
                        'available_qty' => 50, 'qty_required' => 8, 'inspection_required' => true, 'remarks' => null,
                        'lots' => [
                            ['lot' => 'LOT-00014', 'allocation' => 'PROJECT-GAMMA', 'owner' => 'OWNER-VENDOR', 'condition' => 'NEW', 'sample_qty' => 8],
                        ],
                    ],
                ],
            ],

            [
                'request_no'               => 'INS-20260208-0001',
                'service_type_category'    => 'NEW_ARRIVAL',
                'scope_of_work_code'       => 'SOW-NA-003',
                'estimated_completion_date' => now()->addDays(3),
                'related_to'               => null,
                'charge_to_customer'       => false,
                'customer_name'            => null,
                'status'                   => Inspection::STATUS_IN_PROGRESS,
                'created_by'               => 'admin',
                'submitted_at'             => null,
                'completed_at'             => null,
                'created_at'               => now()->subDays(11),
                'updated_at'               => now()->subDays(3),
                'items' => [
                    [
                        'item_code' => 'FLG-001', 'item_name' => 'Weld Neck Flange 6" 150#', 'item_category' => 'FLANGE',
                        'available_qty' => 40, 'qty_required' => 10, 'inspection_required' => true, 'remarks' => 'Check face finish',
                        'lots' => [
                            ['lot' => 'LOT-00016', 'allocation' => 'PROJECT-ALPHA', 'owner' => 'OWNER-COMPANY', 'condition' => 'NEW', 'sample_qty' => 10],
                        ],
                    ],
                ],
            ],

            // READY_TO_REVIEW (3)
            [
                'request_no'               => 'INS-20260201-0001',
                'service_type_category'    => 'NEW_ARRIVAL',
                'scope_of_work_code'       => 'SOW-NA-002',
                'estimated_completion_date' => now()->addDays(2),
                'related_to'               => null,
                'charge_to_customer'       => true,
                'customer_name'            => 'Chevron Pacific',
                'status'                   => Inspection::STATUS_READY_TO_REVIEW,
                'created_by'               => 'inspector_01',
                'submitted_at'             => now()->subDays(3),
                'completed_at'             => null,
                'created_at'               => now()->subDays(18),
                'updated_at'               => now()->subDays(3),
                'items' => [
                    [
                        'item_code' => 'PIP-002', 'item_name' => 'Stainless Steel Pipe 4" Sch10S', 'item_category' => 'PIPE',
                        'available_qty' => 80, 'qty_required' => 15, 'inspection_required' => true, 'remarks' => null,
                        'lots' => [
                            ['lot' => 'LOT-00004', 'allocation' => 'PROJECT-BETA', 'owner' => 'OWNER-CLIENT-A', 'condition' => 'NEW', 'sample_qty' => 10],
                            ['lot' => 'LOT-00005', 'allocation' => 'PROJECT-BETA', 'owner' => 'OWNER-CLIENT-A', 'condition' => 'GOOD', 'sample_qty' => 5],
                        ],
                    ],
                    [
                        'item_code' => 'INS-001', 'item_name' => 'Pressure Transmitter 0-100 PSI', 'item_category' => 'INSTRUMENT',
                        'available_qty' => 10, 'qty_required' => 3, 'inspection_required' => true, 'remarks' => 'Calibration cert required',
                        'lots' => [
                            ['lot' => 'LOT-00024', 'allocation' => 'PROJECT-BETA', 'owner' => 'OWNER-CLIENT-A', 'condition' => 'NEW', 'sample_qty' => 3],
                        ],
                    ],
                ],
            ],

            [
                'request_no'               => 'INS-20260203-0001',
                'service_type_category'    => 'MAINTENANCE',
                'scope_of_work_code'       => 'SOW-MT-002',
                'estimated_completion_date' => now()->addDays(1),
                'related_to'               => 'WO-2026-0038',
                'charge_to_customer'       => false,
                'customer_name'            => null,
                'status'                   => Inspection::STATUS_READY_TO_REVIEW,
                'created_by'               => 'inspector_02',
                'submitted_at'             => now()->subDays(2),
                'completed_at'             => null,
                'created_at'               => now()->subDays(16),
                'updated_at'               => now()->subDays(2),
                'items' => [
                    [
                        'item_code' => 'VLV-003', 'item_name' => 'Check Valve 3" 150#', 'item_category' => 'VALVE',
                        'available_qty' => 25, 'qty_required' => 5, 'inspection_required' => true, 'remarks' => 'Disc wear check',
                        'lots' => [
                            ['lot' => 'LOT-00011', 'allocation' => 'GENERAL-STOCK', 'owner' => 'OWNER-COMPANY', 'condition' => 'FAIR', 'sample_qty' => 5],
                        ],
                    ],
                ],
            ],

            [
                'request_no'               => 'INS-20260205-0001',
                'service_type_category'    => 'ON_SPOT',
                'scope_of_work_code'       => 'SOW-OS-003',
                'estimated_completion_date' => now(),
                'related_to'               => null,
                'charge_to_customer'       => true,
                'customer_name'            => 'Pertamina Hulu Energi',
                'status'                   => Inspection::STATUS_READY_TO_REVIEW,
                'created_by'               => 'admin',
                'submitted_at'             => now()->subDays(1),
                'completed_at'             => null,
                'created_at'               => now()->subDays(14),
                'updated_at'               => now()->subDays(1),
                'items' => [
                    [
                        'item_code' => 'STR-001', 'item_name' => 'H-Beam 200x200x8x12mm', 'item_category' => 'STRUCTURAL',
                        'available_qty' => 200, 'qty_required' => 20, 'inspection_required' => true, 'remarks' => 'Check for corrosion',
                        'lots' => [
                            ['lot' => 'LOT-00025', 'allocation' => 'PROJECT-ALPHA', 'owner' => 'OWNER-CLIENT-B', 'condition' => 'GOOD', 'sample_qty' => 12],
                            ['lot' => 'LOT-00026', 'allocation' => 'PROJECT-ALPHA', 'owner' => 'OWNER-CLIENT-B', 'condition' => 'FAIR', 'sample_qty' => 8],
                        ],
                    ],
                    [
                        'item_code' => 'ELC-002', 'item_name' => 'Cable Tray 300mm Hot-Dip', 'item_category' => 'ELECTRICAL',
                        'available_qty' => 60, 'qty_required' => 6, 'inspection_required' => true, 'remarks' => null,
                        'lots' => [
                            ['lot' => 'LOT-00022', 'allocation' => 'PROJECT-ALPHA', 'owner' => 'OWNER-CLIENT-B', 'condition' => 'NEW', 'sample_qty' => 6],
                        ],
                    ],
                ],
            ],

            // APPROVED (1)
            [
                'request_no'               => 'INS-20260115-0001',
                'service_type_category'    => 'NEW_ARRIVAL',
                'scope_of_work_code'       => 'SOW-NA-004',
                'estimated_completion_date' => now()->subDays(10),
                'related_to'               => null,
                'charge_to_customer'       => false,
                'customer_name'            => null,
                'status'                   => Inspection::STATUS_APPROVED,
                'created_by'               => 'inspector_01',
                'submitted_at'             => now()->subDays(15),
                'completed_at'             => null,
                'created_at'               => now()->subDays(35),
                'updated_at'               => now()->subDays(10),
                'items' => [
                    [
                        'item_code' => 'PIP-003', 'item_name' => 'Galvanized Pipe 2" Sch40', 'item_category' => 'PIPE',
                        'available_qty' => 200, 'qty_required' => 30, 'inspection_required' => true, 'remarks' => null,
                        'lots' => [
                            ['lot' => 'LOT-00006', 'allocation' => 'GENERAL-STOCK', 'owner' => 'OWNER-COMPANY', 'condition' => 'NEW', 'sample_qty' => 15],
                            ['lot' => 'LOT-00007', 'allocation' => 'GENERAL-STOCK', 'owner' => 'OWNER-COMPANY', 'condition' => 'NEW', 'sample_qty' => 15],
                        ],
                    ],
                ],
            ],

            // COMPLETED (2)
            [
                'request_no'               => 'INS-20260105-0001',
                'service_type_category'    => 'MAINTENANCE',
                'scope_of_work_code'       => 'SOW-MT-003',
                'estimated_completion_date' => now()->subDays(30),
                'related_to'               => 'WO-2026-0021',
                'charge_to_customer'       => false,
                'customer_name'            => null,
                'status'                   => Inspection::STATUS_COMPLETED,
                'created_by'               => 'inspector_02',
                'submitted_at'             => now()->subDays(40),
                'completed_at'             => now()->subDays(30),
                'created_at'               => now()->subDays(45),
                'updated_at'               => now()->subDays(30),
                'items' => [
                    [
                        'item_code' => 'FIT-002', 'item_name' => 'Tee Equal 4" Sch40', 'item_category' => 'FITTING',
                        'available_qty' => 35, 'qty_required' => 5, 'inspection_required' => true, 'remarks' => 'Weld seam checked',
                        'lots' => [
                            ['lot' => 'LOT-00015', 'allocation' => 'PROJECT-GAMMA', 'owner' => 'OWNER-VENDOR', 'condition' => 'GOOD', 'sample_qty' => 5],
                        ],
                    ],
                    [
                        'item_code' => 'FLG-002', 'item_name' => 'Slip On Flange 4" 300#', 'item_category' => 'FLANGE',
                        'available_qty' => 18, 'qty_required' => 4, 'inspection_required' => true, 'remarks' => null,
                        'lots' => [
                            ['lot' => 'LOT-00018', 'allocation' => 'PROJECT-GAMMA', 'owner' => 'OWNER-VENDOR', 'condition' => 'NEW', 'sample_qty' => 4],
                        ],
                    ],
                ],
            ],

            [
                'request_no'               => 'INS-20260110-0001',
                'service_type_category'    => 'ON_SPOT',
                'scope_of_work_code'       => 'SOW-OS-002',
                'estimated_completion_date' => now()->subDays(20),
                'related_to'               => null,
                'charge_to_customer'       => true,
                'customer_name'            => 'TotalEnergies EP',
                'status'                   => Inspection::STATUS_COMPLETED,
                'created_by'               => 'admin',
                'submitted_at'             => now()->subDays(30),
                'completed_at'             => now()->subDays(20),
                'created_at'               => now()->subDays(40),
                'updated_at'               => now()->subDays(20),
                'items' => [
                    [
                        'item_code' => 'VLV-001', 'item_name' => 'Gate Valve 6" 150#', 'item_category' => 'VALVE',
                        'available_qty' => 30, 'qty_required' => 6, 'inspection_required' => true, 'remarks' => 'Hydro test passed',
                        'lots' => [
                            ['lot' => 'LOT-00008', 'allocation' => 'PROJECT-ALPHA', 'owner' => 'OWNER-CLIENT-A', 'condition' => 'NEW', 'sample_qty' => 3],
                            ['lot' => 'LOT-00009', 'allocation' => 'PROJECT-BETA',  'owner' => 'OWNER-CLIENT-A', 'condition' => 'GOOD', 'sample_qty' => 3],
                        ],
                    ],
                ],
            ],
        ];
    }
}
