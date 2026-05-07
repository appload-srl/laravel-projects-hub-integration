<?php

namespace Appload\ProjectsHub;

use Appload\ProjectsHub\Console\Commands\ProjectsHubInstallCommand;
use Appload\ProjectsHub\Console\Commands\OpenApiDiffCommand;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Appload\ProjectsHub\Changelog\ChangelogServiceProvider;

class ProjectsHubServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->register(ChangelogServiceProvider::class);

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

        $this->publishes([
            __DIR__ . '/../stubs/github/workflows/openapi-diff-on-tag.yml' => base_path('.github/workflows/openapi-diff-on-tag.yml'),
            __DIR__ . '/../stubs/github/workflows/on-sync-dev-api-diff.yml' => base_path('.github/workflows/on-sync-dev-api-diff.yml'),
            __DIR__ . '/../stubs/github/workflows/on-sync-staging-api-diff.yml' => base_path('.github/workflows/on-sync-staging-api-diff.yml'),
            __DIR__ . '/../stubs/github/workflows/on-sync-prod-api-diff.yml' => base_path('.github/workflows/on-sync-prod-api-diff.yml'),
        ], 'projects-hub-github-workflows');

        if ($this->app->runningInConsole()) {
            $this->commands([
                ProjectsHubInstallCommand::class,
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
