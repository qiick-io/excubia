<?php

declare(strict_types=1);

use Qiick\Excubia\Data\FindingData;
use Qiick\Excubia\Data\HealthReportData;
use Qiick\Excubia\Data\ProjectSnapshotData;
use Qiick\Excubia\Enums\ExitCodeEnum;
use Qiick\Excubia\Enums\FindingStatusEnum;
use Qiick\Excubia\Enums\SeverityEnum;
use Qiick\Excubia\Services\UpgradePlanService;

it('orders security, PHP, blockers, the next Laravel major, then replacements', function () {
    $snapshot = new ProjectSnapshotData('8.2.0.0', 'v10.48.22', [], [], null);
    $findings = [
        planFinding('composer-abandoned', FindingStatusEnum::Warn, 'Replace with symfony/mailer.'),
        planFinding('laravel-eol', FindingStatusEnum::Fail, 'Security fixes for your version end 2025-02-04.'),
        planFinding('php-eol', FindingStatusEnum::Fail, 'Upgrade PHP to 8.3 (supported until 2027-12-31).'),
        planFinding('composer-advisories', FindingStatusEnum::Fail, 'Update vendor/package to ≥ fixed version (GHSA-xxxx).'),
        planFinding('laravel-next-blocker', FindingStatusEnum::Warn, 'No release supports the next major yet.'),
        planFinding('node-eol', FindingStatusEnum::Fail, 'Move to Node LTS 22.'),
        planFinding('composer-advisories', FindingStatusEnum::Pass, 'No advisories.'),
    ];

    $messages = array_map(
        static fn ($step): string => $step->stepNumber.'. '.$step->message,
        (new UpgradePlanService)->plan($snapshot, $findings),
    );

    expect($messages)->toBe([
        '1. Update vendor/package to ≥ fixed version (GHSA-xxxx).',
        '2. Upgrade PHP to 8.3 (supported until 2027-12-31).',
        '3. Upgrade to Laravel 11. No release supports the next major yet.',
        '4. Upgrade to Laravel 11. Security fixes for your version end 2025-02-04.',
        '5. Replace with symfony/mailer.',
    ]);
});

it('uses the locked major plus one rather than a fixed Laravel 13', function () {
    $service = new UpgradePlanService;

    expect($service->nextLaravelMajor('v12.40.0'))->toBe(13)
        ->and($service->nextLaravelMajor('v13.39.0'))->toBe(14)
        ->and($service->nextLaravelMajor(null))->toBeNull();
});

it('does not change the exit code', function () {
    $steps = (new UpgradePlanService)->plan(
        new ProjectSnapshotData(null, 'v12.0.0', [], [], null),
        [planFinding('php-eol', FindingStatusEnum::Fail, 'Upgrade PHP.')],
    );
    $report = new HealthReportData([], $steps);

    expect($report->exitCodeFor(SeverityEnum::High))->toBe(ExitCodeEnum::NoFindingsAtOrAboveFailOn);
});

function planFinding(string $checkId, FindingStatusEnum $status, string $fixHint): FindingData
{
    return new FindingData(
        checkId: $checkId,
        severity: SeverityEnum::High,
        status: $status,
        subject: 'subject',
        message: 'Message.',
        fixHint: $fixHint,
        evidenceUrl: null,
    );
}
