<?php

namespace Appload\ProjectsHub\Changelog\Commands;

use Appload\ProjectsHub\Changelog\Util\Constants;

class ShowVersion extends BaseCommand
{
    protected $signature = 'changelog:show-version {--f|format=}';

    protected $description = 'Show current version';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle(): int
    {
        try {
            $format = $this->option('format');
            if (null === $format || '' === trim($format)) {
                $format = Constants::DEFAULT_FORMAT;
            }
            $result = app('releasechangelog.version')->showVersion($format);

            if ($this->isJson()) {
                return $this->outputJson(['version' => $result]);
            }

            $this->info($result);

            return self::SUCCESS;
        } catch (\Exception $e) {
            return $this->isJson() ? $this->errorJson($e->getMessage()) : $this->failure('Error: ' . $e->getMessage());
        }
    }
}
