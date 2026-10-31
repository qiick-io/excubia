<?php

declare(strict_types=1);

use Qiick\Excubia\Exceptions\InvalidConfigurationException;
use Qiick\Excubia\Parsers\NodeVersionParser;
use Qiick\Excubia\Parsers\PackageLockParser;

it('prefers nvmrc, then node-version, then engines.node', function () {
    $parser = new NodeVersionParser;

    expect($parser->declarationFrom("22\n", '20', '>=18'))->toBe('22')
        ->and($parser->declarationFrom(" \n", '20.11.0', '>=18'))->toBe('20.11.0')
        ->and($parser->declarationFrom(null, null, '>=20'))->toBe('>=20')
        ->and($parser->declarationFrom(null, null, null))->toBeNull();
});

it('reads lockfileVersion 2 and 3 and rejects anything else', function () {
    $parser = new PackageLockParser;
    $packages = [
        '' => ['name' => 'fixture'],
        'node_modules/left-pad' => ['version' => '1.0.0'],
    ];

    expect($parser->parse(['lockfileVersion' => 2, 'packages' => $packages], []))->toHaveCount(1)
        ->and($parser->parse(['lockfileVersion' => 3, 'packages' => $packages], ['left-pad'])[0]->isDirectDependency)->toBeTrue();

    expect(fn () => $parser->parse(['lockfileVersion' => 1, 'packages' => $packages], []))
        ->toThrow(InvalidConfigurationException::class);
});
