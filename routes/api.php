<?php

use Appload\ProjectsHub\Http\Controllers\ChangelogController;
use Appload\ProjectsHub\Http\Middleware\EnsureProjectsHubToken;
use Illuminate\Support\Facades\Route;

Route::middleware([
    EnsureProjectsHubToken::class,
])->group(function () {
    Route::get('/changelog/json', [ChangelogController::class, 'getChangelogJson']);
});