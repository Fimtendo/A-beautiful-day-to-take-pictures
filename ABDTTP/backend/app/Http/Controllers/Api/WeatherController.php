<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class WeatherController extends Controller
{
    public function getWeather(Request $request)
    {
        $request->validate([
            'latitude'  => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        $lat = $request->input('latitude');
        $lng = $request->input('longitude');

        $cacheKey = "weather_lat_" . round((float)$lat, 2) . "_lng_" . round((float)$lng, 2);

        if (Cache::has($cacheKey)) {
            return response()->json(Cache::get($cacheKey));
        }
        
        try {
            $cleanLat = number_format((float)$lat, 4, '.', '');
            $cleanLng = number_format((float)$lng, 4, '.', '');
            $response = Http::withoutVerifying()
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)',
                    'Accept' => 'application/json'
                ])
                ->withOptions([
                    'curl' => [
                        CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4
                    ]
                ])
                ->timeout(5)
                ->get('https://api.open-meteo.com/v1/forecast', [
                    'latitude'  => $cleanLat,
                    'longitude' => $cleanLng,
                    'timezone'  => 'auto',
                    'current'   => 'temperature_2m,weather_code,wind_speed_10m,wind_direction_10m',
                    'daily'     => 'temperature_2m_max,temperature_2m_min,weather_code,precipitation_probability_max,wind_speed_10m_max'
                ]);

            if ($response->failed()) {
                Log::error('Open-Meteo API live verbinding mislukt: ' . $response->status());
                return $this->getFallbackData();
            }

            $d = $response->json();
            if (!$d || !isset($d['daily']) || !isset($d['current'])) {
                Log::warning('Open-Meteo structuur matcht niet met JSON response', ['response' => $d]);
                return $this->getFallbackData();
            }
            $fullForecast = [];
            foreach ($d['daily']['time'] as $i => $date) {
                $fullForecast[] = [
                    'date'         => $date,
                    'minTemp'      => $d['daily']['temperature_2m_min'][$i] ?? 0,
                    'maxTemp'      => $d['daily']['temperature_2m_max'][$i] ?? 0,
                    'weatherCode'  => $d['daily']['weather_code'][$i] ?? 0, 
                    'precipProb'   => $d['daily']['precipitation_probability_max'][$i] ?? 0,
                    'windspeedMax' => $d['daily']['wind_speed_10m_max'][$i] ?? 0,
                ];
            }
            $forecast = array_slice($fullForecast, 1, 3);
            $processedData = [
                'current' => [
                    'temperature'   => $d['current']['temperature_2m'] ?? 0,
                    'weatherCode'   => $d['current']['weather_code'] ?? 0,
                    'windspeed'     => $d['current']['wind_speed_10m'] ?? 0,
                    'winddirection' => $d['current']['wind_direction_10m'] ?? 0,
                    'rainChance'    => $d['daily']['precipitation_probability_max'][0] ?? 0,
                ],
                'forecast' => $forecast,
            ];

            // Sla succesvolle data direct op in de cache voor 60 minuten
            Cache::put($cacheKey, $processedData, now()->addMinutes(60));

            return response()->json($processedData);

        } catch (\Exception $e) {
            Log::error('Exception opgetreden in WeatherController: ' . $e->getMessage());
            return $this->getFallbackData();
        }

    }

    private function getFallbackData() {
        return response()->json([
            'current' => ['temperature' => -30, 'weatherCode' => 1, 'windspeed' => 120, 'winddirection' => 180, 'rainChance' => 100],
            'forecast' => [
                ['date' => now()->addDay()->format('Y-m-d'), 'minTemp' => 101, 'maxTemp' => 18, 'weatherCode' => 1, 'precipProb' => 20, 'windspeedMax' => 15],
                ['date' => now()->addDays(2)->format('Y-m-d'), 'minTemp' => 12, 'maxTemp' => 19, 'weatherCode' => 2, 'precipProb' => 40, 'windspeedMax' => 12],
                ['date' => now()->addDays(3)->format('Y-m-d'), 'minTemp' => 10, 'maxTemp' => 16, 'weatherCode' => 0, 'precipProb' => 5, 'windspeedMax' => 10],
            ]
        ]);
    }
}
