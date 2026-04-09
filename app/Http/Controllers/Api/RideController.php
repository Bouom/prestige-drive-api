<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Ride\CancelRideRequest;
use App\Http\Requests\Ride\QuoteRequest;
use App\Http\Requests\Ride\StoreRideRequest;
use App\Http\Requests\Ride\UpdateRideRequest;
use App\Http\Resources\RideQuoteResource;
use App\Http\Resources\RideResource;
use App\Models\Ride;
use App\Models\RideQuote;
use App\Models\VehicleBrand;
use App\Models\VehicleModel;
use App\Services\PricingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Str;

class RideController extends BaseController
{
    public function __construct(
        private PricingService $pricingService,
    ) {}

    /**
     * Get all rides (with filtering).
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $user = $request->user();
        $query = Ride::with(['customer', 'driver.user', 'vehicle.vehicleModel.vehicleBrand', 'tripType']);

        // Scope rides by user role
        if ($user->isAdmin()) {
            // Admin sees all rides
        } elseif ($user->isDriver() && $user->driverProfile) {
            $query->where('driver_id', $user->driverProfile->id);
        } elseif ($user->isCompany() && $user->companies()->exists()) {
            $companyIds = $user->companies()->pluck('companies.id');
            $query->whereIn('company_id', $companyIds);
        } else {
            $query->where('customer_id', $user->id);
        }

        if ($request->has('status')) {
            $statuses = array_filter(array_map('trim', explode(',', $request->status)));
            $query->whereIn('status', $statuses);
        }

        if ($request->has('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->has('from_date')) {
            $query->where('scheduled_at', '>=', $request->from_date);
        }

        if ($request->has('to_date')) {
            $query->where('scheduled_at', '<=', $request->to_date);
        }

        $query->orderBy('scheduled_at', 'desc');

        $rides = $query->paginate(15);

        return RideResource::collection($rides);
    }

    /**
     * Get a quote for a ride (price simulation).
     */
    public function quote(QuoteRequest $request): JsonResponse
    {
        // Resolve vehicle brand: use provided ID or find-or-create by name
        $vehicleBrandId = $request->vehicle_brand_id;
        if (! $vehicleBrandId && $request->vehicle_brand_name) {
            $brand = VehicleBrand::firstOrCreate(
                ['name' => $request->vehicle_brand_name],
                ['slug' => Str::slug($request->vehicle_brand_name), 'is_active' => true]
            );
            $vehicleBrandId = $brand->id;
        }

        // Resolve vehicle model: use provided ID or find-or-create by name under the brand
        $vehicleModelId = $request->vehicle_model_id;
        if (! $vehicleModelId && $request->vehicle_model_name && $vehicleBrandId) {
            $model = VehicleModel::firstOrCreate(
                ['vehicle_brand_id' => $vehicleBrandId, 'name' => $request->vehicle_model_name],
                ['slug' => Str::slug($request->vehicle_model_name), 'is_active' => true]
            );
            $vehicleModelId = $model->id;
        }

        $pricing = $this->pricingService->calculate(
            distanceKm: (float) $request->estimated_distance_km,
            isRoundTrip: $request->boolean('is_round_trip'),
            tripTypeId: $request->trip_type_id,
            options: $request->only('discount_code'),
        );

        $quote = RideQuote::create([
            'user_id' => auth('api')->id(),
            'pickup_address' => $request->pickup_address,
            'pickup_latitude' => $request->pickup_latitude,
            'pickup_longitude' => $request->pickup_longitude,
            'dropoff_address' => $request->dropoff_address,
            'dropoff_latitude' => $request->dropoff_latitude,
            'dropoff_longitude' => $request->dropoff_longitude,
            'trip_type_id' => $request->trip_type_id,
            'trip_purpose' => $request->trip_purpose ?? 'personal',
            'is_round_trip' => $request->boolean('is_round_trip'),
            'passenger_count' => $request->passenger_count ?? 1,
            'vehicle_brand_id' => $vehicleBrandId,
            'vehicle_model_id' => $vehicleModelId,
            'scheduled_at' => $request->scheduled_at,
            'return_scheduled_at' => $request->return_scheduled_at,
            'estimated_distance_km' => $request->estimated_distance_km,
            'estimated_duration_min' => $request->estimated_duration_min,
            'estimated_price' => $pricing['final_total'],
            'guest_token' => $request->input('guest_token'),
            'session_id' => session()->getId(),
            'ip_address' => $request->ip(),
            'expires_at' => now()->addHours(24),
        ]);

        return $this->sendResponse(
            new RideQuoteResource($quote->load(['tripType', 'vehicleBrand', 'vehicleModel']), $pricing),
            'Devis généré avec succès.'
        );
    }

