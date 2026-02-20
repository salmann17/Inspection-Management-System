<?php

namespace App\Http\Controllers;

use App\Models\Inspection;
use App\Models\InspectionItem;
use App\Models\InspectionLot;
use Illuminate\Http\Request;

class InspectionController extends Controller
{
    public function index(Request $request)
    {
        $query = Inspection::with('items.lots')->orderByDesc('created_at');

        if ($request->filled('status')) {
            $group = strtoupper($request->query('status'));
            $query->where('workflow_status_group', $group);
        }

        $paginated = $query->paginate(10);

        return response()->json([
            'data' => $paginated->items(),
            'meta' => [
                'current_page' => $paginated->currentPage(),
                'last_page'    => $paginated->lastPage(),
                'total'        => $paginated->total(),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'service_type'         => 'required|string',
            'scope_of_work'        => 'required|string',
            'items'                => 'required|array|min:1',
            'items.*.description'  => 'required|string',
            'items.*.qty_required' => 'required|integer|min:1',
            'items.*.lots'         => 'sometimes|array',
        ]);

        $inspection = Inspection::create([
            'request_no'                => Inspection::generateRequestNo(),
            'service_type_category'     => $data['service_type'],
            'scope_of_work_code'        => $data['scope_of_work'],
            'location'                  => $request->input('location'),
            'estimated_completion_date' => $request->input('estimated_completion_date'),
            'related_to'                => $request->input('related_to'),
            'charge_to_customer'        => $request->boolean('charge_to_customer'),
            'customer_name'             => $request->input('customer_name'),
            'status'                    => Inspection::STATUS_NEW,
            'total_items'               => count($data['items']),
            'total_lots'                => collect($data['items'])->sum(fn ($i) => count($i['lots'] ?? [])),
        ]);

        foreach ($data['items'] as $itemData) {
            $item = InspectionItem::create([
                'inspection_id' => $inspection->_id,
                'item_name'     => $itemData['description'],
                'qty_required'  => $itemData['qty_required'],
            ]);

            foreach ($itemData['lots'] ?? [] as $lotData) {
                InspectionLot::create([
                    'inspection_item_id' => $item->_id,
                    'lot'                => $lotData['lot'] ?? null,
                    'allocation'         => $lotData['allocation'] ?? null,
                    'owner'              => $lotData['owner'] ?? null,
                    'condition'          => $lotData['condition'] ?? null,
                    'available_qty'      => $lotData['available_qty'] ?? 0,
                    'sample_qty'         => $lotData['sample_qty'] ?? 0,
                ]);
            }
        }

        return response()->json([
            'success'       => true,
            'inspection_id' => $inspection->_id,
        ], 201);
    }

    public function show(string $id)
    {
        $inspection = Inspection::with('items.lots')->where('_id', $id)->first();

        if (!$inspection) {
            return response()->json(['message' => 'Inspection not found.'], 404);
        }

        return response()->json([
            'id'                        => $inspection->id,
            'inspection_no'             => $inspection->request_no,
            'service_type'              => $inspection->service_type_category,
            'scope_of_work'             => $inspection->scope_of_work_code,
            'location'                  => $inspection->location,
            'estimated_completion_date' => $inspection->estimated_completion_date?->toDateString(),
            'related_to'                => $inspection->related_to,
            'charge_to_customer'        => $inspection->charge_to_customer,
            'customer_name'             => $inspection->customer_name,
            'status'                    => $inspection->status,
            'workflow_status_group'     => $inspection->workflow_status_group,
            'total_items'               => $inspection->total_items,
            'total_lots'                => $inspection->total_lots,
            'created_at'                => $inspection->created_at?->toDateTimeString(),
            'items'                     => $inspection->items->map(fn ($item) => [
                'id'           => $item->id,
                'description'  => $item->item_name,
                'qty_required' => $item->qty_required,
                'lots'         => $item->lots->map(fn ($lot) => [
                    'id'            => $lot->id,
                    'lot'           => $lot->lot,
                    'allocation'    => $lot->allocation,
                    'owner'         => $lot->owner,
                    'condition'     => $lot->condition,
                    'available_qty' => $lot->available_qty,
                    'sample_qty'    => $lot->sample_qty,
                ]),
            ]),
        ]);
    }
}
