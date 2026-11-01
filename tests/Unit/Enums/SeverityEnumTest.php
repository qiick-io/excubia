<?php

declare(strict_types=1);

use Qiick\Excubia\Enums\SeverityEnum;

it('ranks critical above high, medium, low, and info', function () {
    expect(SeverityEnum::Critical->isAtLeastAsSevereAs(SeverityEnum::High))->toBeTrue()
        ->and(SeverityEnum::High->isAtLeastAsSevereAs(SeverityEnum::High))->toBeTrue()
        ->and(SeverityEnum::Medium->isAtLeastAsSevereAs(SeverityEnum::High))->toBeFalse()
        ->and(SeverityEnum::Low->isAtLeastAsSevereAs(SeverityEnum::Medium))->toBeFalse()
        ->and(SeverityEnum::Info->isAtLeastAsSevereAs(SeverityEnum::Low))->toBeFalse();

    expect(SeverityEnum::Critical->weight())
        ->toBeGreaterThan(SeverityEnum::High->weight())
        ->toBeGreaterThan(SeverityEnum::Medium->weight())
        ->toBeGreaterThan(SeverityEnum::Low->weight())
        ->toBeGreaterThan(SeverityEnum::Info->weight());
});
