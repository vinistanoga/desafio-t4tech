<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePlayerRequest;
use App\Http\Requests\UpdatePlayerRequest;
use App\Http\Resources\PlayerResource;
use App\Services\PlayerService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PlayerController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly PlayerService $playerService
    ) {}

    /**
     * Display a listing of players.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = $request->only([
            'search',
            'first_name',
            'last_name',
            'team_ids',
            'player_ids'
        ]);
        $perPage = $request->integer('per_page', 15);

        $players = $this->playerService->getPaginatedPlayers($filters, $perPage);

        return PlayerResource::collection($players);
    }

    /**
     * Store a newly created player.
     */
    public function store(StorePlayerRequest $request): JsonResponse
    {
        $player = $this->playerService->createPlayer($request->validated());

        return $this->successResponse(
            new PlayerResource($player),
            'Player created successfully.',
            201
        );
    }

    /**
     * Display the specified player.
     */
    public function show(int $id): JsonResponse
    {
        $player = $this->playerService->findPlayer($id);

        if (!$player) {
            return $this->errorResponse('Player not found.', 404);
        }

        return $this->successResponse(
            new PlayerResource($player),
            'Player retrieved successfully.'
        );
    }

    /**
     * Update the specified player.
     */
    public function update(UpdatePlayerRequest $request, int $id): JsonResponse
    {
        $player = $this->playerService->updatePlayer($id, $request->validated());

        if (!$player) {
            return $this->errorResponse('Player not found.', 404);
        }

        return $this->successResponse(
            new PlayerResource($player),
            'Player updated successfully.'
        );
    }

    /**
     * Remove the specified player.
     */
    public function destroy(int $id): JsonResponse
    {
        $deleted = $this->playerService->deletePlayer($id);

        if (!$deleted) {
            return $this->errorResponse('Player not found.', 404);
        }

        return $this->successResponse(
            null,
            'Player deleted successfully.'
        );
    }
}
