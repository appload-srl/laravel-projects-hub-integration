# Projects Hub Laravel

Laravel connector for Projects Hub.

## Requirements

- PHP 8.2+
- Laravel 12

## Installation

This package is distributed from a private GitHub repository.

> Note: GitHub authentication may be required only when installing from a private repository.

### 1. Configure the repository in Composer

Add the VCS repository to the host project:

```bash
composer config repositories.laravel-projects-hub-integration \
'{"type":"vcs","url":"https://github.com/appload-srl/laravel-projects-hub-integration.git","no-api":true}'
```

This command writes the custom repository definition into the project's `composer.json`, so Composer knows where to resolve `appload/laravel-projects-hub-integration`.

### 2. Require the package

Install the package with Composer:

```bash
composer require appload/laravel-projects-hub-integration
```

Then publish the package resources:

```bash
php artisan projects-hub:install
```

The install command publishes:

- `config/projects-hub.php`
- the changelog generator package resources required by this package
- GitHub Actions workflow stubs under `.github/workflows`

## Configuration

The package is auto-discovered through Laravel, so no manual service provider registration is required.

After running `projects-hub:install`, review the published config file:

```php
config/projects-hub.php
```

### Environment variables

The package works with sensible defaults. Add these values to your `.env` only when you need to override the defaults:

```dotenv
# Default: true
PROJECTS_HUB_ENABLED=true

# Default: api/projects-hub
PROJECTS_HUB_ROUTE_PREFIX=api/projects-hub

# Default: false
PROJECTS_HUB_AUTH_ENABLED=false

# No default. Required only when PROJECTS_HUB_AUTH_ENABLED=true.
PROJECTS_HUB_AUTH_X_API_KEY=

# Default: storage/api-docs/api-docs.json
PROJECTS_HUB_OPENAPI_DOCS_PATH=storage/api-docs/api-docs.json
```

### Config reference

- `PROJECTS_HUB_ENABLED`: enables or disables the package routes entirely. Defaults to `true`.
- `PROJECTS_HUB_ROUTE_PREFIX`: route prefix used for the package endpoints. Defaults to `api/projects-hub`.
- `PROJECTS_HUB_AUTH_ENABLED`: enables `X-API-Key` protection for the package endpoints. Defaults to `false`.
- `PROJECTS_HUB_AUTH_X_API_KEY`: expected API key when auth is enabled. It has no default and is required only when `PROJECTS_HUB_AUTH_ENABLED=true`.
- `PROJECTS_HUB_OPENAPI_DOCS_PATH`: path to the generated OpenAPI JSON file in the host application. Defaults to `storage/api-docs/api-docs.json`.

### GitHub Actions secrets

The published GitHub Actions workflows require these repository secrets to be configured and injected into CI/CD:

- `PROJECTS_HUB_API_URL`: Projects Hub base URL, without requiring the upload path
- `PROJECTS_HUB_API_KEY`: API key sent as `X-Api-Key`

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

After publishing, review the workflow stubs before committing them:

- `on-sync-dev-api-diff.yml`
- `on-sync-staging-api-diff.yml`
- `on-sync-prod-api-diff.yml`
- `openapi-diff-on-tag.yml`

The reusable workflow is intentionally generic on:

- OpenAPI spec path, overridable through the `PROJECTS_HUB_OPENAPI_DOCS_PATH` repository variable
- Projects Hub upload path, overridable through the `PROJECTS_HUB_API_SPEC_PATH` repository variable
- release tag matching, overridable through the `PROJECTS_HUB_VERSION_TAG_PATTERN` repository variable

The branch names remain explicit defaults (`develop`, `staging`, `main`) because deployment flows differ across projects and should be adjusted in the host repository when needed.

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

### Changelog commands

The package publishes and registers the changelog resources used by the release commands:

- `resources/.changes/changelog.json`
- `resources/.version/version.yml`

Add an unreleased changelog entry:

```bash
php artisan changelog:add --type=feat --message="Add customer export"
```

Optional metadata can be attached to an entry:

```bash
php artisan changelog:add \
  --type=fix \
  --module=billing \
  --issue=123 \
  --message="Fix invoice total rounding"
```

Inspect changelog entries:

```bash
php artisan changelog:show
php artisan changelog:show --ver=1.2.3
php artisan changelog:list
```

Check or update the current version:

```bash
php artisan changelog:show-version
php artisan changelog:update-version --type=patch
```

Suggest the next release type from unreleased entries:

```bash
php artisan changelog:suggest-release
```

Create a release from the current unreleased entries:

```bash
php artisan changelog:release --type=minor --releasename="Customer exports"
```

Allowed release types are:

- `patch`
- `minor`
- `major`
- `rc`
- `timestamp`

If the version must be set explicitly instead of incremented, use:

```bash
php artisan changelog:set-release --versionnumber=1.4.0 --releasename="Customer exports"
```

Generate or refresh the root `CHANGELOG.md` file:

```bash
php artisan changelog:generate-md
```

All changelog commands also support JSON output:

```bash
php artisan changelog:suggest-release --json
```

### Release flow

A typical release flow in a host project is:

1. Add changelog entries during development with `changelog:add`.
2. Before releasing, run `changelog:suggest-release`.
3. Create the release changelog with `changelog:release --type=patch|minor|major --releasename="..."`.
4. Generate `CHANGELOG.md` with `changelog:generate-md`.
5. Commit the updated changelog and version files.
6. Push the release commit.
7. Create the Git tag and the GitHub Release from the GitHub interface.

Example:

```bash
php artisan changelog:suggest-release
php artisan changelog:release --type=minor --releasename="Customer exports"
php artisan changelog:generate-md
git add resources/.changes/changelog.json resources/.version/version.yml CHANGELOG.md
git commit -m "Release v1.4.0"
git push
```

After the commit is on GitHub, create a new release from the GitHub interface and use a tag matching the configured workflow pattern, for example `v1.4.0`.

When the published GitHub Actions workflow runs on the tagged commit, it uploads the current OpenAPI spec with `versionTag` set to that tag, generates the OpenAPI diff against the previous matching tag, and attaches the diff to the GitHub Release.

## Typical setup

1. Install the package with Composer.
2. Run `php artisan projects-hub:install`.
3. Add the required `PROJECTS_HUB_*` variables to `.env`.
4. If you want endpoint protection, enable auth and set `PROJECTS_HUB_AUTH_X_API_KEY`.
5. Make sure your application generates the OpenAPI file at `PROJECTS_HUB_OPENAPI_DOCS_PATH`.
6. Make sure `resources/.changes/changelog.json` exists if you want the changelog endpoint to return data.
