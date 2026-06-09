<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ScheduleResource;
use App\Models\Schedule;
use App\Models\PcStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ScheduleController extends Controller
{
    use AuthorizesRequests;

    public function show(Schedule $schedule): JsonResponse
    {
        $this->authorize('view', $schedule->pc);
        return response()->json(new ScheduleResource($schedule));
    }

    public function update(Request $request, Schedule $schedule): JsonResponse
    {
        $this->authorize('update', $schedule->pc);

        $validator = Validator::make($request->all(), [
            'timestamp' => 'sometimes|required|date',
            'status' => 'sometimes|required|string|in:on,off',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $data = $request->only('timestamp');
        if ($request->has('status')) {
            $status = PcStatus::where('status', $request->input('status'))->first();
            if ($status) {
                $data['pc_status_id'] = $status->id;
            }
        }

        $schedule->update($data);
        return response()->json(new ScheduleResource($schedule));
    }

    public function destroy(Schedule $schedule): JsonResponse
    {
        $this->authorize('delete', $schedule->pc);
        $schedule->delete();
        return response()->json(null, 204);
    }
}
