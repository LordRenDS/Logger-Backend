<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Services\PcService;
use App\Services\SyncService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class SyncController extends Controller
{
    protected PcService $pcService;
    protected SyncService $syncService;

    public function __construct(PcService $pcService, SyncService $syncService)
    {
        $this->pcService = $pcService;
        $this->syncService = $syncService;
    }

    /**
     * @OA\Post(
     *     path="/api/v1/sync/processes",
     *     tags={"Synchronization"},
     *     summary="Sync processes activity logs",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="pc_unique_id", type="string", example="pc-123"),
     *             @OA\Property(property="pc_name", type="string", example="Work PC"),
     *             @OA\Property(property="data", type="array", @OA\Items(
     *                 @OA\Property(property="process_start", type="string", format="date-time"),
     *                 @OA\Property(property="process_name", type="string"),
     *                 @OA\Property(property="window_name", type="string"),
     *                 @OA\Property(property="duration", type="integer")
     *             ))
     *         )
     *     ),
     *     @OA\Response(response=200, description="Sync successful"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function syncProcesses(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'pc_unique_id' => 'required|string',
            'pc_name' => 'nullable|string',
            'data' => 'required|array',
            'data.*.process_start' => 'required|date',
            'data.*.process_name' => 'required|string',
            'data.*.window_name' => 'required|string',
            'data.*.duration' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $user = Auth::user();
        $pc = $this->pcService->findOrCreatePc($user, $request->pc_unique_id, $request->pc_name);

        $count = $this->syncService->syncProcesses($pc, $request->data);

        return response()->json([
            'message' => 'Processes synced successfully',
            'count' => $count,
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/sync/schedules",
     *     tags={"Synchronization"},
     *     summary="Sync PC status history",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="pc_unique_id", type="string", example="pc-123"),
     *             @OA\Property(property="pc_name", type="string", example="Work PC"),
     *             @OA\Property(property="data", type="array", @OA\Items(
     *                 @OA\Property(property="timestamp", type="string", format="date-time"),
     *                 @OA\Property(property="status", type="string", enum={"on", "off"})
     *             ))
     *         )
     *     ),
     *     @OA\Response(response=200, description="Sync successful"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function syncSchedules(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'pc_unique_id' => 'required|string',
            'pc_name' => 'nullable|string',
            'data' => 'required|array',
            'data.*.timestamp' => 'required|date',
            'data.*.status' => 'required|string|in:on,off',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $user = Auth::user();
        $pc = $this->pcService->findOrCreatePc($user, $request->pc_unique_id, $request->pc_name);

        $count = $this->syncService->syncSchedules($pc, $request->data);

        return response()->json([
            'message' => 'Schedules synced successfully',
            'count' => $count,
        ]);
    }
}
