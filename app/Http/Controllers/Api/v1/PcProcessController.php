<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProcessResource;
use App\Models\Pc;
use App\Services\SyncService;
use App\Http\Requests\SyncProcessesRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class PcProcessController extends Controller
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
        return ProcessResource::collection($pc->processes()->paginate());
    }

    public function store(SyncProcessesRequest $request, Pc $pc): JsonResponse
    {
        $this->authorize('view', $pc);

        $count = $this->syncService->syncProcesses($pc, $request->validated()['data']);

        return response()->json([
            'message' => 'Processes synced successfully',
            'count' => $count,
        ], 201);
    }
}
