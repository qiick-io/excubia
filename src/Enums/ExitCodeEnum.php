<?php

declare(strict_types=1);

namespace Qiick\Excubia\Enums;

/**
 * Process status for `vendor/bin/excubia`. Data-source failure (3) exists
 * because the architecture names it. v0.1.0 does not return it: `--strict`
 * is still a proposal, and a data-source miss becomes a skipped check.
 */
enum ExitCodeEnum: int
{
    case NoFindingsAtOrAboveFailOn = 0;
    case FindingsAtOrAboveFailOn = 1;
    case UsageOrConfigurationError = 2;
    case DataSourceFailure = 3;
}
