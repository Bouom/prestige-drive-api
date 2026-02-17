<?php

namespace App\Services;

use App\Models\DriverProfile;
use App\Models\Ride;
use Illuminate\Support\Collection;

class DriverMatchingService
{
    /**
     * Find available drivers near a location.
     */
    public function findNearbyDrivers(float $latitude, float $longitude, int $radiusKm = 10): Collection
    {
        // Using Haversine formula to calculate distance
        // This is a simplified version - in production, consider using PostGIS or similar
        $drivers = DriverProfile::where('is_verified', true)
            ->where('is_available', true)
            ->whereNotNull('current_latitude')
            ->whereNotNull('current_longitude')
            ->select('driver_profiles.*')
            ->selectRaw(
                '(6371 * acos(cos(radians(?)) * cos(radians(current_latitude)) * cos(radians(current_longitude) - radians(?)) + sin(radians(?)) * sin(radians(current_latitude)))) AS distance_km',
                [$latitude, $longitude, $latitude]
            )
            ->having('distance_km', '<=', $radiusKm)
            ->orderBy('distance_km')
            ->with(['user', 'vehicle'])
            ->get();

        return $drivers;
    }

    /**
     * Find the best driver for a ride based on multiple criteria.
     */
    public function findBestDriver(Ride $ride, array $criteria = []): ?DriverProfile
    {
        $radiusKm = $criteria['radius_km'] ?? 10;
        $minRating = $criteria['min_rating'] ?? 4.0;

        $drivers = $this->findNearbyDrivers(
            $ride->pickup_latitude,
            $ride->pickup_longitude,
            $radiusKm
        );

        // Filter by minimum rating
        $drivers = $drivers->filter(function ($driver) use ($minRating) {
            return $driver->rating >= $minRating || is_null($driver->rating);
        });

        if ($drivers->isEmpty()) {
            return null;
        }

        // Score drivers based on multiple factors
        $scoredDrivers = $drivers->map(function ($driver) use ($ride) {
            $score = $this->calculateDriverScore($driver, $ride);

            return [
                'driver' => $driver,
                'score' => $score,
            ];
        });

        // Sort by score (highest first)
        $scoredDrivers = $scoredDrivers->sortByDesc('score');

        return $scoredDrivers->first()['driver'] ?? null;
    }

    /**
     * Calculate driver score based on various factors.
     */
    private function calculateDriverScore(DriverProfile $driver, Ride $ride): float
    {
        $score = 0;

        // Rating (0-50 points)
        if ($driver->rating) {
            $score += ($driver->rating / 5) * 50;
        } else {
            $score += 30; // Default score for new drivers
        }

        // Distance (0-30 points) - closer is better
        if (isset($driver->distance_km)) {
            $distanceScore = max(0, 30 - ($driver->distance_km * 3));
            $score += $distanceScore;
        }

        // Acceptance rate (0-10 points)
        if ($driver->acceptance_rate) {
            $score += ($driver->acceptance_rate / 100) * 10;
        }

        // Completion rate (0-10 points)
        if ($driver->total_rides > 0) {
            $completionRate = ($driver->total_rides - $driver->cancelled_rides) / $driver->total_rides;
            $score += $completionRate * 10;
        }

        return $score;
    }

    /**
     * Assign a driver to a ride.
     */
    public function assignDriver(Ride $ride, DriverProfile $driver): bool
    {
        // Check if driver is still available
        if (! $driver->is_available || ! $driver->is_verified) {
            return false;
        }

        // Assign driver to ride
        $ride->update([
            'driver_id' => $driver->user_id,
            'vehicle_id' => $driver->current_vehicle_id,
            'status' => 'assigned',
        ]);

        // Mark driver as unavailable (optional - depends on business logic)
        // $driver->update(['is_available' => false]);

        return true;
    }

    /**
     * Auto-assign driver to a ride.
     */
    public function autoAssignDriver(Ride $ride, array $criteria = []): ?DriverProfile
    {
        $driver = $this->findBestDriver($ride, $criteria);

        if ($driver && $this->assignDriver($ride, $driver)) {
            return $driver;
        }

        return null;
    }

    /**
     * Get driver availability statistics.
     */
    public function getAvailabilityStats(
        ?float $latitude = null,
        ?float $longitude = null,
        int $radiusKm = 10
    ): array {
        $totalDrivers = DriverProfile::where('is_verified', true)->count();
        $availableDrivers = DriverProfile::where('is_verified', true)
            ->where('is_available', true)
            ->count();

        $stats = [
            'total_verified_drivers' => $totalDrivers,
            'available_drivers' => $availableDrivers,
            'availability_rate' => $totalDrivers > 0 ? round(($availableDrivers / $totalDrivers) * 100, 2) : 0,
        ];

        if ($latitude && $longitude) {
            $nearbyDrivers = $this->findNearbyDrivers($latitude, $longitude, $radiusKm);
            $stats['nearby_drivers'] = $nearbyDrivers->count();
            $stats['search_radius_km'] = $radiusKm;
        }

        return $stats;
    }

    /**
     * Check if a driver is available for a ride.
     */
    public function isDriverAvailable(DriverProfile $driver, Ride $ride): bool
    {
        // Check basic availability
        if (! $driver->is_verified || ! $driver->is_available) {
            return false;
        }

        // Check if driver has an active ride
        $activeRide = Ride::where('driver_id', $driver->user_id)
            ->whereIn('status', ['assigned', 'accepted', 'arrived', 'in_progress'])
            ->exists();

        if ($activeRide) {
            return false;
        }

        // Check if driver is within acceptable distance (if location available)
        if ($driver->current_latitude && $driver->current_longitude) {
            $distance = $this->calculateDistance(
                $ride->pickup_latitude,
                $ride->pickup_longitude,
                $driver->current_latitude,
                $driver->current_longitude
            );

            $maxDistance = config('app.driver_search_radius_km', 10);

            if ($distance > $maxDistance) {
                return false;
            }
        }

        return true;
    }

    /**
     * Calculate distance between two coordinates using Haversine formula.
     *
     * @return float Distance in kilometers
     */
    private function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371; // Earth's radius in kilometers

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}
