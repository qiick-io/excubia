<?php

declare(strict_types=1);

arch('uses strict types')
    ->expect('Qiick\Excubia')
    ->toUseStrictTypes();

arch('does not call debug functions or env')
    ->expect('Qiick\Excubia')
    ->not->toUse(['dd', 'dump', 'var_dump', 'env']);

arch('data objects are readonly and suffixed')
    ->expect('Qiick\Excubia\Data')
    ->toBeReadonly()
    ->toHaveSuffix('Data');

arch('enums use the enum suffix')
    ->expect('Qiick\Excubia\Enums')
    ->toBeEnums()
    ->toHaveSuffix('Enum');

arch('contracts are interfaces')
    ->expect('Qiick\Excubia\Contracts')
    ->toBeInterfaces()
    ->toHaveSuffix('Contract');

arch('parsers use the parser suffix')
    ->expect('Qiick\Excubia\Parsers')
    ->toHaveSuffix('Parser');

arch('services use the service suffix')
    ->expect('Qiick\Excubia\Services')
    ->toHaveSuffix('Service');

arch('registries use the registry suffix')
    ->expect('Qiick\Excubia\Registries')
    ->toHaveSuffix('Registry');

arch('exceptions use the exception suffix')
    ->expect('Qiick\Excubia\Exceptions')
    ->toHaveSuffix('Exception');

it('restricts health check dependencies once checks exist', function () {
    $checkDirectory = dirname(__DIR__, 2).'/src/Checks';

    if (! is_dir($checkDirectory)) {
        expect(is_dir($checkDirectory))->toBeFalse();

        return;
    }

    expect('Qiick\Excubia\Checks')
        ->toHaveSuffix('Check')
        ->toOnlyUse([
            'Qiick\Excubia\Contracts',
            'Qiick\Excubia\Data',
            'Qiick\Excubia\Enums',
            'Qiick\Excubia\Services',
        ]);
});
