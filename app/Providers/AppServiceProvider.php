<?php

namespace App\Providers;

// use App\Models\Product;
// use App\Observers\ProductObserver;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        // Defining super-admin through Gate
        // This code will run before checking any permission
        Gate::before(function ($user, $ability) {
            return $user->hasRole('admin') ? true : null;
        });

        // Observers
        // Product::observe(ProductObserver::class);

    }

    protected $policies = [
        \Illuminate\Notifications\DatabaseNotification::class => \App\Policies\NotificationPolicy::class,
    ];
}