    /**
     * Update an existing quote (re-simulate with new parameters).
     */
    public function updateQuote(QuoteRequest $request, int $id): JsonResponse
    {
        $quote = RideQuote::findOrFail($id);

        // Resolve vehicle brand
        $vehicleBrandId = $request->vehicle_brand_id;
        if (! $vehicleBrandId && $request->vehicle_brand_name) {
            $brand = VehicleBrand::firstOrCreate(
                ['name' => $request->vehicle_brand_name],
                ['slug' => Str::slug($request->vehicle_brand_name), 'is_active' => true]
            );
            $vehicleBrandId = $brand->id;
        }

        // Resolve vehicle model
        $vehicleModelId = $request->vehicle_model_id;
        if (! $vehicleModelId && $request->vehicle_model_name && $vehicleBrandId) {
            $model = VehicleModel::firstOrCreate(
                ['vehicle_brand_id' => $vehicleBrandId, 'name' => $request->vehicle_model_name],
                ['slug' => Str::slug($request->vehicle_model_name), 'is_active' => true]
            );
            $vehicleModelId = $model->id;
        }

        $pricing = $this->pricingService->calculate(
            distanceKm: (float) $request->estimated_distance_km,
            isRoundTrip: $request->boolean('is_round_trip'),
            tripTypeId: $request->trip_type_id,
            options: $request->only('discount_code'),
        );

        $quote->update([
            'user_id' => auth('api')->id() ?? $quote->user_id,
            'pickup_address' => $request->pickup_address,
            'pickup_latitude' => $request->pickup_latitude,
            'pickup_longitude' => $request->pickup_longitude,
            'dropoff_address' => $request->dropoff_address,
            'dropoff_latitude' => $request->dropoff_latitude,
            'dropoff_longitude' => $request->dropoff_longitude,
            'trip_type_id' => $request->trip_type_id,
            'trip_purpose' => $request->trip_purpose ?? 'personal',
            'is_round_trip' => $request->boolean('is_round_trip'),
            'passenger_count' => $request->passenger_count ?? 1,
            'vehicle_brand_id' => $vehicleBrandId,
            'vehicle_model_id' => $vehicleModelId,
            'scheduled_at' => $request->scheduled_at,
            'return_scheduled_at' => $request->return_scheduled_at,
            'estimated_distance_km' => $request->estimated_distance_km,
            'estimated_duration_min' => $request->estimated_duration_min,
            'estimated_price' => $pricing['final_total'],
            'expires_at' => now()->addHours(24),
        ]);

        return $this->sendResponse(
            new RideQuoteResource($quote->fresh(['tripType', 'vehicleBrand', 'vehicleModel']), $pricing),
            'Simulation mise à jour avec succès.'
        );
    }

    /**
     * Get the authenticated user's ride quotes (simulations).
     */
    public function myQuotes(Request $request): AnonymousResourceCollection
    {
        $quotes = RideQuote::where('user_id', $request->user()->id)
            ->whereNull('converted_to_ride_id')
            ->with(['tripType', 'vehicleBrand', 'vehicleModel'])
            ->orderByDesc('created_at')
            ->paginate(15);

        return RideQuoteResource::collection($quotes);
    }

