<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LocationService
{
    /**
     * Geocode an address to coordinates.
     *
     * @return array|null ['latitude' => float, 'longitude' => float]
     */
    public function geocode(string $address): ?array
    {
        // Use Google Maps if API key is configured, otherwise use OpenStreetMap
        if (config('services.google_maps.enabled')) {
            return $this->geocodeWithGoogle($address);
        }

        return $this->geocodeWithOSM($address);
    }

    /**
     * Geocode using OpenStreetMap Nominatim.
     */
    private function geocodeWithOSM(string $address): ?array
    {
        try {
            $response = Http::get('https://nominatim.openstreetmap.org/search', [
                'q' => $address,
                'format' => 'json',
                'limit' => 1,
                'addressdetails' => 1,
            ]);

            if ($response->successful() && count($response->json()) > 0) {
                $result = $response->json()[0];

                return [
                    'latitude' => (float) $result['lat'],
                    'longitude' => (float) $result['lon'],
                    'display_name' => $result['display_name'] ?? $address,
                ];
            }

            return null;
        } catch (\Exception $e) {
            Log::error('OSM Geocoding failed', [
                'address' => $address,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Geocode using Google Maps API.
     */
    private function geocodeWithGoogle(string $address): ?array
    {
        try {
            $apiKey = config('services.google_maps.api_key');

            $response = Http::get('https://maps.googleapis.com/maps/api/geocode/json', [
                'address' => $address,
                'key' => $apiKey,
            ]);

            if ($response->successful()) {
                $data = $response->json();

                if ($data['status'] === 'OK' && ! empty($data['results'])) {
                    $result = $data['results'][0];
                    $location = $result['geometry']['location'];

                    return [
                        'latitude' => (float) $location['lat'],
                        'longitude' => (float) $location['lng'],
                        'display_name' => $result['formatted_address'],
                    ];
                }
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Google Maps Geocoding failed', [
                'address' => $address,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Reverse geocode coordinates to an address.
     */
    public function reverseGeocode(float $latitude, float $longitude): ?string
    {
        try {
            $response = Http::get('https://nominatim.openstreetmap.org/reverse', [
                'lat' => $latitude,
                'lon' => $longitude,
                'format' => 'json',
                'addressdetails' => 1,
            ]);

            if ($response->successful()) {
                $result = $response->json();

                return $result['display_name'] ?? null;
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Reverse geocoding failed', [
                'latitude' => $latitude,
                'longitude' => $longitude,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Calculate distance between two coordinates.
     *
     * @param  string  $unit  (km or mi)
     */
    public function calculateDistance(
        float $lat1,
        float $lon1,
        float $lat2,
        float $lon2,
        string $unit = 'km'
    ): float {
        $earthRadius = $unit === 'mi' ? 3959 : 6371; // miles or kilometers

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    /**
     * Calculate estimated duration between two coordinates.
     *
     * @return int Duration in minutes
     */
    public function calculateDuration(
        float $lat1,
        float $lon1,
        float $lat2,
        float $lon2,
        ?int $averageSpeedKmh = null
    ): int {
        $averageSpeedKmh = $averageSpeedKmh ?? config('lcp.ride.average_speed_kmh', 50);
        $distance = $this->calculateDistance($lat1, $lon1, $lat2, $lon2);

        $hours = $distance / $averageSpeedKmh;
        $minutes = $hours * 60;

        // Add buffer time (10%) for stops, traffic, etc.
        return (int) ceil($minutes * 1.1);
    }

    /**
     * Get route between two points.
     */
    public function getRoute(
        float $startLat,
        float $startLon,
        float $endLat,
        float $endLon
    ): ?array {
        try {
            // Using OSRM (Open Source Routing Machine)
            // In production, consider using Google Directions API, Mapbox, etc.
            $response = Http::get("http://router.project-osrm.org/route/v1/driving/{$startLon},{$startLat};{$endLon},{$endLat}", [
                'overview' => 'false',
                'steps' => 'false',
            ]);

            if ($response->successful()) {
                $result = $response->json();

                if (isset($result['routes'][0])) {
                    $route = $result['routes'][0];

                    return [
                        'distance_km' => round($route['distance'] / 1000, 2),
                        'duration_minutes' => round($route['duration'] / 60),
                        'duration_seconds' => $route['duration'],
                    ];
                }
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Route calculation failed', [
                'start' => [$startLat, $startLon],
                'end' => [$endLat, $endLon],
                'error' => $e->getMessage(),
            ]);

            // Fallback to simple distance calculation
            return [
                'distance_km' => $this->calculateDistance($startLat, $startLon, $endLat, $endLon),
                'duration_minutes' => $this->calculateDuration($startLat, $startLon, $endLat, $endLon),
                'fallback' => true,
            ];
        }
    }

    /**
     * Validate coordinates.
     */
    public function validateCoordinates(float $latitude, float $longitude): bool
    {
        return $latitude >= -90 && $latitude <= 90 && $longitude >= -180 && $longitude <= 180;
    }

    /**
     * Check if coordinates are in France.
     */
    public function isInFrance(float $latitude, float $longitude): bool
    {
        // Rough bounding box for France
        return $latitude >= 41.0 && $latitude <= 51.5 &&
            $longitude >= -5.5 && $longitude <= 10.0;
    }

    /**
     * Format coordinates for display.
     */
    public function formatCoordinates(float $latitude, float $longitude): string
    {
        $latDirection = $latitude >= 0 ? 'N' : 'S';
        $lonDirection = $longitude >= 0 ? 'E' : 'W';

        return sprintf(
            '%s° %s, %s° %s',
            number_format(abs($latitude), 6),
            $latDirection,
            number_format(abs($longitude), 6),
            $lonDirection
        );
    }
}
