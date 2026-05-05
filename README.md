# Projects Hub Laravel

Laravel connector for Projects Hub.

## Requirements

- PHP 8.2+
- Laravel 12

## Installation

Install the package with Composer:

```bash
composer require appload/projects-hub-laravel
```

Then publish the package resources:

```bash
php artisan projects-hub:install
```

The install command publishes:

- `config/projects-hub.php`
- the changelog generator package resources required by this package

## Configuration

The package is auto-discovered through Laravel, so no manual service provider registration is required.

After running `projects-hub:install`, review the published config file:

```php
config/projects-hub.php
```

### Environment variables

Add these values to your `.env` as needed:

```dotenv
PROJECTS_HUB_ENABLED=true
PROJECTS_HUB_ROUTE_PREFIX=api/projects-hub

PROJECTS_HUB_AUTH_ENABLED=false
PROJECTS_HUB_AUTH_X_API_KEY=

PROJECTS_HUB_OPENAPI_DOCS_PATH=storage/api-docs/api-docs.json
```

### Config reference

- `PROJECTS_HUB_ENABLED`: enables or disables the package routes entirely.
- `PROJECTS_HUB_ROUTE_PREFIX`: route prefix used for the package endpoints.
- `PROJECTS_HUB_AUTH_ENABLED`: enables `X-API-Key` protection for the package endpoints.
- `PROJECTS_HUB_AUTH_X_API_KEY`: expected API key when auth is enabled.
- `PROJECTS_HUB_OPENAPI_DOCS_PATH`: path to the generated OpenAPI JSON file in the host application.

## Exposed endpoint

When the package is enabled, it registers the changelog endpoint under the configured prefix:

```text
GET /api/projects-hub/changelog/json
```

If you change `PROJECTS_HUB_ROUTE_PREFIX`, the final URL changes accordingly.

The endpoint returns the contents of:

```text
resources/.changes/changelog.json
```

If the file does not exist, the endpoint responds with `404`.

## Authentication

If `PROJECTS_HUB_AUTH_ENABLED=true`, every request to the package endpoints must include:

```http
X-API-Key: your-secret-key
```

If authentication is enabled but `PROJECTS_HUB_AUTH_X_API_KEY` is empty, the package returns `500` because it is not configured correctly.

## Artisan commands

### Install command

```bash
php artisan projects-hub:install
```

Options:

- `--force`: overwrite already published files

Example:

```bash
php artisan projects-hub:install --force
```

### OpenAPI diff command

The package also provides a helper command to compare the current OpenAPI spec with a baseline from Git using `oasdiff`:

```bash
php artisan projects-hub:openapi:diff
```

Available options:

- `--ref=origin/main`: baseline Git ref
- `--mode=breaking`: diff mode, either `breaking` or `changelog`
- `--output=`: optional output file path
- `--fetch`: fetch the remote ref before diffing
- `--no-generate`: skip `l5-swagger:generate` and use the current local spec as-is

Example:

```bash
php artisan projects-hub:openapi:diff --ref=origin/main --mode=changelog --output=storage/app/openapi-changelog.md
```

Notes:

- `git` must be available in `PATH`
- `oasdiff` must be installed and available in `PATH`
- when generation is enabled, the host app must expose the `l5-swagger:generate` Artisan command

## Typical setup

1. Install the package with Composer.
2. Run `php artisan projects-hub:install`.
3. Add the required `PROJECTS_HUB_*` variables to `.env`.
4. If you want endpoint protection, enable auth and set `PROJECTS_HUB_AUTH_X_API_KEY`.
5. Make sure your application generates the OpenAPI file at `PROJECTS_HUB_OPENAPI_DOCS_PATH`.
6. Make sure `resources/.changes/changelog.json` exists if you want the changelog endpoint to return data.
