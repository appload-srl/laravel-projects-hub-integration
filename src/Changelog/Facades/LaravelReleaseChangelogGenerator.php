<?php

namespace Appload\ProjectsHub\Changelog\Facades;

use Illuminate\Support\Facades\Facade as LaravelFacade;

class LaravelReleaseChangelogGenerator extends LaravelFacade
{
    /** @psalm-pure */
    #[\Override]
    protected static function getFacadeAccessor(): string
    {
        return 'releasechangelog';
    }
}
