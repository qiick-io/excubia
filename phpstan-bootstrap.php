<?php

declare(strict_types=1);

use Composer\InstalledVersions;

/*
 * Larastan reads LARAVEL_VERSION while loading stubs. This package is not a
 * Laravel application, so the constant is taken from the illuminate/support
 * version already installed for Larastan.
 */
if (! defined('LARAVEL_VERSION')) {
    $installedVersion = InstalledVersions::getPrettyVersion('illuminate/support') ?? '11.44.2';

    define('LARAVEL_VERSION', ltrim($installedVersion, 'v'));
}
