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

    public function show(Process $process): ProcessResource
    {
        $this->authorize('view', $process->pc);

        return new ProcessResource($process);
    }

    public function update(UpdateProcessRequest $request, Process $process): ProcessResource
    {
        $this->authorize('update', $process->pc);
        $process->update($request->validated());

        return new ProcessResource($process);
    }

    public function destroy(Process $process): JsonResponse
    {
        $this->authorize('delete', $process->pc);
        $process->delete();

        return response()->json(null, 204);
    }
}
