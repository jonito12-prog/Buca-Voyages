<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

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
        try {
            \Illuminate\Support\Facades\Gate::before(function ($user, $ability) {
                if ($user->role?->slug === 'admin') {
                    return true;
                }
            });

            // We do a simple schema check to prevent crashes if DB isn't migrated yet
            if (\Illuminate\Support\Facades\Schema::hasTable('permissions')) {
                $permissions = \App\Models\Permission::all();
                foreach ($permissions as $permission) {
                    \Illuminate\Support\Facades\Gate::define($permission->slug, function ($user) use ($permission) {
                        return $user->hasPermission($permission->slug);
                    });
                }
            }
        } catch (\Exception $e) {
            // Prevent crashes during early bootstrapping, install or migration
        }
    }
}
