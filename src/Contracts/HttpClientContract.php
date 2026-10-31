<?php

declare(strict_types=1);

namespace Qiick\Excubia\Contracts;

use Qiick\Excubia\Exceptions\DataSourceUnavailableException;

/**
 * The only HTTP boundary in the package. Tests substitute a fake so no
 * test opens a socket. Timeouts, TLS, and retries belong to the
 * implementation, not to this contract.
 */
interface HttpClientContract
{
    /**
     * Fetch a JSON object.
     *
     * Sends the URL and the query parameters only. Callers must not put
     * file contents, paths, or secrets in either argument.
     *
     * @param  array<string, scalar|null>  $queryParameters
     * @return array<string, mixed>
     *
     * @throws DataSourceUnavailableException
     */
    public function getJson(string $url, array $queryParameters = []): array;

    /**
     * Post a JSON object and decode the JSON object that comes back.
     *
     * The payload may contain package names and versions only.
     *
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     *
     * @throws DataSourceUnavailableException
     */
    public function postJson(string $url, array $payload): array;
}