    /**
     * Create a new ride booking.
     */
    public function store(StoreRideRequest $request): JsonResponse
    {
        $ride = Ride::create([
            'customer_id' => $request->user()->id,
            'trip_type_id' => $request->trip_type_id,
            'pickup_address' => $request->pickup_address,
            'pickup_latitude' => $request->pickup_latitude,
            'pickup_longitude' => $request->pickup_longitude,
            'dropoff_address' => $request->dropoff_address,
            'dropoff_latitude' => $request->dropoff_latitude,
            'dropoff_longitude' => $request->dropoff_longitude,
            'scheduled_at' => $request->scheduled_at,
            'estimated_distance_km' => $request->estimated_distance_km,
            'estimated_duration_min' => $request->estimated_duration_min,
            'passenger_count' => $request->passenger_count ?? 1,
            'has_luggage' => $request->boolean('has_luggage'),
            'requires_child_seat' => $request->boolean('requires_child_seat'),
            'is_round_trip' => $request->boolean('is_round_trip'),
            'return_scheduled_at' => $request->return_scheduled_at,
            'customer_notes' => $request->customer_notes,
            'base_price' => $request->base_price,
            'total_price' => $request->total_price,
            'final_price' => $request->final_price,
            'status' => 'pending',
            'payment_status' => 'pending',
        ]);

        // Link the quote to the newly created ride
        if ($request->filled('quote_id')) {
            RideQuote::where('id', $request->quote_id)
                ->where('user_id', $request->user()->id)
                ->whereNull('converted_to_ride_id')
                ->update(['converted_to_ride_id' => $ride->id]);
        }

        return $this->sendResponse(
            new RideResource($ride->load(['customer', 'tripType'])),
            'Course réservée avec succès.'
        );
    }

    /**
     * Get a specific ride.
     */
    public function show(string $uuid): JsonResponse
    {
        $ride = Ride::where('uuid', $uuid)
            ->with(['customer', 'driver.user', 'vehicle.vehicleModel.vehicleBrand', 'tripType', 'waypoints', 'review'])
            ->firstOrFail();

        $this->authorize('view', $ride);

        return $this->sendResponse(
            new RideResource($ride),
            'Course récupérée avec succès.'
        );
    }

    /**
     * Update a ride.
     */
    public function update(UpdateRideRequest $request, string $uuid): JsonResponse
    {
        $ride = Ride::where('uuid', $uuid)->firstOrFail();

        $this->authorize('update', $ride);

        $ride->update($request->validated());

        return $this->sendResponse(
            new RideResource($ride->fresh(['customer', 'driver', 'tripType'])),
            'Course mise à jour avec succès.'
        );
    }

    /**
     * Cancel a ride.
     */
    public function cancel(CancelRideRequest $request, string $uuid): JsonResponse
    {
        $ride = Ride::where('uuid', $uuid)->firstOrFail();

        $this->authorize('cancel', $ride);

        $ride->update([
            'status' => 'cancelled_by_'.($request->user()->isDriver() ? 'driver' : 'customer'),
            'cancelled_at' => now(),
            'cancellation_reason' => $request->reason,
        ]);

        return $this->sendResponse(
            new RideResource($ride),
            'Course annulée avec succès.'
        );
    }

    /**
     * Start a ride (driver action).
     */
    public function start(Request $request, string $uuid): JsonResponse
    {
        $ride = Ride::where('uuid', $uuid)->firstOrFail();

        $this->authorize('start', $ride);

        $ride->update([
            'status' => 'in_progress',
            'started_at' => now(),
            'pickup_photo_url' => $request->pickup_photo_url,
        ]);

        return $this->sendResponse(
            new RideResource($ride),
            'Course démarrée.'
        );
    }

    /**
     * Complete a ride (driver action).
     */
    public function complete(Request $request, string $uuid): JsonResponse
    {
        $ride = Ride::where('uuid', $uuid)->firstOrFail();

        $this->authorize('complete', $ride);

        $ride->update([
            'status' => 'completed',
            'completed_at' => now(),
            'actual_distance_km' => $request->actual_distance_km,
            'actual_duration_min' => $request->actual_duration_min,
            'dropoff_photo_url' => $request->dropoff_photo_url,
            'driver_notes' => $request->driver_notes,
        ]);

        return $this->sendResponse(
            new RideResource($ride),
            'Course terminée avec succès.'
        );
    }

    /**
     * Delete a ride (soft delete).
     */
    public function destroy(string $uuid): JsonResponse
    {
        $ride = Ride::where('uuid', $uuid)->firstOrFail();

        $this->authorize('delete', $ride);

        $ride->delete();

        return $this->sendResponse([], 'Course supprimée avec succès.');
    }
}
