<?php

declare(strict_types=1);

namespace Qiick\Excubia\Renderers;

use Qiick\Excubia\Contracts\ReportRendererContract;
use Qiick\Excubia\Data\HealthReportData;

/**
 * JSON report for schema version 1. `advisoryId` is not emitted: the spec
 * does not say the rendered report prints that field.
 */
final class JsonReportRenderer implements ReportRendererContract
{
    public function render(HealthReportData $healthReport): string
    {
        $checks = [];

        foreach ($healthReport->checkResults as $checkResult) {
            $findings = [];

            foreach ($checkResult->findings as $finding) {
                $findings[] = [
                    'checkId' => $finding->checkId,
                    'severity' => $finding->severity->value,
                    'status' => $finding->status->value,
                    'subject' => $finding->subject,
                    'message' => $finding->message,
                    'fixHint' => $finding->fixHint,
                    'evidenceUrl' => $finding->evidenceUrl,
                ];
            }

            $checks[] = [
                'checkId' => $checkResult->checkId,
                'name' => $checkResult->name,
                'status' => $checkResult->status->value,
                'findings' => $findings,
            ];
        }

        $upgradePlan = [];

        foreach ($healthReport->upgradeSteps as $upgradeStep) {
            $upgradePlan[] = [
                'stepNumber' => $upgradeStep->stepNumber,
                'message' => $upgradeStep->message,
            ];
        }

        return json_encode([
            'schemaVersion' => $healthReport->schemaVersion,
            'checks' => $checks,
            'upgradePlan' => $upgradePlan,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n";
    }
}
