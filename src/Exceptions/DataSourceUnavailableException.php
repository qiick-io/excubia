<?php

declare(strict_types=1);

namespace Qiick\Excubia\Exceptions;

use RuntimeException;

/**
 * A public data source did not answer.
 *
 * API clients throw this. Checks must not let it escape `run`: the runner
 * turns it into a skipped result. `--strict` is not implemented, so this
 * exception does not become exit code 3.
 */
final class DataSourceUnavailableException extends RuntimeException
{
    public function __construct(
        public readonly string $dataSourceName,
        string $message,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
    }
}
