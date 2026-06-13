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

    /**
     * @OA\Get(
     *     path="/api/v1/pcs",
     *     tags={"PCs"},
     *     summary="List all PCs owned by the authenticated user",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="unique_id", type="string", example="pc-123"),
     *                     @OA\Property(property="name", type="string", example="Work PC"),
     *                     @OA\Property(property="last_seen_at", type="string", format="date-time", example="2026-06-13T12:00:00Z"),
     *                     @OA\Property(property="created_at", type="string", format="date-time", example="2026-06-13T10:00:00Z"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2026-06-13T12:00:00Z")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function index(): AnonymousResourceCollection
    {
        $pcs = Pc::where('user_id', Auth::id())->get();

        return PcResource::collection($pcs);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/pcs",
     *     tags={"PCs"},
     *     summary="Register a new PC or find an existing one and update last_seen_at",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"unique_id"},
     *             @OA\Property(property="unique_id", type="string", example="pc-123"),
     *             @OA\Property(property="name", type="string", example="Work PC")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="PC registered/found successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="unique_id", type="string", example="pc-123"),
     *                 @OA\Property(property="name", type="string", example="Work PC"),
     *                 @OA\Property(property="last_seen_at", type="string", format="date-time", example="2026-06-13T12:00:00Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=403, description="Forbidden (device owned by another user)"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
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

    /**
     * @OA\Get(
     *     path="/api/v1/pcs/{pc}",
     *     tags={"PCs"},
     *     summary="Get details of a specific PC",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="pc",
     *         in="path",
     *         required=true,
     *         description="The unique_id of the PC",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="unique_id", type="string", example="pc-123"),
     *                 @OA\Property(property="name", type="string", example="Work PC"),
     *                 @OA\Property(property="last_seen_at", type="string", format="date-time", example="2026-06-13T12:00:00Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=403, description="Forbidden (not user's device)"),
     *     @OA\Response(response=404, description="PC not found")
     * )
     */
    public function show(Pc $pc): PcResource
    {
        $this->authorize('view', $pc);

        return new PcResource($pc);
    }

    /**
     * @OA\Put(
     *     path="/api/v1/pcs/{pc}",
     *     tags={"PCs"},
     *     summary="Update a PC's name",
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
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="New Work PC Name")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="PC updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="unique_id", type="string", example="pc-123"),
     *                 @OA\Property(property="name", type="string", example="New Work PC Name")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=403, description="Forbidden (not user's device)"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function update(UpdatePcRequest $request, Pc $pc): PcResource
    {
        $this->authorize('update', $pc);
        $pc->update($request->validated());

        return new PcResource($pc);
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/pcs/{pc}",
     *     tags={"PCs"},
     *     summary="Delete a PC and all its logs",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="pc",
     *         in="path",
     *         required=true,
     *         description="The unique_id of the PC",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(response=204, description="PC deleted successfully"),
     *     @OA\Response(response=401, description="Unauthorized"),
     *     @OA\Response(response=403, description="Forbidden (not user's device)"),
     *     @OA\Response(response=404, description="PC not found")
     * )
     */
    public function destroy(Pc $pc): JsonResponse
    {
        $this->authorize('delete', $pc);
        $pc->delete();

        return response()->json(null, 204);
    }
}
