<?php

namespace Appload\ProjectsHub\Console\Commands;

use Illuminate\Console\Command;
class ProjectsHubInstallCommand extends Command
{
    protected $signature = 'projects-hub:install {--force : Overwrite already published files}';

    protected $description = 'Install Projects Hub and publish required resources.';

    public function handle(): int
    {
        $force = (bool) $this->option('force');

        $this->components->info('Publishing Projects Hub config...');

        $this->callSilent('vendor:publish', [
            '--tag' => 'projects-hub-config',
            '--force' => $force,
        ]);

        $this->components->info('Publishing changelog generator files...');

        $this->callSilent('vendor:publish', [
            '--tag' => 'projects-hub-changelog-resources',
            '--force' => $force,
        ]);

        $this->components->success('Projects Hub installed successfully.');

        return self::SUCCESS;
    }
}
