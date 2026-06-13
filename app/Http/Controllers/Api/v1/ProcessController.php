<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateProcessRequest;
use App\Http\Resources\ProcessResource;
use App\Models\Process;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;

class ProcessController extends Controller
{
    use AuthorizesRequests;

    /**
     * @OA\Get(
     *     path="/api/v1/processes/{process}",
     *     tags={"Processes"},
     *     summary="Get details of a specific process activity log",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="process",
     *         in="path",
     *         required=true,
     *         description="The ID of the process log",
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="pc_id", type="integer", example=1),
     *                 @OA\Property(property="process_start", type="string", format="date-time", example="2026-06-13T12:00:00Z"),
     *                 @OA\Property(property="process_name", type="string", example="chrome.exe"),
     *                 @OA\Property(property="window_name", type="string", example="Google Search"),
     *                 @OA\Property(property="duration", type="integer", example=120)
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=403, description="Forbidden (not user's device)"),
     *     @OA\Response(response=404, description="Process log not found")
     * )
     */
    public function show(Process $process): ProcessResource
    {
        $this->authorize('view', $process->pc);

        return new ProcessResource($process);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/processes/{process}",
     *     tags={"Processes"},
     *     summary="Update a specific process activity log",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="process",
     *         in="path",
     *         required=true,
     *         description="The ID of the process log",
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="duration", type="integer", example=180),
     *             @OA\Property(property="window_name", type="string", example="New Window Title")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Process log updated successfully",
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="duration", type="integer", example=180)
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=403, description="Forbidden (not user's device)"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function update(UpdateProcessRequest $request, Process $process): ProcessResource
    {
        $this->authorize('update', $process->pc);
        $process->update($request->validated());

        return new ProcessResource($process);
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/processes/{process}",
     *     tags={"Processes"},
     *     summary="Delete a specific process activity log",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="process",
     *         in="path",
     *         required=true,
     *         description="The ID of the process log",
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(response=204, description="Process log deleted successfully"),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=403, description="Forbidden (not user's device)"),
     *     @OA\Response(response=404, description="Process log not found")
     * )
     */
    public function destroy(Process $process): JsonResponse
    {
        $this->authorize('delete', $process->pc);
        $process->delete();

        return response()->json(null, 204);
    }
}
