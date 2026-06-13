<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateScheduleRequest;
use App\Http\Resources\ScheduleResource;
use App\Models\PcStatus;
use App\Models\Schedule;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class ScheduleController extends Controller
{
    use AuthorizesRequests;

    /**
     * @OA\Get(
     *     path="/api/v1/schedules/{schedule}",
     *     tags={"Schedules"},
     *     summary="Get details of a specific schedule status log",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="schedule",
     *         in="path",
     *         required=true,
     *         description="The ID of the schedule log",
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
     *                 @OA\Property(property="timestamp", type="string", format="date-time", example="2026-06-13T12:00:00Z"),
     *                 @OA\Property(property="status", type="string", example="on")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=403, description="Forbidden (not user's device)"),
     *     @OA\Response(response=404, description="Schedule log not found")
     * )
     */
    public function show(Schedule $schedule): ScheduleResource
    {
        $schedule->load(['pc', 'pcStatus']);
        $this->authorize('view', $schedule->pc);

        return new ScheduleResource($schedule);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/schedules/{schedule}",
     *     tags={"Schedules"},
     *     summary="Update a specific schedule status log",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="schedule",
     *         in="path",
     *         required=true,
     *         description="The ID of the schedule log",
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="timestamp", type="string", format="date-time", example="2026-06-13T12:00:00Z"),
     *             @OA\Property(property="status", type="string", enum={"on", "off"}, example="off")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Schedule log updated successfully",
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="timestamp", type="string", format="date-time", example="2026-06-13T12:00:00Z"),
     *                 @OA\Property(property="status", type="string", example="off")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=403, description="Forbidden (not user's device)"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function update(UpdateScheduleRequest $request, Schedule $schedule): ScheduleResource
    {
        $schedule->load('pc');
        $this->authorize('update', $schedule->pc);

        $validated = $request->validated();
        $data = [];

        if (array_key_exists('timestamp', $validated)) {
            $data['timestamp'] = $validated['timestamp'];
        }

        if (array_key_exists('status', $validated)) {
            $status = PcStatus::where('status', $validated['status'])->first();
            if (! $status) {
                throw ValidationException::withMessages([
                    'status' => ['The specified status could not be resolved.'],
                ]);
            }
            $data['pc_status_id'] = $status->id;
        }

        $schedule->update($data);
        $schedule->load('pcStatus');

        return new ScheduleResource($schedule);
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/schedules/{schedule}",
     *     tags={"Schedules"},
     *     summary="Delete a specific schedule status log",
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Parameter(
     *         name="schedule",
     *         in="path",
     *         required=true,
     *         description="The ID of the schedule log",
     *
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(response=204, description="Schedule log deleted successfully"),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=403, description="Forbidden (not user's device)"),
     *     @OA\Response(response=404, description="Schedule log not found")
     * )
     */
    public function destroy(Schedule $schedule): JsonResponse
    {
        $this->authorize('delete', $schedule->pc);
        $schedule->delete();

        return response()->json(null, 204);
    }
}
