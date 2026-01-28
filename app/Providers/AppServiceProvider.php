<?php

namespace App\Providers;

use App\PassportGrants\SocialGrant;
use Dedoc\Scramble\Scramble;
use Dedoc\Scramble\Support\Generator\OpenApi;
use Dedoc\Scramble\Support\Generator\SecurityScheme;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;
use League\OAuth2\Server\AuthorizationServer;
use Laravel\Passport\Bridge\RefreshTokenRepository;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //$this->app->bind(SocialGrantUserProvider::class, AppSocialGrantUserProvider::class);
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
