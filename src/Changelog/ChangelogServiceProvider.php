<?php

namespace Appload\ProjectsHub\Changelog;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;
use Appload\ProjectsHub\Changelog\Commands\AddChangelog;
use Appload\ProjectsHub\Changelog\Commands\GenerateChangelogMdCommand;
use Appload\ProjectsHub\Changelog\Commands\ListChangelog;
use Appload\ProjectsHub\Changelog\Commands\ReleaseChangelog;
use Appload\ProjectsHub\Changelog\Commands\ShowChangelog;
use Appload\ProjectsHub\Changelog\Commands\SuggestRelease;
use Appload\ProjectsHub\Changelog\Commands\SetReleaseChangelog;
use Appload\ProjectsHub\Changelog\Commands\ShowVersion;
use Appload\ProjectsHub\Changelog\Commands\UpdateVersion;
use Appload\ProjectsHub\Changelog\Logic\Version;
use Appload\ProjectsHub\Changelog\Logic\VersionHandling;
use Appload\ProjectsHub\Changelog\Util\Constants;

class ChangelogServiceProvider extends ServiceProvider
{
    private function packagePath(string $path = ''): string
    {
        $basePath = __DIR__;

        return $path === '' ? $basePath : $basePath . '/' . ltrim($path, '/');
    }

    #[\Override]
    public function register(): void
    {
        $this->mergeConfigFrom($this->packagePath('config/config.php'), 'releasechangelog');

        $this->app->singleton(Constants::APP_VERSION_HANDLING, static function () {
            return new VersionHandling();
        });
        $this->app->singleton('releasechangelog.version', static function () {
            return new Version();
        });
    }

    public function boot(): void
    {
        // Register the command if we are using the application via the CLI
        if ($this->app->runningInConsole()) {
            $this->commands([
                ReleaseChangelog::class,
                AddChangelog::class,
                UpdateVersion::class,
                ShowVersion::class,
                SetReleaseChangelog::class,
                GenerateChangelogMdCommand::class,
                ListChangelog::class,
                ShowChangelog::class,
                SuggestRelease::class,
            ]);

            $this->publishes([
                $this->packagePath('resources/.version/version.yml') => resource_path('.version/version.yml'),
                $this->packagePath('resources/.changes/changelog.json') => resource_path('.changes/changelog.json'),
            ], 'projects-hub-changelog-resources');
        }

        Blade::directive(
            Config::get('releasechangelog.blade-directive', 'releasechangelog'),
            static function (string $format = Constants::DEFAULT_FORMAT) {
                return "<?php echo app('releasechangelog.version')->showVersion({$format}); ?>";
            }
        );
    }
}
