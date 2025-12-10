<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class HawbService
{
    /**
     * Get cached auth token from identity provider. Cached for 1 hour.
     */
    public static function getAuthToken(): ?string
    {
        return Cache::remember('asgl_api_token', 3600, function () {
            try {
                $login = env('ASGL_API_LOGIN', 'ASGL-ĐKK');
                $password = env('ASGL_API_PASSWORD', 'Asgl@1909');

                $response = Http::timeout(10)->post('https://id.asgl.net.vn/api/auth/login', [
                    'login' => $login,
                    'password' => $password,
                ]);

                $data = $response->json();
                if (($data['success'] ?? false) && isset($data['data']['token'])) {
                    return $data['data']['token'];
                }
            } catch (\Exception $e) {
                Log::error('Auth token fetch error: ' . $e->getMessage());
            }

            return null;
        });
    }

    /**
     * Search for HAWB information via API
     */
    public static function searchHawbApi(string $hawbNumber): ?array
    {
        $token = self::getAuthToken();
        if (empty($token)) {
            Log::warning('No auth token available for HAWB check');
            return null;
        }

        try {
            $url = "https://wh-nba.asgl.net.vn/api/check-in/hawb?search={$hawbNumber}";
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$token}",
                'Accept' => 'application/json',
            ])->timeout(10)->get($url);

            $data = $response->json();
            if (($data['success'] ?? false) && isset($data['data'])) {
                return $data['data'];
            }
        } catch (\Exception $e) {
            Log::error('HAWB API error: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Get list of agents from API
     */
    public static function getListAgentApi(): array
    {
        try {
            $response = Http::timeout(10)->get('https://wh-nba.asgl.net.vn/api/list-agent');
            $data = $response->json();
            if (($data['success'] ?? false)) {
                $payload = $data['data'] ?? null;

                // Case: data is an array of strings: ["BOLO","APEX",...]
                if (is_array($payload) && !empty($payload) && is_string(array_values($payload)[0])) {
                    $result = [];
                    foreach ($payload as $agent) {
                        $result[$agent] = $agent;
                    }
                    return $result;
                }

                // Case: data is an associative array containing 'agents'
                if (is_array($payload) && isset($payload['agents']) && is_array($payload['agents'])) {
                    $agentsArr = $payload['agents'];
                    $result = [];
                    foreach ($agentsArr as $item) {
                        if (is_string($item)) {
                            $result[$item] = $item;
                        } elseif (is_array($item) && isset($item['AgentCode'], $item['AgentName'])) {
                            $result[$item['AgentCode']] = $item['AgentName'];
                        }
                    }
                    return $result;
                }

                // Case: data is an array of objects with AgentName / AgentCode
                if (is_array($payload) && !empty($payload) && is_array(reset($payload))) {
                    return collect($payload)->pluck('AgentName', 'AgentCode')->toArray();
                }
            }
        } catch (\Exception $e) {
            Log::error('Agent API error: ' . $e->getMessage());
        }

        return [];
    }

    /**
     * Process HAWB search results into standardized format
     */
    public static function processHawbSearchResults(array $apiData): array
    {
        // Expected shape: ['hawb' => [ {Hawb, Pcs, ...}, ... ]]
        $newRows = [];
        if (isset($apiData['hawb']) && is_array($apiData['hawb'])) {
            foreach ($apiData['hawb'] as $item) {
                $newRows[] = [
                    'hawb_number' => $item['Hawb'] ?? null,
                    'pcs' => isset($item['Pcs']) ? (string)$item['Pcs'] : null,
                ];
            }
        }

        return $newRows;
    }

    /**
     * Add new HAWB rows to existing array, avoiding duplicates
     */
    public static function addHawbsToExisting(array $existingHawbs, array $newRows): array
    {
        // Get existing HAWB numbers to check for duplicates
        $existingHawbNumbers = array_column($existingHawbs, 'hawb_number');
        
        $addedCount = 0;
        $updatedHawbs = $existingHawbs;
        
        foreach ($newRows as $newRow) {
            // Only add if HAWB number doesn't already exist and is not empty
            if (!empty($newRow['hawb_number']) && !in_array($newRow['hawb_number'], $existingHawbNumbers)) {
                $updatedHawbs[] = $newRow;
                $existingHawbNumbers[] = $newRow['hawb_number'];
                $addedCount++;
            }
        }

        return [
            'hawbs' => $updatedHawbs,
            'added_count' => $addedCount,
            'total_count' => count($updatedHawbs),
            'had_duplicates' => count($newRows) > $addedCount
        ];
    }
}
