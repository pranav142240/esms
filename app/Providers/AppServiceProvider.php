<?php

namespace App\Providers;

use App\Domain\Superadmin\Repositories\SuperadminRepositoryInterface;
use App\Domain\Superadmin\Repositories\SuperadminRepository;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Bind repository interfaces to their concrete implementations
        $this->app->bind(SuperadminRepositoryInterface::class, SuperadminRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        ResetPassword::createUrlUsing(function (object $notifiable, string $token) {
            return config('app.frontend_url')."/password-reset/$token?email={$notifiable->getEmailForPasswordReset()}";
        });
    }
}
