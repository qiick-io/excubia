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
 * Reports when the declared Node version is end of life, or close to it.
 *
 * The declaration is already chosen as `.nvmrc`, then `.node-version`, then
 * `package.json` `engines.node`. This check does not add another rule for
 * disagreements. 180 days is the warn window. The config file only has an
 * enabled flag for this check.
 */
final class NodeEndOfLifeCheck implements HealthCheckContract
{
    private const WARN_DAYS = 180;

    public function __construct(
        private readonly EndOfLifeApiService $endOfLifeApi,
        private readonly DateTimeImmutable $today,
    ) {}

    public function id(): string
    {
        return 'node-eol';
    }

    public function name(): string
    {
        return 'Node end of life';
    }

    public function run(ProjectSnapshotData $projectSnapshot, ExcubiaConfigurationData $configuration): CheckResultData
    {
        if (! $configuration->isNodeEndOfLifeCheckEnabled) {
            return CheckResultData::skipped($this->id(), $this->name(), 'node', 'The node-eol check is disabled.');
        }

        $declaration = $projectSnapshot->nodeVersionDeclaration;

        if ($declaration === null || trim($declaration) === '') {
            return CheckResultData::skipped($this->id(), $this->name(), 'node', 'Node version is not declared.');
        }

        $major = $this->major($declaration);

        if ($major === null) {
            return CheckResultData::skipped($this->id(), $this->name(), 'node', 'The declared Node version could not be read as a major version.');
        }

        $cycles = $this->endOfLifeApi->cyclesIfAvailable('nodejs');

        if ($cycles === null) {
            return CheckResultData::skipped($this->id(), $this->name(), 'node', 'endoflife.date did not respond.');
        }

        $cycle = null;

        foreach ($cycles as $candidate) {
            if ($candidate->cycle === $major) {
                $cycle = $candidate;
                break;
            }
        }

        if ($cycle === null) {
            return CheckResultData::skipped($this->id(), $this->name(), 'node', 'No end-of-life cycle matches the declared Node version.');
        }

        $today = $this->today->setTimezone(new DateTimeZone('UTC'))->setTime(0, 0);
        $ends = $this->date($cycle->securitySupportEndsOn);
        $fixHint = 'Move to Node LTS '.$this->recommendedLts($cycles, $today).'.';

        if ($ends !== null && $today > $ends) {
            return $this->one(FindingStatusEnum::Fail, SeverityEnum::High, 'Node '.$major.' is end of life.', $fixHint);
        }

        if ($ends !== null && (int) $today->diff($ends)->days <= self::WARN_DAYS) {
            return $this->one(FindingStatusEnum::Warn, SeverityEnum::Low, 'Maintenance for Node '.$major.' ends on '.$ends->format('Y-m-d').'.', $fixHint);
        }

        return CheckResultData::fromFindings($this->id(), $this->name(), []);
    }

    private function one(FindingStatusEnum $status, SeverityEnum $severity, string $message, string $fixHint): CheckResultData
    {
        return CheckResultData::fromFindings($this->id(), $this->name(), [
            new FindingData($this->id(), $severity, $status, 'node', $message, $fixHint, 'https://endoflife.date/nodejs'),
        ]);
    }

    private function major(string $declaration): ?string
    {
        if (preg_match('/(\d+)/', $declaration, $matches) !== 1) {
            return null;
        }

        return $matches[1];
    }

    /**
     * @param  list<EndOfLifeCycleData>  $cycles
     */
    private function recommendedLts(array $cycles, DateTimeImmutable $today): string
    {
        $recommended = null;

        foreach ($cycles as $cycle) {
            if (! $cycle->isLongTermSupport) {
                continue;
            }

            $ends = $this->date($cycle->securitySupportEndsOn);

            if ($ends !== null && $today > $ends) {
                continue;
            }

            if ($recommended === null || version_compare($cycle->cycle, $recommended, '>')) {
                $recommended = $cycle->cycle;
            }
        }

        return $recommended ?? 'a current LTS';
    }

    private function date(?string $value): ?DateTimeImmutable
    {
        if ($value === null) {
            return null;
        }

        $parsed = DateTimeImmutable::createFromFormat('!Y-m-d', $value, new DateTimeZone('UTC'));

        return $parsed === false ? null : $parsed->setTime(0, 0);
    }
}
