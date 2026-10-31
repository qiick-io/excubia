<?php

declare(strict_types=1);

namespace Qiick\Excubia\Exceptions;

use RuntimeException;

/**
 * The project file or the tool configuration cannot be used.
 *
 * Callers map this to exit code 2. It is also raised for a lockfile that
 * is present but not valid JSON, because the scan cannot continue.
 */
final class InvalidConfigurationException extends RuntimeException {}
