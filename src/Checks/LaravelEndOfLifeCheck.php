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
 * Reports when the locked Laravel version is out of security fixes, or close to it.
 */
final class LaravelEndOfLifeCheck implements HealthCheckContract
{
    public function __construct(
        private readonly EndOfLifeApiService $endOfLifeApi,
        private readonly DateTimeImmutable $today,
    ) {}

    public function id(): string
    {
        return 'laravel-eol';
    }

    public function name(): string
    {
        return 'Laravel end of life';
    }

    public function run(ProjectSnapshotData $projectSnapshot, ExcubiaConfigurationData $configuration): CheckResultData
    {
        if ($projectSnapshot->laravelFrameworkVersion === null) {
            return CheckResultData::skipped($this->id(), $this->name(), 'laravel/framework', 'laravel/framework is not locked.');
        }

        $cycles = $this->endOfLifeApi->cyclesIfAvailable('laravel');

        if ($cycles === null) {
            return CheckResultData::skipped($this->id(), $this->name(), 'laravel/framework', 'endoflife.date did not respond.');
        }

        $major = $this->major($projectSnapshot->laravelFrameworkVersion);
        $cycle = $major === null ? null : $this->matchingCycle($major, $cycles);

        if ($cycle === null) {
            return CheckResultData::skipped($this->id(), $this->name(), 'laravel/framework', 'No end-of-life cycle matches the locked Laravel version.');
        }

        $today = $this->today->setTimezone(new DateTimeZone('UTC'))->setTime(0, 0);
        $securityEnds = $this->date($cycle->securitySupportEndsOn);
        $bugFixesEnd = $this->date($cycle->activeSupportEndsOn);
        $nextMajor = (string) ((int) $major + 1);
        $securityDate = $securityEnds?->format('Y-m-d') ?? 'an unknown date';
        $fixHint = 'Upgrade to Laravel '.$nextMajor.'; security fixes for your version end '.$securityDate.'.';

        if ($securityEnds !== null && $today > $securityEnds) {
            return $this->one(FindingStatusEnum::Fail, SeverityEnum::Critical, 'Security fixes ended on '.$securityDate.'.', $fixHint);
        }

        $bugFixesEnded = $bugFixesEnd !== null && $today > $bugFixesEnd;
        $securityIsClose = $securityEnds !== null && $this->daysUntil($today, $securityEnds) <= $configuration->laravelWarnDaysBeforeEndOfLife;

        if ($bugFixesEnded || $securityIsClose) {
            return $this->one(FindingStatusEnum::Warn, SeverityEnum::Medium, 'Bug fixes ended or security fixes end within '.$configuration->laravelWarnDaysBeforeEndOfLife.' days.', $fixHint);
        }

        return CheckResultData::fromFindings($this->id(), $this->name(), []);
    }

    private function one(FindingStatusEnum $status, SeverityEnum $severity, string $message, string $fixHint): CheckResultData
    {
        return CheckResultData::fromFindings($this->id(), $this->name(), [
            new FindingData($this->id(), $severity, $status, 'laravel/framework', $message, $fixHint, 'https://endoflife.date/laravel'),
        ]);
    }

    private function major(string $lockedVersion): ?string
    {
        if (preg_match('/^v?(\d+)/i', $lockedVersion, $matches) !== 1) {
            return null;
        }

        return $matches[1];
    }

    /**
     * @param  list<EndOfLifeCycleData>  $cycles
     */
    private function matchingCycle(string $major, array $cycles): ?EndOfLifeCycleData
    {
        foreach ($cycles as $cycle) {
            if ($cycle->cycle === $major) {
                return $cycle;
            }
        }

        return null;
    }

    private function date(?string $value): ?DateTimeImmutable
    {
        if ($value === null) {
            return null;
        }

        $parsed = DateTimeImmutable::createFromFormat('!Y-m-d', $value, new DateTimeZone('UTC'));

        return $parsed === false ? null : $parsed->setTime(0, 0);
    }

    private function daysUntil(DateTimeImmutable $today, DateTimeImmutable $end): int
    {
        return (int) $today->diff($end)->days;
    }
}
