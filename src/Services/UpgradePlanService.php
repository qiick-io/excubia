<?php

declare(strict_types=1);

namespace Qiick\Excubia\Services;

use Qiick\Excubia\Data\FindingData;
use Qiick\Excubia\Data\ProjectSnapshotData;
use Qiick\Excubia\Data\UpgradeStepData;
use Qiick\Excubia\Enums\FindingStatusEnum;

/**
 * Turns findings into numbered upgrade steps.
 *
 * Order is security fixes, PHP end of life, next-major blockers, the Laravel
 * major, then stale and abandoned replacements. The next Laravel major is
 * the locked major plus one. Steps are informational and are not findings,
 * so they never change the exit code. node-eol is not in this order.
 */
final class UpgradePlanService
{
    /**
     * @param  list<FindingData>  $findings
     * @return list<UpgradeStepData>
     */
    public function plan(ProjectSnapshotData $projectSnapshot, array $findings): array
    {
        $phases = [
            ['composer-advisories', 'npm-advisories'],
            ['php-eol'],
            ['laravel-next-blocker'],
            ['laravel-eol'],
            ['composer-stale', 'composer-abandoned'],
        ];
        $laravelPhases = ['laravel-next-blocker', 'laravel-eol'];
        $nextLaravelMajor = $this->nextLaravelMajor($projectSnapshot->laravelFrameworkVersion);
        $steps = [];
        $stepNumber = 1;

        foreach ($phases as $checkIds) {
            foreach ($findings as $finding) {
                if (! in_array($finding->checkId, $checkIds, true)) {
                    continue;
                }

                if ($finding->status !== FindingStatusEnum::Fail && $finding->status !== FindingStatusEnum::Warn) {
                    continue;
                }

                $message = $finding->fixHint;

                if ($nextLaravelMajor !== null && in_array($finding->checkId, $laravelPhases, true)) {
                    $message = 'Upgrade to Laravel '.$nextLaravelMajor.'. '.$finding->fixHint;
                }

                $steps[] = new UpgradeStepData($stepNumber, trim($message));
                $stepNumber++;
            }
        }

        return $steps;
    }

    /**
     * Locked major plus one. `v10.48.22` is 11. There is no fixed target.
     */
    public function nextLaravelMajor(?string $lockedVersion): ?int
    {
        if ($lockedVersion === null) {
            return null;
        }

        if (preg_match('/^v?(\d+)\./i', $lockedVersion, $matches) !== 1) {
            return null;
        }

        return (int) $matches[1] + 1;
    }
}
