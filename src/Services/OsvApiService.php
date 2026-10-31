<?php

declare(strict_types=1);

namespace Qiick\Excubia\Services;

use Qiick\Excubia\Contracts\HttpClientContract;
use Qiick\Excubia\Data\OsvVulnerabilityData;

/**
 * OSV batch query. One POST carries every npm package name and version.
 */
final class OsvApiService
{
    public function __construct(
        private readonly HttpClientContract $httpClient,
        private readonly ResponseCacheService $responseCache,
    ) {}

    /**
     * @param  list<array{name: string, version: string}>  $packages
     * @return list<OsvVulnerabilityData>
     */
    public function vulnerabilities(array $packages): array
    {
        $queries = [];

        foreach ($packages as $package) {
            if (preg_match('#^[A-Za-z0-9@][A-Za-z0-9@/_.+-]*$#', $package['name']) !== 1 || str_contains($package['name'], '..')) {
                continue;
            }

            if ($package['version'] === '' || str_contains($package['version'], "\n")) {
                continue;
            }

            $queries[] = [
                'package' => [
                    'name' => $package['name'],
                    'ecosystem' => 'npm',
                ],
                'version' => $package['version'],
            ];
        }

        if ($queries === []) {
            return [];
        }

        $url = 'https://api.osv.dev/v1/querybatch';
        $document = $this->responseCache->remember(
            $url.'?'.json_encode($queries, JSON_THROW_ON_ERROR),
            fn (): array => $this->httpClient->postJson($url, ['queries' => $queries]),
        );
        $results = $document['results'] ?? [];
        $vulnerabilities = [];

        if (! is_array($results)) {
            return [];
        }

        foreach ($results as $index => $result) {
            $query = $queries[$index] ?? null;

            if ($query === null || ! is_array($result) || ! is_array($result['vulns'] ?? null)) {
                continue;
            }

            foreach ($result['vulns'] as $vulnerability) {
                if (! is_array($vulnerability) || ! is_string($vulnerability['id'] ?? null)) {
                    continue;
                }

                $vulnerabilities[] = new OsvVulnerabilityData(
                    packageName: $query['package']['name'],
                    version: $query['version'],
                    id: $vulnerability['id'],
                    severity: $this->severity($vulnerability),
                );
            }
        }

        return $vulnerabilities;
    }

    /**
     * @param  array<mixed>  $vulnerability
     */
    private function severity(array $vulnerability): ?string
    {
        $scores = $vulnerability['severity'] ?? null;

        if (! is_array($scores)) {
            return null;
        }

        $first = $scores[0] ?? null;

        if (! is_array($first)) {
            return null;
        }

        $score = $first['score'] ?? null;

        return is_string($score) ? $score : null;
    }
}
