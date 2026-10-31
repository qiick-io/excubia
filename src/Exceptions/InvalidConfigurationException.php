<?php

declare(strict_types=1);

namespace Qiick\Excubia\Exceptions;

use RuntimeException;
use Throwable;

/**
 * The project file or the tool configuration cannot be used.
 *
 * The exception code is exit code 2. A broken or unparseable composer.lock
 * uses this exception so the process can exit 2 with the message.
 */
final class InvalidConfigurationException extends RuntimeException
{
    public function __construct(string $message, ?Throwable $previous = null)
    {
        parent::__construct($message, 2, $previous);
    }
}
