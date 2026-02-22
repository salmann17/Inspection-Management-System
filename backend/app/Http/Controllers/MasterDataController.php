<?php

namespace App\Http\Controllers;

use App\Models\InventoryLot;
use App\Models\MasterData;

class MasterDataController extends Controller
{
    public function index()
    {
        $masterData = MasterData::active()
            ->whereIn('type', ['service_type_category', 'scope_of_work', 'scope_item'])
            ->get(['type', 'code', 'name', 'parent_code', 'meta']);

        $grouped = $masterData->groupBy('type');

        return response()->json([
            'service_types' => $grouped->get('service_type_category', collect())->values(),
            'scopes'        => $grouped->get('scope_of_work', collect())->values(),
            'scope_items'   => $grouped->get('scope_item', collect())->values(),
            'inventory_lots' => InventoryLot::all([
                'lot', 'allocation', 'owner', 'condition',
                'available_qty', 'item_code', 'item_name',
            ]),
        ]);
    }
}
