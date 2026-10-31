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
     * Fetch JSON. The body may be an object or a list.
     *
     * Headers exist so a GitHub token can be sent without putting it in
     * the URL or the cache key. Callers must not put file contents or
     * project paths in the URL, the query, or the headers.
     *
     * @param  array<string, scalar|list<scalar>|null>  $queryParameters
     * @param  array<string, string>  $headers
     * @return array<int|string, mixed>
     *
     * @throws DataSourceUnavailableException
     */
    public function getJson(string $url, array $queryParameters = [], array $headers = []): array;

    /**
     * Post JSON and decode the JSON that comes back.
     *
     * The payload may contain package names and versions only.
     *
     * @param  array<string, mixed>  $payload
     * @param  array<string, string>  $headers
     * @return array<int|string, mixed>
     *
     * @throws DataSourceUnavailableException
     */
    public function postJson(string $url, array $payload, array $headers = []): array;
}
