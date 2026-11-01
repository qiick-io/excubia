<?php

declare(strict_types=1);

namespace Qiick\Excubia\Enums;

/**
 * Outcome of one finding. Pass and skipped never change the exit code.
 * Warn and fail do, once their severity reaches the configured threshold.
 */
enum FindingStatusEnum: string
{
    case Pass = 'pass';
    case Warn = 'warn';
    case Fail = 'fail';
    case Skipped = 'skipped';
}
