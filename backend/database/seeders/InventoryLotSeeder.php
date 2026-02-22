<?php

namespace Database\Seeders;

use App\Models\InventoryLot;
use Illuminate\Database\Seeder;

class InventoryLotSeeder extends Seeder
{
    public function run(): void
    {
        $records = $this->getData();

        InventoryLot::truncate();
        InventoryLot::insert($records);

        $this->command->info('InventoryLots seeded: ' . count($records) . ' records.');
    }

    private function getData(): array
    {
        $now = now();

        $items = [
            ['item_code' => 'PIP-001', 'item_name' => 'Carbon Steel Pipe 6" Sch40',    'item_category' => 'PIPE'],
            ['item_code' => 'PIP-002', 'item_name' => 'Stainless Steel Pipe 4" Sch10S', 'item_category' => 'PIPE'],
            ['item_code' => 'PIP-003', 'item_name' => 'Galvanized Pipe 2" Sch40',       'item_category' => 'PIPE'],
            ['item_code' => 'VLV-001', 'item_name' => 'Gate Valve 6" 150#',             'item_category' => 'VALVE'],
            ['item_code' => 'VLV-002', 'item_name' => 'Ball Valve 4" 300#',             'item_category' => 'VALVE'],
            ['item_code' => 'VLV-003', 'item_name' => 'Check Valve 3" 150#',            'item_category' => 'VALVE'],
            ['item_code' => 'FIT-001', 'item_name' => 'Elbow 90° 6" Sch40',            'item_category' => 'FITTING'],
            ['item_code' => 'FIT-002', 'item_name' => 'Tee Equal 4" Sch40',             'item_category' => 'FITTING'],
            ['item_code' => 'FLG-001', 'item_name' => 'Weld Neck Flange 6" 150#',       'item_category' => 'FLANGE'],
            ['item_code' => 'FLG-002', 'item_name' => 'Slip On Flange 4" 300#',         'item_category' => 'FLANGE'],
            ['item_code' => 'ELC-001', 'item_name' => 'Junction Box Explosion Proof',   'item_category' => 'ELECTRICAL'],
            ['item_code' => 'ELC-002', 'item_name' => 'Cable Tray 300mm Hot-Dip',       'item_category' => 'ELECTRICAL'],
            ['item_code' => 'INS-001', 'item_name' => 'Pressure Transmitter 0-100 PSI', 'item_category' => 'INSTRUMENT'],
            ['item_code' => 'STR-001', 'item_name' => 'H-Beam 200x200x8x12mm',         'item_category' => 'STRUCTURAL'],
        ];

        $allocations = ['PROJECT-ALPHA', 'PROJECT-BETA', 'PROJECT-GAMMA', 'GENERAL-STOCK'];
        $owners      = ['OWNER-COMPANY', 'OWNER-CLIENT-A', 'OWNER-CLIENT-B', 'OWNER-VENDOR'];
        $conditions  = ['NEW', 'GOOD', 'FAIR', 'DAMAGED', 'NEEDS_REPAIR'];
        $warehouses  = ['WH-A', 'WH-B', 'WH-C', 'YARD-1'];

        $records = [];
        $lotSeq  = 1;

        foreach ($items as $item) {
            // Generate 2-4 lots per item with varying attributes
            $lotCount = rand(2, 4);

            for ($i = 0; $i < $lotCount; $i++) {
                $records[] = [
                    'item_code'          => $item['item_code'],
                    'item_name'          => $item['item_name'],
                    'item_category'      => $item['item_category'],
                    'lot'                => 'LOT-' . str_pad($lotSeq++, 5, '0', STR_PAD_LEFT),
                    'allocation'         => $allocations[array_rand($allocations)],
                    'owner'              => $owners[array_rand($owners)],
                    'condition'          => $conditions[array_rand($conditions)],
                    'available_qty'      => rand(5, 500),
                    'warehouse_location' => $warehouses[array_rand($warehouses)],
                    'created_at'         => $now,
                    'updated_at'         => $now,
                ];
            }
        }

        return $records;
    }
}
