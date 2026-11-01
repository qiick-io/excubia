<?php

declare(strict_types=1);

namespace Qiick\Excubia\Laravel;

use Illuminate\Support\ServiceProvider;

/**
 * Registers `php artisan excubia:check` when this package is installed in
 * a Laravel app. The provider does not boot the scanned project.
 */
final class ExcubiaServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->commands([
            ExcubiaCheckCommand::class,
        ]);
    }
}
