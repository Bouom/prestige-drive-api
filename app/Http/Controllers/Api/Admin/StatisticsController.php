<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\BaseController;
use App\Models\Company;
use App\Models\DriverProfile;
use App\Models\Payment;
use App\Models\Ride;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StatisticsController extends BaseController
{
    /**
     * Get dashboard statistics overview.
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', User::class);

        $period = $request->input('period', '30');

        $users = $this->getUserStats($period);
        $rides = $this->getRideStats($period);
        $revenue = $this->getRevenueStats($period);
        $drivers = $this->getDriverStats($period);
        $companies = $this->getCompanyStats($period);

        $pendingRides = Ride::whereIn('status', ['pending', 'confirmed', 'accepted', 'assigned'])->count();

        return $this->sendResponse([
            // Flat keys expected by the frontend AdminDashboardPage
            'total_users' => $users['total'],
            'recent_registrations' => $users['new'],
            'total_rides' => $rides['total'],
            'pending_rides' => $pendingRides,
            'total_revenue' => $revenue['total'],
            'active_drivers' => $drivers['available'],
            'pending_verifications' => $drivers['total'] - $drivers['verified'],
            // Full nested breakdown still available
            'users' => $users,
            'rides' => $rides,
            'revenue' => $revenue,
            'drivers' => $drivers,
            'companies' => $companies,
            'period_days' => $period,
            'generated_at' => now()->toIso8601String(),
        ], 'Statistiques du tableau de bord récupérées avec succès.');
    }

    /**
     * Get user statistics.
     */
    public function users(): JsonResponse
    {
        $total = User::count();
        $active = User::where('is_active', true)->count();
        $verified = User::whereNotNull('email_verified_at')->count();

        $byType = User::select('user_type_id', DB::raw('count(*) as count'))
            ->groupBy('user_type_id')
            ->with('userType:id,name')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->userType->name ?? 'Inconnu' => $item->count];
            });

        $recentRegistrations = User::where('created_at', '>=', now()->subDays(30))
            ->selectRaw('DATE(created_at) as date, count(*) as count')
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->get();

        return $this->sendResponse([
            'total' => $total,
            'active' => $active,
            'verified' => $verified,
            'inactive' => $total - $active,
            'by_type' => $byType,
            'recent_registrations' => $recentRegistrations,
        ], 'Statistiques des utilisateurs récupérées avec succès.');
    }

    /**
     * Get ride statistics.
     */
    public function rides(Request $request): JsonResponse
    {
        $period = $request->input('period', 30);
        $startDate = now()->subDays($period);

        $total = Ride::count();
        $completed = Ride::where('status', 'completed')->count();
        $cancelled = Ride::whereIn('status', ['cancelled_by_customer', 'cancelled_by_driver', 'cancelled_by_admin'])->count();
        $pending = Ride::where('status', 'pending')->count();
        $inProgress = Ride::where('status', 'in_progress')->count();

        $recentRides = Ride::where('created_at', '>=', $startDate)
            ->selectRaw('DATE(created_at) as date, count(*) as count')
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        $byStatus = Ride::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status');

        return $this->sendResponse([
            'total' => $total,
            'completed' => $completed,
            'cancelled' => $cancelled,
            'pending' => $pending,
            'in_progress' => $inProgress,
            'completion_rate' => $total > 0 ? round($completed / $total, 4) : 0,
            'cancellation_rate' => $total > 0 ? round($cancelled / $total, 4) : 0,
            'by_status' => $byStatus,
            'by_date' => $recentRides,
        ], 'Statistiques des courses récupérées avec succès.');
    }

    /**
     * Get revenue statistics.
     */
    public function revenue(Request $request): JsonResponse
    {
        $period = $request->input('period', 30);
        $startDate = now()->subDays($period);

        $totalRevenue = Payment::where('status', 'succeeded')->sum('amount');

        $periodRevenue = Payment::where('status', 'succeeded')
            ->where('created_at', '>=', $startDate)
            ->sum('amount');

        $avgTransactionValue = Payment::where('status', 'succeeded')->avg('amount');

        $revenueByDate = Payment::where('status', 'succeeded')
            ->where('created_at', '>=', $startDate)
            ->selectRaw('DATE(created_at) as date, SUM(amount) as total, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        $revenueByMethod = Payment::where('status', 'succeeded')
            ->select('payment_method_type', DB::raw('SUM(amount) as total, COUNT(*) as count'))
            ->groupBy('payment_method_type')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->payment_method_type ?? 'Inconnu' => [
                    'total' => (float) $item->total,
                    'count' => $item->count,
                ]];
            });

        return $this->sendResponse([
            'total_revenue' => round($totalRevenue, 2),
            'period_revenue' => round($periodRevenue, 2),
            'average_transaction' => round($avgTransactionValue ?? 0, 2),
            'by_date' => $revenueByDate,
            'by_payment_method' => $revenueByMethod,
            'currency' => 'EUR',
        ], 'Statistiques de revenus récupérées avec succès.');
    }

    /**
     * Get driver statistics.
     */
    public function drivers(): JsonResponse
    {
        $total = DriverProfile::count();
        $verified = DriverProfile::where('is_verified', true)->count();
        $available = DriverProfile::where('is_available', true)
            ->where('is_verified', true)
            ->count();

        $topDrivers = DriverProfile::where('is_verified', true)
            ->orderBy('total_rides', 'desc')
            ->limit(10)
            ->with('user:id,first_name,last_name,email')
            ->get()
            ->map(function ($driver) {
                return [
                    'id' => $driver->id,
                    'name' => $driver->user->first_name.' '.$driver->user->last_name,
                    'email' => $driver->user->email,
                    'total_rides' => $driver->total_rides,
                    'total_earnings' => round($driver->total_earnings, 2),
                    'acceptance_rate' => round($driver->acceptance_rate, 2),
                ];
            });

        return $this->sendResponse([
            'total' => $total,
            'verified' => $verified,
            'available' => $available,
            'pending_verification' => $total - $verified,
            'top_drivers' => $topDrivers,
        ], 'Statistiques des chauffeurs récupérées avec succès.');
    }

    /**
     * Get company statistics.
     */
    public function companies(): JsonResponse
    {
        $total = Company::count();
        $verified = Company::where('is_verified', true)->count();
        $active = Company::where('is_active', true)->count();

        $recentCompanies = Company::where('created_at', '>=', now()->subDays(30))->count();

        $topCompanies = Company::where('is_verified', true)
            ->withCount('rides')
            ->orderBy('rides_count', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($company) {
                return [
                    'id' => $company->id,
                    'name' => $company->name,
                    'email' => $company->contact_email,
                    'total_rides' => $company->rides_count,
                ];
            });

        return $this->sendResponse([
            'total' => $total,
            'verified' => $verified,
            'active' => $active,
            'pending_verification' => $total - $verified,
            'recent_registrations' => $recentCompanies,
            'top_companies' => $topCompanies,
        ], 'Statistiques des sociétés récupérées avec succès.');
    }

    private function getUserStats(int $days): array
    {
        $startDate = now()->subDays($days);

        return [
            'total' => User::count(),
            'new' => User::where('created_at', '>=', $startDate)->count(),
            'active' => User::where('is_active', true)->count(),
        ];
    }

    private function getRideStats(int $days): array
    {
        $startDate = now()->subDays($days);

        return [
            'total' => Ride::count(),
            'new' => Ride::where('created_at', '>=', $startDate)->count(),
            'completed' => Ride::where('status', 'completed')
                ->where('completed_at', '>=', $startDate)
                ->count(),
            'cancelled' => Ride::whereIn('status', ['cancelled_by_customer', 'cancelled_by_driver', 'cancelled_by_admin'])
                ->where('updated_at', '>=', $startDate)
                ->count(),
        ];
    }

    private function getRevenueStats(int $days): array
    {
        $startDate = now()->subDays($days);

        $revenue = Payment::where('status', 'succeeded')
            ->where('created_at', '>=', $startDate)
            ->sum('amount');

        return [
            'total' => round($revenue, 2),
            'transactions' => Payment::where('status', 'succeeded')
                ->where('created_at', '>=', $startDate)
                ->count(),
        ];
    }

    private function getDriverStats(int $days): array
    {
        $startDate = now()->subDays($days);

        return [
            'total' => DriverProfile::count(),
            'new' => DriverProfile::where('created_at', '>=', $startDate)->count(),
            'verified' => DriverProfile::where('is_verified', true)->count(),
            'available' => DriverProfile::where('is_available', true)->count(),
        ];
    }

    private function getCompanyStats(int $days): array
    {
        $startDate = now()->subDays($days);

        return [
            'total' => Company::count(),
            'new' => Company::where('created_at', '>=', $startDate)->count(),
            'verified' => Company::where('is_verified', true)->count(),
        ];
    }
}
