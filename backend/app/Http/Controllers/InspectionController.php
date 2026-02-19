<?php

namespace App\Http\Controllers;

use App\Models\Inspection;
use Illuminate\Http\Request;

class InspectionController extends Controller
{
    public function index(Request $request)
    {
        $query = Inspection::orderByDesc('created_at');

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
}
