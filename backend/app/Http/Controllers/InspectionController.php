<?php

namespace App\Http\Controllers;

use App\Models\Inspection;
use App\Models\InspectionItem;
use App\Models\InspectionLot;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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

    public function updateHeader(string $id, Request $request)
    {
        $inspection = $this->findEditable($id);
        if ($inspection instanceof JsonResponse) return $inspection;

        $request->validate([
            'service_type'              => 'sometimes|string',
            'scope_of_work'             => 'sometimes|string',
            'location'                  => 'sometimes|nullable|string',
            'estimated_completion_date' => 'sometimes|nullable|date',
            'related_to'                => 'sometimes|nullable|string',
            'charge_to_customer'        => 'sometimes|boolean',
            'customer_name'             => 'sometimes|nullable|string',
        ]);

        if ($request->has('service_type'))              $inspection->service_type_category     = $request->input('service_type');
        if ($request->has('scope_of_work'))             $inspection->scope_of_work_code        = $request->input('scope_of_work');
        if ($request->has('location'))                  $inspection->location                  = $request->input('location');
        if ($request->has('estimated_completion_date')) $inspection->estimated_completion_date = $request->input('estimated_completion_date');
        if ($request->has('related_to'))                $inspection->related_to                = $request->input('related_to');
        if ($request->has('charge_to_customer'))        $inspection->charge_to_customer        = $request->boolean('charge_to_customer');
        if ($request->has('customer_name'))             $inspection->customer_name             = $request->input('customer_name');

        $inspection->save();

        return response()->json(['success' => true]);
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
