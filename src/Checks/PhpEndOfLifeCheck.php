<?php

declare(strict_types=1);

namespace Qiick\Excubia\Checks;

use DateTimeImmutable;
use DateTimeZone;
use Qiick\Excubia\Contracts\HealthCheckContract;
use Qiick\Excubia\Data\CheckResultData;
use Qiick\Excubia\Data\EndOfLifeCycleData;
use Qiick\Excubia\Data\ExcubiaConfigurationData;
use Qiick\Excubia\Data\FindingData;
use Qiick\Excubia\Data\ProjectSnapshotData;
use Qiick\Excubia\Enums\FindingStatusEnum;
use Qiick\Excubia\Enums\SeverityEnum;
use Qiick\Excubia\Services\EndOfLifeApiService;

/**
 * Reports when the resolved PHP version is out of security support, or close to it.
 */
final class PhpEndOfLifeCheck implements HealthCheckContract
{
    public function __construct(
        private readonly EndOfLifeApiService $endOfLifeApi,
        private readonly DateTimeImmutable $today,
    ) {}

    public function id(): string
    {
        return 'php-eol';
    }

    public function name(): string
    {
        return 'PHP end of life';
    }

    public function run(ProjectSnapshotData $projectSnapshot, ExcubiaConfigurationData $configuration): CheckResultData
    {
        if ($projectSnapshot->phpVersion === null) {
            return CheckResultData::skipped($this->id(), $this->name(), 'php', 'PHP version is not declared.');
        }

        $cycles = $this->endOfLifeApi->cyclesIfAvailable('php');

        if ($cycles === null) {
            return CheckResultData::skipped($this->id(), $this->name(), 'php', 'endoflife.date did not respond.');
        }

        $cycle = $this->matchingCycle($projectSnapshot->phpVersion, $cycles);

        if ($cycle === null) {
            return CheckResultData::skipped($this->id(), $this->name(), 'php', 'No end-of-life cycle matches the resolved PHP version.');
        }

        $today = $this->today->setTimezone(new DateTimeZone('UTC'))->setTime(0, 0);
        $securityEnds = $this->date($cycle->securitySupportEndsOn);
        $activeEnds = $this->date($cycle->activeSupportEndsOn);

        if ($securityEnds !== null && $today > $securityEnds) {
            return $this->failed($cycle, $cycles, $today);
        }

        $securityIsClose = $securityEnds !== null && $this->daysUntil($today, $securityEnds) <= $configuration->phpWarnDaysBeforeEndOfLife;
        $activeHasEnded = $activeEnds !== null && $today > $activeEnds;

        if ($activeHasEnded && $securityIsClose) {
            return CheckResultData::fromFindings($this->id(), $this->name(), [
                $this->finding(
                    FindingStatusEnum::Warn,
                    SeverityEnum::Medium,
                    'Active support ended. Security support ends '.$securityEnds->format('Y-m-d').'.',
                    $this->fixHint($cycles, $today),
                ),
            ]);
        }

        return CheckResultData::fromFindings($this->id(), $this->name(), []);
    }

    /**
     * @param  list<EndOfLifeCycleData>  $cycles
     */
    private function failed(EndOfLifeCycleData $cycle, array $cycles, DateTimeImmutable $today): CheckResultData
    {
        $endedOn = $cycle->securitySupportEndsOn ?? 'an unknown date';

        return CheckResultData::fromFindings($this->id(), $this->name(), [
            $this->finding(
                FindingStatusEnum::Fail,
                SeverityEnum::Critical,
                'Security support ended on '.$endedOn.'.',
                $this->fixHint($cycles, $today),
            ),
        ]);
    }

    private function finding(FindingStatusEnum $status, SeverityEnum $severity, string $message, string $fixHint): FindingData
    {
        return new FindingData(
            checkId: $this->id(),
            severity: $severity,
            status: $status,
            subject: 'php',
            message: $message,
            fixHint: $fixHint,
            evidenceUrl: 'https://endoflife.date/php',
        );
    }

    /**
     * @param  list<EndOfLifeCycleData>  $cycles
     */
    private function matchingCycle(string $phpVersion, array $cycles): ?EndOfLifeCycleData
    {
        if (preg_match('/^v?(\d+)\.(\d+)/i', $phpVersion, $matches) !== 1) {
            return null;
        }

        $cycleName = $matches[1].'.'.$matches[2];

        foreach ($cycles as $cycle) {
            if ($cycle->cycle === $cycleName) {
                return $cycle;
            }
        }

        return null;
    }

    /**
     * @param  list<EndOfLifeCycleData>  $cycles
     */
    private function fixHint(array $cycles, DateTimeImmutable $today): string
    {
        $recommended = null;

        foreach ($cycles as $cycle) {
            $securityEnds = $this->date($cycle->securitySupportEndsOn);

            if ($securityEnds !== null && $today > $securityEnds) {
                continue;
            }

            if ($recommended === null || version_compare($cycle->cycle, $recommended->cycle, '>')) {
                $recommended = $cycle;
            }
        }

        if ($recommended === null) {
            return 'Upgrade PHP to a supported release.';
        }

        $until = $recommended->securitySupportEndsOn ?? 'a later date';

        return 'Upgrade PHP to '.$recommended->cycle.' (supported until '.$until.').';
    }

    private function date(?string $value): ?DateTimeImmutable
    {
        if ($value === null) {
            return null;
        }

        $parsed = DateTimeImmutable::createFromFormat('!Y-m-d', $value, new DateTimeZone('UTC'));

        if ($parsed === false) {
            return null;
        }

        return $parsed->setTime(0, 0);
    }

    private function daysUntil(DateTimeImmutable $today, DateTimeImmutable $end): int
    {
        return (int) $today->diff($end)->days;
    }
}
