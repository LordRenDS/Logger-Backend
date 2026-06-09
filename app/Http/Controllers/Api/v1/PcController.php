<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePcRequest;
use App\Http\Requests\UpdatePcRequest;
use App\Http\Resources\PcResource;
use App\Models\Pc;
use App\Services\PcService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;

class PcController extends Controller
{
    use AuthorizesRequests;

    protected PcService $pcService;

    public function __construct(PcService $pcService)
    {
        $this->pcService = $pcService;
    }

    public function index(): AnonymousResourceCollection
    {
        $pcs = Pc::where('user_id', Auth::id())->get();

        return PcResource::collection($pcs);
    }

    public function store(StorePcRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $pc = $this->pcService->findOrCreatePc(
            Auth::user(),
            $validated['unique_id'],
            $validated['name'] ?? null
        );

        return (new PcResource($pc))->response()->setStatusCode(201);
    }

    public function show(Pc $pc): PcResource
    {
        $this->authorize('view', $pc);

        return new PcResource($pc);
    }

    public function update(UpdatePcRequest $request, Pc $pc): PcResource
    {
        $this->authorize('update', $pc);
        $pc->update($request->validated());

        return new PcResource($pc);
    }

    public function destroy(Pc $pc): JsonResponse
    {
        $this->authorize('delete', $pc);
        $pc->delete();

        return response()->json(null, 204);
    }
}
