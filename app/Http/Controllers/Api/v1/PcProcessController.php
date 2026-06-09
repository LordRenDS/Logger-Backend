<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProcessResource;
use App\Models\Pc;
use App\Services\SyncService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class PcProcessController extends Controller
{
    use AuthorizesRequests;

    protected SyncService $syncService;

    public function __construct(SyncService $syncService)
    {
        $this->syncService = $syncService;
    }

    public function index(Pc $pc): JsonResponse
    {
        $this->authorize('view', $pc);
        return response()->json(ProcessResource::collection($pc->processes()->paginate()));
    }

    public function store(Request $request, Pc $pc): JsonResponse
    {
        $this->authorize('view', $pc);

        $validator = Validator::make($request->all(), [
            'data' => 'required|array',
            'data.*.process_start' => 'required|date',
            'data.*.process_name' => 'required|string',
            'data.*.window_name' => 'required|string',
            'data.*.duration' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $count = $this->syncService->syncProcesses($pc, $request->input('data'));

        return response()->json([
            'message' => 'Processes synced successfully',
            'count' => $count,
        ], 201);
    }
}
