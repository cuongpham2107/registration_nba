<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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
                Log::error('Auth token fetch error: '.$e->getMessage());
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
            Log::error('HAWB API error: '.$e->getMessage());
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
            // dd($data);
            if (($data['success'] ?? false)) {
                $payload = $data['data'] ?? null;

                // Case: data is an array of strings: ["BOLO","APEX",...]
                if (is_array($payload) && ! empty($payload) && is_string(array_values($payload)[0])) {
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
                if (is_array($payload) && ! empty($payload) && is_array(reset($payload))) {
                    return collect($payload)->pluck('AgentName', 'AgentCode')->toArray();
                }
            }
        } catch (\Exception $e) {
            Log::error('Agent API error: '.$e->getMessage());
        }

        return [];
    }
}
