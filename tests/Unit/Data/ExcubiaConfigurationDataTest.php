<?php

declare(strict_types=1);

use Qiick\Excubia\Data\ExcubiaConfigurationData;
use Qiick\Excubia\Enums\SeverityEnum;
use Qiick\Excubia\Exceptions\InvalidConfigurationException;

it('ships the spec defaults', function () {
    $configuration = ExcubiaConfigurationData::defaults();

    expect($configuration->failOnSeverity)->toBe(SeverityEnum::High)
        ->and($configuration->isDevelopmentDependencyIncluded)->toBeTrue()
        ->and($configuration->staleAfterMonths)->toBe(24)
        ->and($configuration->phpWarnDaysBeforeEndOfLife)->toBe(180)
        ->and($configuration->laravelWarnDaysBeforeEndOfLife)->toBe(180)
        ->and($configuration->isNodeEndOfLifeCheckEnabled)->toBeTrue()
        ->and($configuration->ignoreRules)->toBe([])
        ->and($configuration->cacheDirectory)->toBeNull()
        ->and($configuration->cacheTtlHours)->toBe(24);
});

it('rejects an ignore rule without a reason', function () {
    expect(fn () => new ExcubiaConfigurationData(
        failOnSeverity: SeverityEnum::High,
        isDevelopmentDependencyIncluded: true,
        staleAfterMonths: 24,
        phpWarnDaysBeforeEndOfLife: 180,
        laravelWarnDaysBeforeEndOfLife: 180,
        isNodeEndOfLifeCheckEnabled: true,
        ignoreRules: [[
            'checkId' => 'php-eol',
            'subject' => 'php',
            'reason' => '',
        ]],
        cacheDirectory: null,
        cacheTtlHours: 24,
    ))->toThrow(InvalidConfigurationException::class);
});

it('rejects an unknown ignore rule key', function () {
    expect(fn () => new ExcubiaConfigurationData(
        failOnSeverity: SeverityEnum::High,
        isDevelopmentDependencyIncluded: true,
        staleAfterMonths: 24,
        phpWarnDaysBeforeEndOfLife: 180,
        laravelWarnDaysBeforeEndOfLife: 180,
        isNodeEndOfLifeCheckEnabled: true,
        ignoreRules: [[
            'checkId' => 'php-eol',
            'subject' => 'php',
            'reason' => 'Temporary',
            'note' => 'nope',
        ]],
        cacheDirectory: null,
        cacheTtlHours: 24,
    ))->toThrow(InvalidConfigurationException::class, 'Unknown ignore rule key [note].');
});
