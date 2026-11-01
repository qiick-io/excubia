<?php

declare(strict_types=1);

use Qiick\Excubia\Contracts\HealthCheckContract;
use Qiick\Excubia\Data\CheckResultData;
use Qiick\Excubia\Data\ExcubiaConfigurationData;
use Qiick\Excubia\Data\ProjectSnapshotData;
use Qiick\Excubia\Exceptions\InvalidConfigurationException;
use Qiick\Excubia\Registries\HealthCheckRegistry;

function fakeCheck(string $id): HealthCheckContract
{
    return new class($id) implements HealthCheckContract
    {
        public function __construct(private readonly string $identifier) {}

        public function id(): string
        {
            return $this->identifier;
        }

        public function name(): string
        {
            return $this->identifier;
        }

        public function run(ProjectSnapshotData $projectSnapshot, ExcubiaConfigurationData $configuration): CheckResultData
        {
            return CheckResultData::fromFindings($this->identifier, $this->identifier, []);
        }
    };
}

it('filters with only and then skip', function () {
    $registry = new HealthCheckRegistry([
        fakeCheck('php-eol'),
        fakeCheck('laravel-eol'),
        fakeCheck('node-eol'),
    ]);

    $selected = $registry->healthChecksFor(['node-eol', 'php-eol'], ['php-eol']);

    expect(array_map(static fn (HealthCheckContract $check): string => $check->id(), $selected))
        ->toBe(['node-eol']);
});

it('rejects an unknown check id', function () {
    $registry = new HealthCheckRegistry([fakeCheck('php-eol')]);

    expect(fn () => $registry->healthChecksFor(['missing'], null))
        ->toThrow(InvalidConfigurationException::class, 'Unknown check id [missing].');
});
