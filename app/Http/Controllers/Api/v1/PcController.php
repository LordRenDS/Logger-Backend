<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\PcResource;
use App\Models\Pc;
use App\Services\PcService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class PcController extends Controller
{
    use AuthorizesRequests;

    protected PcService $pcService;

    public function __construct(PcService $pcService)
    {
        $this->pcService = $pcService;
    }

    public function index(): JsonResponse
    {
        $pcs = Pc::where('user_id', Auth::id())->get();
        return response()->json(PcResource::collection($pcs));
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'unique_id' => 'required|string',
            'name' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $pc = $this->pcService->findOrCreatePc(Auth::user(), $request->unique_id, $request->name);
        return response()->json(new PcResource($pc), 201);
    }

    public function show(Pc $pc): JsonResponse
    {
        $this->authorize('view', $pc);
        return response()->json(new PcResource($pc));
    }

    public function update(Request $request, Pc $pc): JsonResponse
    {
        $this->authorize('update', $pc);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $pc->update($request->only('name'));
        return response()->json(new PcResource($pc));
    }

    public function destroy(Pc $pc): JsonResponse
    {
        $this->authorize('delete', $pc);
        $pc->delete();
        return response()->json(null, 204);
    }
}
