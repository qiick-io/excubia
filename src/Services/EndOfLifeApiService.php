<?php

declare(strict_types=1);

namespace Qiick\Excubia\Services;

use Qiick\Excubia\Contracts\HttpClientContract;
use Qiick\Excubia\Data\EndOfLifeCycleData;
use Qiick\Excubia\Exceptions\DataSourceUnavailableException;

/**
 * endoflife.date client. Only the product slug is sent.
 */
final class EndOfLifeApiService
{
    public function __construct(
        private readonly HttpClientContract $httpClient,
        private readonly ResponseCacheService $responseCache,
    ) {}

    /**
     * @return list<EndOfLifeCycleData>
     *
     * @throws DataSourceUnavailableException
     */
    public function cycles(string $product): array
    {
        if (! in_array($product, ['php', 'laravel', 'nodejs'], true)) {
            throw new DataSourceUnavailableException('endoflife.date', 'Unknown end-of-life product ['.$product.'].');
        }

        $url = 'https://endoflife.date/api/'.$product.'.json';
        $document = $this->responseCache->remember($url, fn (): array => $this->httpClient->getJson($url));
        $cycles = [];

        foreach ($document as $cycle) {
            if (! is_array($cycle) || ! is_string($cycle['cycle'] ?? null)) {
                continue;
            }

            $cycles[] = new EndOfLifeCycleData(
                cycle: $cycle['cycle'],
                releaseDate: is_string($cycle['releaseDate'] ?? null) ? $cycle['releaseDate'] : null,
                securitySupportEndsOn: is_string($cycle['eol'] ?? null) ? $cycle['eol'] : null,
                activeSupportEndsOn: is_string($cycle['support'] ?? null) ? $cycle['support'] : null,
                isLongTermSupport: ($cycle['lts'] ?? false) === true,
            );
        }

        return $cycles;
    }
}
