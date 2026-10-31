<?php

declare(strict_types=1);

namespace Qiick\Excubia\Data;

/**
 * One Packagist security advisory. `affectedVersions` is a Composer constraint.
 */
final readonly class ComposerAdvisoryData
{
    public function __construct(
        public string $advisoryId,
        public string $packageName,
        public string $affectedVersions,
        public ?string $severity,
        public ?string $link,
    ) {}
}
