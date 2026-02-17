<?php

namespace App\Providers;

use App\PassportGrants\SocialGrant;
use Dedoc\Scramble\Scramble;
use Dedoc\Scramble\Support\Generator\OpenApi;
use Dedoc\Scramble\Support\Generator\SecurityScheme;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Bridge\RefreshTokenRepository;
use Laravel\Passport\Passport;
use League\OAuth2\Server\AuthorizationServer;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // $this->app->bind(SocialGrantUserProvider::class, AppSocialGrantUserProvider::class);
        // Register services
        $this->app->singleton(\App\Services\PricingService::class);
        $this->app->singleton(\App\Services\LocationService::class);
        $this->app->singleton(\App\Services\DriverMatchingService::class);
        $this->app->singleton(\App\Services\FileStorageService::class);
        $this->app->singleton(\App\Services\NotificationService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Scramble::configure()
            ->withDocumentTransformers(function (OpenApi $openApi) {
                $openApi->secure(
                    SecurityScheme::http('bearer')
                );
            });

        // Enable signed URLs for email verification
        if (env('APP_ENV') !== 'local') {
            URL::forceScheme('https');
        }

        // Initialize Passport routes and configurations
        Passport::enablePasswordGrant();
        Passport::tokensExpireIn(now()->addMinutes(15));
        Passport::refreshTokensExpireIn(now()->addMonths(6));

        // Register policies
        Gate::policy(\App\Models\Ride::class, \App\Policies\RidePolicy::class);
        Gate::policy(\App\Models\User::class, \App\Policies\UserPolicy::class);
        Gate::policy(\App\Models\Company::class, \App\Policies\CompanyPolicy::class);
        Gate::policy(\App\Models\DriverProfile::class, \App\Policies\DriverProfilePolicy::class);
        Gate::policy(\App\Models\Payment::class, \App\Policies\PaymentPolicy::class);
        Gate::policy(\App\Models\Review::class, \App\Policies\ReviewPolicy::class);
        Gate::policy(\App\Models\Vehicle::class, \App\Policies\VehiclePolicy::class);
        Gate::policy(\App\Models\Refund::class, \App\Policies\RefundPolicy::class);

        $this->registerGrants();
    }

    protected function registerGrants()
    {
        $server = $this->app->make(AuthorizationServer::class);

        $server->enableGrantType(
            new SocialGrant($this->app->make(RefreshTokenRepository::class)),
            Passport::tokensExpireIn()
        );
    }
}
