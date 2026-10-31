<?php

declare(strict_types=1);

namespace Qiick\Excubia\Renderers;

use Qiick\Excubia\Contracts\ReportRendererContract;
use Qiick\Excubia\Data\FindingData;
use Qiick\Excubia\Data\HealthReportData;
use Qiick\Excubia\Enums\FindingStatusEnum;
use Qiick\Excubia\Enums\SeverityEnum;

/**
 * Human table. Colour is the caller's choice: pass false when stdout is not
 * a TTY or NO_COLOR is set. Fail and warn rows are grouped by severity.
 * The upgrade plan is last.
 */
final class TableReportRenderer implements ReportRendererContract
{
    public function __construct(private readonly bool $isColorEnabled = false) {}

    public function render(HealthReportData $healthReport): string
    {
        $lines = [$healthReport->summaryLine(), ''];

        foreach ([SeverityEnum::Critical, SeverityEnum::High, SeverityEnum::Medium, SeverityEnum::Low] as $severity) {
            $findings = $this->findingsFor($healthReport, $severity);

            if ($findings === []) {
                continue;
            }

            $lines[] = $this->paint($severity, ucfirst($severity->value));
            $lines[] = '';

            foreach ($findings as $finding) {
                $lines[] = $finding->subject;
                $lines[] = '  '.$finding->message;
                $lines[] = '  '.$finding->fixHint;
                $lines[] = '';
            }
        }

        $lines[] = 'Upgrade plan';

        if ($healthReport->upgradeSteps === []) {
            $lines[] = 'None.';
        }

        foreach ($healthReport->upgradeSteps as $upgradeStep) {
            $lines[] = $upgradeStep->stepNumber.'. '.$upgradeStep->message;
        }

        return implode("\n", $lines)."\n";
    }

    /**
     * @return list<FindingData>
     */
    private function findingsFor(HealthReportData $healthReport, SeverityEnum $severity): array
    {
        $findings = [];

        foreach ($healthReport->findings() as $finding) {
            if ($finding->severity !== $severity) {
                continue;
            }

            if ($finding->status !== FindingStatusEnum::Fail && $finding->status !== FindingStatusEnum::Warn) {
                continue;
            }

            $findings[] = $finding;
        }

        return $findings;
    }

    private function paint(SeverityEnum $severity, string $label): string
    {
        if (! $this->isColorEnabled) {
            return $label;
        }

        $code = match ($severity) {
            SeverityEnum::Critical => '31',
            SeverityEnum::High => '33',
            SeverityEnum::Medium => '36',
            SeverityEnum::Low => '37',
            SeverityEnum::Info => '37',
        };

        return "\033[".$code.'m'.$label."\033[0m";
    }
}
