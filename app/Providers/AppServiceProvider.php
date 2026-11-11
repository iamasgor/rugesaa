<?php

namespace App\Providers;

use App\Models\EventRegistration;
use App\Models\Membership;
use App\Models\User;
use App\Services\SslCommerzGateway;
use Carbon\CarbonImmutable;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(SslCommerzGateway::class, fn (): SslCommerzGateway => new SslCommerzGateway(
            storeId: config('services.sslcommerz.store_id'),
            storePassword: config('services.sslcommerz.store_password'),
            sandbox: (bool) config('services.sslcommerz.sandbox'),
        ));
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();

        Gate::define('view-directory', fn (User $user): bool => $user->hasMemberAccess());

        RateLimiter::for('contact', fn (Request $request): Limit => Limit::perHour(5)->by($request->ip()));
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        Relation::morphMap([
            'membership' => Membership::class,
            'event_registration' => EventRegistration::class,
        ]);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
