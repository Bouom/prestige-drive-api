<?php

namespace App\Services;

use App\Models\PricingRule;
use App\Models\PricingSnapshot;
use App\Models\Prix;
use App\Models\PromoCode;
use App\Models\RetourChauffeur;
use App\Models\Ride;
use App\Models\User;

class PricingService
{
    /**
     * Calculate pricing for a ride.
     */
    public function calculate(
        float $distanceKm,
        bool $isRoundTrip = false,
        ?int $tripTypeId = null,
        array $options = [],
        ?User $user = null
    ): array {
        // Get active per-km rate from prix table (falls back to config)
        $baseRatePerKm = Prix::getActiveRate();

        // Get active return fee from retour_chauffeur table (falls back to config)
        $returnFeeBase = RetourChauffeur::getActiveFee();

        // Get active pricing rule for additional parameters (commission, minimum fare, etc.)
        $pricingRule = PricingRule::where('is_active', true)
            ->where('valid_from', '<=', now())
            ->where(function ($query) {
                $query->whereNull('valid_until')
                    ->orWhere('valid_until', '>=', now());
            })
            ->when($tripTypeId, function ($query) use ($tripTypeId) {
                $query->where('trip_type_id', $tripTypeId);
            })
            ->orderBy('created_at', 'desc')
            ->first();

        $minimumFare = $pricingRule?->minimum_fare ?? (float) config('lcp.ride.minimum_fare', 10.00);
        $platformCommissionRate = $pricingRule?->platform_commission_rate ?? (float) config('lcp.ride.commission_rate', 15.00);

        // Calculate base price
        $basePrice = $distanceKm * $baseRatePerKm;

        // Apply distance tier surcharges
        $surcharges = [];
        $totalSurcharges = 0.00;

        // Long distance surcharge (>= 700km)
        if ($distanceKm >= 700) {
            $longDistanceSurcharge = $basePrice * 0.25; // 25% surcharge
            $surcharges[] = [
                'type' => 'long_distance',
                'description' => 'Long distance surcharge (>= 700km)',
                'amount' => $longDistanceSurcharge,
            ];
            $totalSurcharges += $longDistanceSurcharge;
        }

        // Return fee: applied to one-way trips only (driver must return home)
        // For round trips the driver returns with the passenger, so no return fee
        $returnFee = 0.00;
        if (! $isRoundTrip) {
            $returnFee = $returnFeeBase;
        }

        // Additional surcharge for very long round trips (>= 1400km)
        if ($isRoundTrip && $distanceKm >= 1400) {
            $roundTripSurcharge = $basePrice * 0.50;
            $surcharges[] = [
                'type' => 'very_long_round_trip',
                'description' => 'Supplément aller-retour très longue distance',
                'amount' => $roundTripSurcharge,
            ];
            $totalSurcharges += $roundTripSurcharge;
        }

        // Subtotal before discounts
        $subtotal = $basePrice + $totalSurcharges + $returnFee;

        // Apply minimum fare
        if ($subtotal < $minimumFare) {
            $subtotal = $minimumFare;
        }

        // Apply discounts (if any)
        $discounts = [];
        $totalDiscounts = 0.00;

        // Check for discount code in options
        if (isset($options['discount_code']) && !empty($options['discount_code'])) {
            $promoCode = PromoCode::where('code', strtoupper($options['discount_code']))->first();
            $customerType = $options['customer_type'] ?? null;

            if ($promoCode && $promoCode->isApplicableTo($user, $customerType)) {
                $discountAmount = 0.00;

                if ($promoCode->discount_type === 'percentage') {
                    $discountAmount = $subtotal * ($promoCode->discount_value / 100);
                } else {
                    $discountAmount = min($promoCode->discount_value, $subtotal);
                }

                $discounts[] = [
                    'code' => $promoCode->code,
                    'type' => $promoCode->discount_type,
                    'value' => $promoCode->discount_value,
                    'target_type' => $promoCode->target_type,
                    'amount' => round($discountAmount, 2),
                    'description' => $promoCode->description,
                ];

                $totalDiscounts = round($discountAmount, 2);
            }
        }

        // Calculate platform fee
        $platformFeeAmount = $subtotal * ($platformCommissionRate / 100);

        // Calculate VAT (20% in France)
        $taxRate = 20.00;
        $taxAmount = $subtotal * ($taxRate / 100);

        // Final total
        $finalTotal = $subtotal - $totalDiscounts + $taxAmount;

        // Driver earnings (subtotal - platform commission)
        $driverEarnings = $subtotal - $platformFeeAmount;

        return [
            'distance_km' => $distanceKm,
            'base_rate_per_km' => $baseRatePerKm,
            'base_price' => round($basePrice, 2),
            'surcharges' => $surcharges,
            'total_surcharges' => round($totalSurcharges, 2),
            'return_fee' => round($returnFee, 2),
            'subtotal' => round($subtotal, 2),
            'discounts' => $discounts,
            'total_discounts' => round($totalDiscounts, 2),
            'platform_fee_rate' => $platformCommissionRate,
            'platform_fee_amount' => round($platformFeeAmount, 2),
            'tax_rate' => $taxRate,
            'tax_amount' => round($taxAmount, 2),
            'final_total' => round($finalTotal, 2),
            'driver_earnings' => round($driverEarnings, 2),
            'pricing_rule_id' => $pricingRule?->id,
            'pricing_rule_version' => $pricingRule?->version,
        ];
    }

    /**
     * Create pricing snapshot for a ride.
     */
    public function createSnapshot(Ride $ride, array $pricingData): PricingSnapshot
    {
        return PricingSnapshot::create([
            'ride_id' => $ride->id,
            'pricing_rule_id' => $pricingData['pricing_rule_id'],
            'pricing_rule_version' => $pricingData['pricing_rule_version'],
            'distance_km' => $pricingData['distance_km'],
            'base_rate_per_km' => $pricingData['base_rate_per_km'],
            'base_calculation' => [
                'distance' => $pricingData['distance_km'],
                'rate' => $pricingData['base_rate_per_km'],
                'subtotal' => $pricingData['base_price'],
            ],
            'surcharges' => $pricingData['surcharges'],
            'discounts' => $pricingData['discounts'],
            'return_fee' => $pricingData['return_fee'],
            'platform_fee_rate' => $pricingData['platform_fee_rate'],
            'platform_fee_amount' => $pricingData['platform_fee_amount'],
            'subtotal' => $pricingData['subtotal'],
            'total_surcharges' => $pricingData['total_surcharges'],
            'total_discounts' => $pricingData['total_discounts'],
            'tax_rate' => $pricingData['tax_rate'],
            'tax_amount' => $pricingData['tax_amount'],
            'final_total' => $pricingData['final_total'],
        ]);
    }
}
