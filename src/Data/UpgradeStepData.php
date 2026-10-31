<?php

declare(strict_types=1);

namespace Qiick\Excubia\Data;

/**
 * One numbered step of the upgrade plan.
 *
 * The plan is informational. It is not a finding and it never changes the
 * exit code.
 */
final readonly class UpgradeStepData
{
    public function __construct(
        public int $stepNumber,
        public string $message,
    ) {}
}
