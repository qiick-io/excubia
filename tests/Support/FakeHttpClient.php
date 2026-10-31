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
    public int $calls = 0;

    /** @var array<string, string> */
    public array $lastHeaders = [];

    /** @var array<string, scalar|list<scalar>|null> */
    public array $lastQuery = [];

    /**
     * @param  array<string, array<int|string, mixed>>  $responses
     */
    public function __construct(private readonly array $responses) {}

    public function getJson(string $url, array $queryParameters = [], array $headers = []): array
    {
        $this->calls++;
        $this->lastHeaders = $headers;
        $this->lastQuery = $queryParameters;

        return $this->responseFor($url);
    }

    public function postJson(string $url, array $payload, array $headers = []): array
    {
        $this->calls++;
        $this->lastHeaders = $headers;

        return $this->responseFor($url);
    }

    /**
     * @return array<int|string, mixed>
     */
    private function responseFor(string $url): array
    {
        if (! isset($this->responses[$url])) {
            throw new DataSourceUnavailableException('fake', 'No recorded response for '.$url.'.');
        }

        return $this->responses[$url];
    }
}
