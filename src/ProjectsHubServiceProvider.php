<?php

namespace Appload\ProjectsHub;

use Appload\ProjectsHub\Console\Commands\OpenApiDiffCommand;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class ProjectsHubServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/projects-hub.php',
            'projects-hub'
        );
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/projects-hub.php' => config_path('projects-hub.php'),
        ], 'projects-hub-config');

        if ($this->app->runningInConsole()) {
            $this->commands([
                OpenApiDiffCommand::class,
            ]);
        }

        if (! config('projects-hub.enabled')) {
            return;
        }

        Route::prefix(config('projects-hub.route.prefix'))
            ->middleware(config('projects-hub.route.middleware', ['api']))
            ->group(__DIR__ . '/../routes/api.php');
    }
}