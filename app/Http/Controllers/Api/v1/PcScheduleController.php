<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\SyncSchedulesRequest;
use App\Http\Resources\ScheduleResource;
use App\Models\Pc;
use App\Services\SyncService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PcScheduleController extends Controller
{
    use AuthorizesRequests;

    protected SyncService $syncService;

    public function __construct(SyncService $syncService)
    {
        $this->syncService = $syncService;
    }

    /**
     * @OA\Get(
     *     path="/api/v1/pcs/{pc}/schedules",
     *     tags={"Schedules"},
     *     summary="List paginated schedules (power status history) for a specific PC",
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
     *         description="Paginated schedules list",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="pc_id", type="integer", example=1),
     *                     @OA\Property(property="timestamp", type="string", format="date-time", example="2026-06-13T12:00:00Z"),
     *                     @OA\Property(property="status", type="string", example="on")
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

        return ScheduleResource::collection($pc->schedules()->paginate());
    }

    /**
     * @OA\Post(
     *     path="/api/v1/pcs/{pc}/schedules",
     *     tags={"Schedules"},
     *     summary="Bulk sync schedules (power status history) for a specific PC",
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
     *                 description="Array of schedule status logs to sync",
     *                 @OA\Items(
     *                     required={"timestamp", "status"},
     *                     @OA\Property(property="timestamp", type="string", format="date-time", example="2026-06-13T12:00:00Z"),
     *                     @OA\Property(property="status", type="string", enum={"on", "off"}, example="on")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Schedules synced successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Schedules synced successfully"),
     *             @OA\Property(property="count", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=403, description="Forbidden (not user's device)"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
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
