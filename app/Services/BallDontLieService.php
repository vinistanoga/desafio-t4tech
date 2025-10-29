<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BallDontLieService
{
    protected string $baseUrl;
    protected string $apiKey;
    protected int $requestDelay = 12; // (5 req/min)

    public function __construct()
    {
        $this->baseUrl = config('services.balldontlie.url');
        $this->apiKey = config('services.balldontlie.key');
    }

    /**
     * Get all NBA teams from BallDontLie API.
     */
    public function getTeams(): array
    {
        try {
            $response = $this->makeRequest('GET', '/teams');

            if (!isset($response['data'])) {
                Log::error('BallDontLie API: Invalid teams response structure', $response);
                return [];
            }

            return $response['data'];
        } catch (\Exception $e) {
            Log::error('BallDontLie API: Failed to fetch teams', [
                'error' => $e->getMessage(),
            ]);
            return [];
        }
    }

    /**
     * Get players from BallDontLie API with cursor-based pagination.
     *
     * @param int|null $cursor The cursor for pagination (null for first page)
     * @param int $perPage Results per page (default 25, max 100)
     */
    public function getPlayers(?int $cursor = null, int $perPage = 100): array
    {
        try {
            $params = ['per_page' => $perPage];

            if ($cursor !== null) {
                $params['cursor'] = $cursor;
            }

            $response = $this->makeRequest('GET', '/players', $params);

            if (!isset($response['data'])) {
                Log::error('BallDontLie API: Invalid players response structure', $response);
                return ['data' => [], 'meta' => []];
            }

            return [
                'data' => $response['data'],
                'meta' => $response['meta'] ?? [],
            ];
        } catch (\Exception $e) {
            Log::error('BallDontLie API: Failed to fetch players', [
                'cursor' => $cursor,
                'error' => $e->getMessage(),
            ]);
            return ['data' => [], 'meta' => []];
        }
    }

    /**
     * Make HTTP request to BallDontLie API.
     * Rate limited to 5 requests per minute (free tier).
     */
    protected function makeRequest(string $method, string $endpoint, array $params = []): array
    {
        $url = $this->baseUrl . $endpoint;

        Log::info('BallDontLie API Request', [
            'method' => $method,
            'endpoint' => $endpoint,
            'params' => $params,
        ]);

        $response = Http::withHeaders([
            'Authorization' => $this->apiKey,
        ])
            ->timeout(30)
            ->retry(2, 2000)
            ->{strtolower($method)}($url, $params);

        if ($response->failed()) {
            $status = $response->status();
            $body = $response->body();

            Log::error('BallDontLie API: Request failed', [
                'status' => $status,
                'body' => $body,
                'endpoint' => $endpoint,
            ]);

            throw new \Exception("API request failed with status {$status}: {$body}");
        }

        // Rate limiting
        sleep($this->requestDelay);

        return $response->json();
    }

    /**
     * Transform team data from API format to database format.
     */
    public function transformTeamData(array $apiTeam): array
    {
        return [
            'external_id' => $apiTeam['id'],
            'conference' => $apiTeam['conference'] ?? null,
            'division' => $apiTeam['division'] ?? null,
            'city' => $apiTeam['city'] ?? null,
            'name' => $apiTeam['name'],
            'full_name' => $apiTeam['full_name'],
            'abbreviation' => $apiTeam['abbreviation'],
        ];
    }

    /**
     * Transform player data from API format to database format.
     */
    public function transformPlayerData(array $apiPlayer, ?int $teamId = null): array
    {
        return [
            'external_id' => $apiPlayer['id'],
            'first_name' => $apiPlayer['first_name'],
            'last_name' => $apiPlayer['last_name'],
            'position' => $apiPlayer['position'] ?? null,
            'height' => $apiPlayer['height'] ?? null,
            'weight' => $apiPlayer['weight'] ?? null,
            'jersey_number' => $apiPlayer['jersey_number'] ?? null,
            'college' => $apiPlayer['college'] ?? null,
            'country' => $apiPlayer['country'] ?? null,
            'draft_year' => $apiPlayer['draft_year'] ?? null,
            'draft_round' => $apiPlayer['draft_round'] ?? null,
            'draft_number' => $apiPlayer['draft_number'] ?? null,
            'team_id' => $teamId,
        ];
    }
}
