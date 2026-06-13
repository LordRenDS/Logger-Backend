<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\SyncProcessesRequest;
use App\Http\Resources\ProcessResource;
use App\Models\Pc;
use App\Services\SyncService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PcProcessController extends Controller
{
    use AuthorizesRequests;

    protected SyncService $syncService;

    public function __construct(SyncService $syncService)
    {
        $this->syncService = $syncService;
    }

    /**
     * @OA\Get(
     *     path="/api/v1/pcs/{pc}/processes",
     *     tags={"Processes"},
     *     summary="List paginated processes activity logs for a specific PC",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="pc",
     *         in="path",
     *         required=true,
     *         description="The unique_id of the PC",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         required=false,
     *         description="Page number",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Paginated processes logs list",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="pc_id", type="integer", example=1),
     *                     @OA\Property(property="process_start", type="string", format="date-time", example="2026-06-13T12:00:00Z"),
     *                     @OA\Property(property="process_name", type="string", example="chrome.exe"),
     *                     @OA\Property(property="window_name", type="string", example="Google Search"),
     *                     @OA\Property(property="duration", type="integer", example=120)
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="links",
     *                 type="object",
     *                 @OA\Property(property="first", type="string"),
     *                 @OA\Property(property="last", type="string"),
     *                 @OA\Property(property="prev", type="string", nullable=true),
     *                 @OA\Property(property="next", type="string", nullable=true)
     *             ),
     *             @OA\Property(
     *                 property="meta",
     *                 type="object",
     *                 @OA\Property(property="current_page", type="integer"),
     *                 @OA\Property(property="from", type="integer"),
     *                 @OA\Property(property="last_page", type="integer"),
     *                 @OA\Property(property="per_page", type="integer"),
     *                 @OA\Property(property="to", type="integer"),
     *                 @OA\Property(property="total", type="integer")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=403, description="Forbidden (not user's device)"),
     *     @OA\Response(response=404, description="PC not found")
     * )
     */
    public function index(Pc $pc): AnonymousResourceCollection
    {
        $this->authorize('view', $pc);

        return ProcessResource::collection($pc->processes()->paginate());
    }

    /**
     * @OA\Post(
     *     path="/api/v1/pcs/{pc}/processes",
     *     tags={"Processes"},
     *     summary="Bulk sync processes activity logs for a specific PC",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="pc",
     *         in="path",
     *         required=true,
     *         description="The unique_id of the PC",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"data"},
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 description="Array of process activity logs to sync",
     *                 @OA\Items(
     *                     required={"process_start", "process_name", "window_name", "duration"},
     *                     @OA\Property(property="process_start", type="string", format="date-time", example="2026-06-13T12:00:00Z"),
     *                     @OA\Property(property="process_name", type="string", example="chrome.exe"),
     *                     @OA\Property(property="window_name", type="string", example="Google Search"),
     *                     @OA\Property(property="duration", type="integer", example=60)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Processes synced successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Processes synced successfully"),
     *             @OA\Property(property="count", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=403, description="Forbidden (not user's device)"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
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
