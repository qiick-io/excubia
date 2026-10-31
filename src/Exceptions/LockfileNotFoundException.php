<?php

declare(strict_types=1);

namespace Qiick\Excubia\Exceptions;

use RuntimeException;

/**
 * `composer.lock` is required for every scan. Callers map this to exit code 2.
 */
final class LockfileNotFoundException extends RuntimeException
{
    public function __construct(string $projectRoot)
    {
        parent::__construct('composer.lock was not found in '.$projectRoot.'.');
    }
}
