<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProcessResource;
use App\Models\Process;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ProcessController extends Controller
{
    use AuthorizesRequests;

    public function show(Process $process): JsonResponse
    {
        $this->authorize('view', $process->pc);
        return response()->json(new ProcessResource($process));
    }

    public function update(Request $request, Process $process): JsonResponse
    {
        $this->authorize('update', $process->pc);

        $validator = Validator::make($request->all(), [
            'process_name' => 'sometimes|required|string',
            'window_name' => 'sometimes|required|string',
            'duration' => 'sometimes|required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $process->update($request->only(['process_name', 'window_name', 'duration']));
        return response()->json(new ProcessResource($process));
    }

    public function destroy(Process $process): JsonResponse
    {
        $this->authorize('delete', $process->pc);
        $process->delete();
        return response()->json(null, 204);
    }
}
