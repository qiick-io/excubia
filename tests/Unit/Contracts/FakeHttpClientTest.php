<?php

declare(strict_types=1);

use Qiick\Excubia\Exceptions\DataSourceUnavailableException;
use Qiick\Excubia\Tests\Support\FakeHttpClient;

it('returns a recorded payload and does not perform http', function () {
    $client = new FakeHttpClient([
        'https://endoflife.date/api/php.json' => ['cycle' => '8.3'],
    ]);

    expect($client->getJson('https://endoflife.date/api/php.json'))->toBe(['cycle' => '8.3'])
        ->and($client->postJson('https://endoflife.date/api/php.json', []))->toBe(['cycle' => '8.3']);
});

it('fails closed when nothing was recorded', function () {
    $client = new FakeHttpClient([]);

    expect(fn () => $client->getJson('https://example.test/missing'))
        ->toThrow(DataSourceUnavailableException::class);
});
