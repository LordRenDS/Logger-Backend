<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ScheduleResource;
use App\Models\Pc;
use App\Services\SyncService;
use App\Http\Requests\SyncSchedulesRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class PcScheduleController extends Controller
{
    use AuthorizesRequests;

    protected SyncService $syncService;

    public function __construct(SyncService $syncService)
    {
        $this->syncService = $syncService;
    }

    public function index(Pc $pc): AnonymousResourceCollection
    {
        $this->authorize('view', $pc);
        return ScheduleResource::collection($pc->schedules()->paginate());
    }

    public function store(SyncSchedulesRequest $request, Pc $pc): JsonResponse
    {
        $this->authorize('view', $pc);

        $count = $this->syncService->syncSchedules($pc, $request->validated()['data']);

        return response()->json([
            'message' => 'Schedules synced successfully',
            'count' => $count,
        ], 201);
    }
}
