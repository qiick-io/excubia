<?php

declare(strict_types=1);

namespace Qiick\Excubia\Data;

/**
 * One published version from Packagist metadata.
 *
 * `abandoned` is null when the package is maintained, true when Packagist
 * marked it abandoned without a replacement, and a package name when a
 * replacement was suggested.
 */
final readonly class PackagistVersionData
{
    /**
     * @param  array<string, string>  $requirements
     */
    public function __construct(
        public string $version,
        public ?string $releasedAt,
        public string|bool|null $abandoned,
        public ?string $sourceUrl,
        public array $requirements,
    ) {}
}
