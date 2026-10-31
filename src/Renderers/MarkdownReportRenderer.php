<?php

declare(strict_types=1);

namespace Qiick\Excubia\Renderers;

use Qiick\Excubia\Contracts\ReportRendererContract;
use Qiick\Excubia\Data\HealthReportData;
use Qiick\Excubia\Enums\FindingStatusEnum;
use Qiick\Excubia\Enums\SeverityEnum;

/**
 * Markdown report for agents. Same groups as the table, without colour.
 */
final class MarkdownReportRenderer implements ReportRendererContract
{
    public function render(HealthReportData $healthReport): string
    {
        $lines = [
            '# qiick Excubia',
            '',
            $healthReport->summaryLine(),
            '',
        ];

        foreach ([SeverityEnum::Critical, SeverityEnum::High, SeverityEnum::Medium, SeverityEnum::Low] as $severity) {
            $wroteHeading = false;

            foreach ($healthReport->findings() as $finding) {
                if ($finding->severity !== $severity) {
                    continue;
                }

                if ($finding->status !== FindingStatusEnum::Fail && $finding->status !== FindingStatusEnum::Warn) {
                    continue;
                }

                if (! $wroteHeading) {
                    $lines[] = '## '.ucfirst($severity->value);
                    $lines[] = '';
                    $wroteHeading = true;
                }

                $lines[] = '- **'.$finding->subject.'** — '.$finding->message;
                $lines[] = '  - '.$finding->fixHint;
            }

            if ($wroteHeading) {
                $lines[] = '';
            }
        }

        $lines[] = '## Upgrade plan';
        $lines[] = '';

        if ($healthReport->upgradeSteps === []) {
            $lines[] = 'None.';
        }

        foreach ($healthReport->upgradeSteps as $upgradeStep) {
            $lines[] = $upgradeStep->stepNumber.'. '.$upgradeStep->message;
        }

        return implode("\n", $lines)."\n";
    }
}
