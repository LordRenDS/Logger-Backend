<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ScheduleResource;
use App\Models\Pc;
use App\Services\SyncService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class PcScheduleController extends Controller
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
        return response()->json(ScheduleResource::collection($pc->schedules()->paginate()));
    }

    public function store(Request $request, Pc $pc): JsonResponse
    {
        $this->authorize('view', $pc);

        $validator = Validator::make($request->all(), [
            'data' => 'required|array',
            'data.*.timestamp' => 'required|date',
            'data.*.status' => 'required|string|in:on,off',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $count = $this->syncService->syncSchedules($pc, $request->input('data'));

        return response()->json([
            'message' => 'Schedules synced successfully',
            'count' => $count,
        ], 201);
    }
}
