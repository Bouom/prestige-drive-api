<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PricingSnapshotResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * This resource captures pricing rules at the time of quote/ride creation
     * to maintain historical accuracy even when pricing rules change.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->uuid,

            // Reference
            'snapshot_date' => $this->snapshot_date->toIso8601String(),
            'trip_type' => $this->trip_type_name,
            'vehicle_category' => $this->vehicle_category,

            // Base Rates (as they were at time of snapshot)
            'rates' => [
                'base_rate' => (float) $this->base_rate,
                'per_km_rate' => (float) $this->per_km_rate,
                'per_minute_rate' => (float) $this->per_minute_rate,
                'minimum_fare' => (float) $this->minimum_fare,
                'booking_fee' => (float) $this->booking_fee,
            ],

            // Multipliers Applied
            'multipliers' => [
                'time_of_day' => (float) $this->time_multiplier,
                'day_of_week' => (float) $this->day_multiplier,
                'season' => (float) $this->season_multiplier,
                'demand' => (float) $this->demand_multiplier,
                'total' => (float) $this->total_multiplier,
            ],

            // Surcharges Applied
            'surcharges' => [
                'night_rate' => (float) $this->night_surcharge,
                'peak_hours' => (float) $this->peak_surcharge,
                'weekend' => (float) $this->weekend_surcharge,
                'holiday' => (float) $this->holiday_surcharge,
                'airport' => (float) $this->airport_surcharge,
                'other' => (float) $this->other_surcharge,
                'total' => (float) $this->total_surcharge,
            ],

            // Discounts Applied
            'discounts' => [
                'code' => $this->discount_code,
                'type' => $this->discount_type,
                'value' => (float) $this->discount_value,
                'amount' => (float) $this->discount_amount,
            ],

            // Fees
            'fees' => [
                'platform_fee' => (float) $this->platform_fee,
                'platform_fee_percentage' => (float) $this->platform_fee_percentage,
                'payment_processing_fee' => (float) $this->payment_processing_fee,
                'cancellation_fee' => (float) $this->cancellation_fee,
            ],

            // Taxes
            'taxes' => [
                'vat_rate' => (float) $this->vat_rate,
                'vat_amount' => (float) $this->vat_amount,
                'other_taxes' => (float) $this->other_taxes,
                'total_taxes' => (float) $this->total_taxes,
            ],

            // Price Breakdown
            'breakdown' => [
                'distance_price' => (float) $this->distance_price,
                'time_price' => (float) $this->time_price,
                'base_subtotal' => (float) $this->base_subtotal,
                'after_multipliers' => (float) $this->after_multipliers,
                'after_surcharges' => (float) $this->after_surcharges,
                'after_discounts' => (float) $this->after_discounts,
                'before_taxes' => (float) $this->before_taxes,
                'final_price' => (float) $this->final_price,
            ],

            // Commission Split
            'commission' => $this->when(
                $request->user() && $request->user()->isAdmin(),
                [
                    'platform_percentage' => (float) $this->platform_commission_percentage,
                    'platform_amount' => (float) $this->platform_commission_amount,
                    'driver_percentage' => (float) $this->driver_commission_percentage,
                    'driver_earnings' => (float) $this->driver_earnings,
                ]
            ),

            // Context Information
            'context' => [
                'day_of_week' => $this->day_of_week,
                'time_of_day' => $this->time_of_day,
                'is_peak_hour' => $this->is_peak_hour,
                'is_night' => $this->is_night,
                'is_weekend' => $this->is_weekend,
                'is_holiday' => $this->is_holiday,
                'weather_condition' => $this->weather_condition,
                'demand_level' => $this->demand_level,
            ],

            // Special Conditions
            'conditions' => $this->when($this->special_conditions,
                json_decode($this->special_conditions, true)
            ),

            // Version Control
            'pricing_version' => $this->pricing_version,
            'calculation_method' => $this->calculation_method,

            // Timestamps
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
