<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTeamRequest;
use App\Http\Requests\UpdateTeamRequest;
use App\Http\Resources\TeamResource;
use App\Services\TeamService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TeamController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly TeamService $teamService
    ) {}

    /**
     * Display a listing of teams.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = $request->only(['division', 'conference']);
        $perPage = $request->integer('per_page', 15);

        $teams = $this->teamService->getPaginatedTeams($filters, $perPage);

        return TeamResource::collection($teams);
    }

    /**
     * Store a newly created team.
     */
    public function store(StoreTeamRequest $request): JsonResponse
    {
        $team = $this->teamService->createTeam($request->validated());

        return $this->successResponse(
            new TeamResource($team),
            'Team created successfully.',
            201
        );
    }

    /**
     * Display the specified team.
     */
    public function show(int $id): JsonResponse
    {
        $team = $this->teamService->findTeam($id);

        if (!$team) {
            return $this->errorResponse('Team not found.', 404);
        }

        return $this->successResponse(
            new TeamResource($team),
            'Team retrieved successfully.'
        );
    }

    /**
     * Update the specified team.
     */
    public function update(UpdateTeamRequest $request, int $id): JsonResponse
    {
        $team = $this->teamService->updateTeam($id, $request->validated());

        if (!$team) {
            return $this->errorResponse('Team not found.', 404);
        }

        return $this->successResponse(
            new TeamResource($team),
            'Team updated successfully.'
        );
    }

    /**
     * Remove the specified team.
     */
    public function destroy(int $id): JsonResponse
    {
        $deleted = $this->teamService->deleteTeam($id);

        if (!$deleted) {
            return $this->errorResponse('Team not found.', 404);
        }

        return $this->successResponse(
            null,
            'Team deleted successfully.'
        );
    }
}
