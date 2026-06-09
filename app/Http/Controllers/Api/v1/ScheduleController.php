<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ScheduleResource;
use App\Models\Schedule;
use App\Models\PcStatus;
use App\Http\Requests\UpdateScheduleRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ScheduleController extends Controller
{
    use AuthorizesRequests;

    public function show(Schedule $schedule): ScheduleResource
    {
        $this->authorize('view', $schedule->pc);
        return new ScheduleResource($schedule);
    }

    public function update(UpdateScheduleRequest $request, Schedule $schedule): ScheduleResource
    {
        $this->authorize('update', $schedule->pc);

        $validated = $request->validated();
        $data = [];

        if (array_key_exists('timestamp', $validated)) {
            $data['timestamp'] = $validated['timestamp'];
        }

        if (array_key_exists('status', $validated)) {
            $status = PcStatus::where('status', $validated['status'])->first();
            if (!$status) {
                throw ValidationException::withMessages([
                    'status' => ['The specified status could not be resolved.'],
                ]);
            }
            $data['pc_status_id'] = $status->id;
        }

        $schedule->update($data);
        return new ScheduleResource($schedule);
    }

    public function destroy(Schedule $schedule): JsonResponse
    {
        $this->authorize('delete', $schedule->pc);
        $schedule->delete();
        return response()->json(null, 204);
    }
}
