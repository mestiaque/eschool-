<?php
namespace ME\SchoolManagement;

use Illuminate\Support\ServiceProvider;

class SchoolManagementServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadRoutesFrom(__DIR__ . '/routes/web.php');
        $this->loadRoutesFrom(__DIR__ . '/routes/api.php');
        $this->loadViewsFrom(__DIR__ . '/resources/views', 'school-management');
        $this->loadTranslationsFrom(__DIR__.'/resources/lang', 'school-management');

        // Bangla validation messages when the host app has none of its own.
        $this->app->booted(function () {
            if (!is_file(lang_path('bn/validation.php'))) {
                $lines = \Illuminate\Support\Arr::dot(require __DIR__ . '/resources/lang/bn/validation.php');
                $this->app['translator']->addLines(
                    collect($lines)->mapWithKeys(fn ($v, $k) => ["validation.{$k}" => $v])->all(), 'bn');
            }
        });
        $this->loadMigrationsFrom(__DIR__.'/Database/migrations');
        $this->publishes([
            __DIR__.'/Database/Seeders' => database_path('seeders'),
        ], 'school-management-seeders');
        $this->publishes([
            __DIR__.'/Config' => config_path('school-management'),
        ], 'school-management-config');
        // The theme header falls back to backend/img/undraw_profile.svg for users
        // without a photo but doesn't ship that file.
        $this->publishes([
            __DIR__.'/public/img/avatar.svg' => public_path('backend/img/undraw_profile.svg'),
        ], ['school-management-assets', 'laravel-assets']);

        // Swap the admin sidebar for a teacher-only one when the logged-in
        // account is linked to a teacher, without touching the host theme.
        $this->addWebMiddleware(\ME\SchoolManagement\Http\Middleware\TeacherSidebar::class);

        // Never send the registration OTP back to the browser outside local development.
        $this->addWebMiddleware(\ME\SchoolManagement\Http\Middleware\HideOtpDebug::class);

        // After login, land on the school dashboard unless the host chose
        // another home page (METHEME_HOME_ROUTE).
        if (!config('me_settings.home_route')) {
            config(['me_settings.home_route' => 'dashboard']);
        }

        // Timezone chosen in Settings → General.
        $timezone = \ME\SchoolManagement\Models\SchoolSetting::cached()['timezone'] ?? null;
        if ($timezone && in_array($timezone, timezone_identifiers_list(), true)) {
            config(['app.timezone' => $timezone]);
            date_default_timezone_set($timezone);
        }
    }

    public function register()
    {
        require_once __DIR__ . '/helpers.php';

        if (file_exists(__DIR__ . '/Config/config.php')) {
            $this->mergeConfigFrom(__DIR__ . '/Config/config.php', 'school-management');
        }

        if (file_exists(__DIR__ . '/Config/sidebar.php')) {
            $this->mergeConfigFrom(__DIR__ . '/Config/sidebar.php', 'sidebar');
        }

        // Roles & Permissions management (the actual CRUD screens) is owned
        // by the metheme host theme (me.roles.*, me.users.*). This package
        // only contributes its own module/action catalog into the same
        // config('permissions') key metheme's Role form reads to build its
        // permission checkboxes — metheme ships that key empty by design.
        if (file_exists(__DIR__ . '/Config/permissions.php')) {
            $this->mergeConfigFrom(__DIR__ . '/Config/permissions.php', 'permissions');
        }
    }

    /**
     * Add to the "web" group on the HTTP kernel as well as the router: the
     * kernel copies its own groups onto the router whenever it is created
     * (e.g. in tests), which would drop a router-only addition.
     */
    private function addWebMiddleware(string $middleware): void
    {
        $kernel = $this->app->make(\Illuminate\Contracts\Http\Kernel::class);
        if (method_exists($kernel, 'appendMiddlewareToGroup')) {
            $kernel->appendMiddlewareToGroup('web', $middleware);
        }
        $this->app['router']->pushMiddlewareToGroup('web', $middleware);
    }
}
