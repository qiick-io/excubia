<?php

declare(strict_types=1);

namespace Qiick\Excubia\Tests\Support;

use Qiick\Excubia\Contracts\HttpClientContract;
use Qiick\Excubia\Exceptions\DataSourceUnavailableException;

/**
 * HTTP stand-in for tests. It only returns values it was given, so a test
 * that uses it cannot open a socket.
 */
final class FakeHttpClient implements HttpClientContract
{
    /**
     * @param  array<string, array<string, mixed>>  $responses
     */
    public function __construct(private readonly array $responses) {}

    public function getJson(string $url, array $queryParameters = []): array
    {
        return $this->responseFor($url);
    }

    public function postJson(string $url, array $payload): array
    {
        return $this->responseFor($url);
    }

    /**
     * @return array<string, mixed>
     */
    private function responseFor(string $url): array
    {
        if (! isset($this->responses[$url])) {
            throw new DataSourceUnavailableException('fake', 'No recorded response for '.$url.'.');
        }

        return $this->responses[$url];
    }
}
