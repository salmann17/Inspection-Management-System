<?php

namespace App\Http\Controllers;

use App\Models\Inspection;
use App\Models\InspectionCharge;
use App\Models\InspectionItem;
use App\Models\InspectionLot;
use App\Models\MasterData;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InspectionController extends Controller
{
    private function findEditable(string $id): Inspection|JsonResponse
    {
        $inspection = Inspection::where('_id', $id)->first();

        if (!$inspection) {
            return response()->json(['message' => 'Inspection not found.'], 404);
        }

        if (!$inspection->isEditable()) {
            return response()->json(['message' => 'Inspection cannot be modified in current status.'], 400);
        }

        return $inspection;
    }

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

        $scopeCode   = $inspection->scope_of_work_code;
        $scopeRecord = MasterData::where('type', 'scope_of_work')->where('code', $scopeCode)->first();
        $scopeItems  = MasterData::where('type', 'scope_item')->where('parent_code', $scopeCode)->get(['code', 'name', 'description']);

        $charges = $inspection->charge_to_customer
            ? $inspection->charges->map(fn ($c) => [
                'id'                  => $c->id,
                'order_no'            => $c->order_no,
                'service_description' => $c->service_description,
                'qty'                 => $c->qty,
                'unit_price'          => $c->unit_price,
            ])
            : [];

        return response()->json([
            'id'                        => $inspection->id,
            'inspection_no'             => $inspection->request_no,
            'service_type'              => $inspection->service_type_category,
            'scope_of_work'             => $scopeRecord ? [
                'code'           => $scopeRecord->code,
                'name'           => $scopeRecord->name,
                'included_items' => $scopeItems->map(fn ($s) => ['code' => $s->code, 'name' => $s->name, 'description' => $s->description])->values(),
            ] : ['code' => $scopeCode, 'name' => $scopeCode, 'included_items' => []],
            'location'                  => $inspection->location,
            'estimated_completion_date' => $inspection->estimated_completion_date?->toDateString(),
            'related_to'                => $inspection->related_to,
            'charge_to_customer'        => $inspection->charge_to_customer,
            'customer_name'             => $inspection->customer_name,
            'charges'                   => $charges,
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

    public function addCharge(string $id, Request $request)
    {
        $inspection = Inspection::where('_id', $id)->first();

        if (!$inspection) {
            return response()->json(['message' => 'Inspection not found.'], 404);
        }

        if (!$inspection->charge_to_customer) {
            return response()->json(['message' => 'Charges are not enabled for this inspection.'], 400);
        }

        if ($inspection->workflow_status_group === 'COMPLETED') {
            return response()->json(['message' => 'Cannot add charges to a completed inspection.'], 400);
        }

        $data = $request->validate([
            'order_no'            => 'required|string',
            'service_description' => 'required|string',
            'qty'                 => 'required|integer|min:1',
            'unit_price'          => 'required|numeric|min:0',
        ]);

        InspectionCharge::create([
            'inspection_id'       => $inspection->id,
            'order_no'            => $data['order_no'],
            'service_description' => $data['service_description'],
            'qty'                 => $data['qty'],
            'unit_price'          => $data['unit_price'],
        ]);

        $charges = $inspection->charges()->get()->map(fn ($c) => [
            'id'                  => $c->id,
            'order_no'            => $c->order_no,
            'service_description' => $c->service_description,
            'qty'                 => $c->qty,
            'unit_price'          => $c->unit_price,
        ]);

        return response()->json(['charges' => $charges], 201);
    }

    public function updateStatus(string $id, Request $request)
    {
        $request->validate(['status' => 'required|string']);

        $inspection = Inspection::where('_id', $id)->first();

        if (!$inspection) {
            return response()->json(['message' => 'Inspection not found.'], 404);
        }

        $target  = strtoupper($request->input('status'));
        $current = $inspection->workflow_status_group;
        $allowed = Inspection::GROUP_TRANSITIONS[$current] ?? [];

        if (!in_array($target, $allowed, true)) {
            return response()->json(['message' => 'Invalid status transition.'], 400);
        }

        $inspection->status = Inspection::GROUP_TO_STATUS[$target];
        $inspection->save();

        return response()->json([
            'id'                    => $inspection->id,
            'status'                => $inspection->status,
            'workflow_status_group' => $inspection->workflow_status_group,
        ]);
    }

    public function update(string $id, Request $request)
    {
        $inspection = $this->findEditable($id);
        if ($inspection instanceof JsonResponse) return $inspection;

        $data = $request->validate([
            'service_type'                 => 'required|string',
            'scope_of_work'                => 'required|string',
            'location'                     => 'nullable|string',
            'estimated_completion_date'    => 'nullable|date',
            'related_to'                   => 'nullable|string',
            'charge_to_customer'           => 'required|boolean',
            'customer_name'                => 'nullable|string',
            'items'                        => 'required|array|min:1',
            'items.*.description'          => 'required|string',
            'items.*.qty_required'         => 'required|integer|min:1',
            'items.*.lots'                 => 'sometimes|array',
            'items.*.lots.*.lot'           => 'nullable|string',
            'items.*.lots.*.allocation'    => 'nullable|string',
            'items.*.lots.*.owner'         => 'nullable|string',
            'items.*.lots.*.condition'     => 'nullable|string',
            'items.*.lots.*.available_qty' => 'integer|min:0',
            'items.*.lots.*.sample_qty'    => 'integer|min:0',
        ]);

        DB::transaction(function () use ($inspection, $data) {
            $itemIds = InspectionItem::where('inspection_id', $inspection->id)->pluck('_id');
            InspectionLot::whereIn('inspection_item_id', $itemIds)->delete();
            InspectionItem::where('inspection_id', $inspection->id)->delete();

            $inspection->service_type_category     = $data['service_type'];
            $inspection->scope_of_work_code        = $data['scope_of_work'];
            $inspection->location                  = $data['location'] ?? null;
            $inspection->estimated_completion_date = $data['estimated_completion_date'] ?? null;
            $inspection->related_to                = $data['related_to'] ?? null;
            $inspection->charge_to_customer        = $data['charge_to_customer'];
            $inspection->customer_name             = $data['customer_name'] ?? null;
            $inspection->total_items               = count($data['items']);
            $inspection->total_lots                = collect($data['items'])->sum(fn ($i) => count($i['lots'] ?? []));
            $inspection->save();

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
        });

        $inspection->load('items.lots');

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

    public function updateItem(string $id, string $itemId, Request $request)
    {
        $inspection = $this->findEditable($id);
        if ($inspection instanceof JsonResponse) return $inspection;

        $item = InspectionItem::where('_id', $itemId)
            ->where('inspection_id', $id)
            ->first();

        if (!$item) {
            return response()->json(['message' => 'Item not found.'], 404);
        }

        $request->validate([
            'description'  => 'sometimes|string',
            'qty_required' => 'sometimes|integer|min:1',
        ]);

        if ($request->has('description'))  $item->item_name    = $request->input('description');
        if ($request->has('qty_required')) $item->qty_required = $request->input('qty_required');

        $item->save();

        return response()->json(['success' => true]);
    }

    public function updateLot(string $id, string $itemId, string $lotId, Request $request)
    {
        $inspection = $this->findEditable($id);
        if ($inspection instanceof JsonResponse) return $inspection;

        $lot = InspectionLot::where('_id', $lotId)
            ->where('inspection_item_id', $itemId)
            ->first();

        if (!$lot) {
            return response()->json(['message' => 'Lot not found.'], 404);
        }

        $request->validate([
            'lot'           => 'sometimes|nullable|string',
            'allocation'    => 'sometimes|nullable|string',
            'owner'         => 'sometimes|nullable|string',
            'condition'     => 'sometimes|nullable|string',
            'available_qty' => 'sometimes|integer|min:0',
            'sample_qty'    => 'sometimes|integer|min:0',
        ]);

        foreach (['lot', 'allocation', 'owner', 'condition', 'available_qty', 'sample_qty'] as $field) {
            if ($request->has($field)) $lot->$field = $request->input($field);
        }

        $lot->save();

        return response()->json(['success' => true]);
    }
}
