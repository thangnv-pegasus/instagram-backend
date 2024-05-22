<?php

namespace App\Providers;

use Carbon\Carbon;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Laravel\Passport\Passport;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // $this->registerPolicies();
        $this->registerPolicies();
        Passport::ignoreRoutes();
        // Passport::personalAccessTokensExpireIn(Carbon::now()->addDays(1));
        // Passport::refreshTokensExpireIn(Carbon::now()->addDays(30));
        // Passport::tokensExpireIn(now()->addDays(1));
        //
    }
}
