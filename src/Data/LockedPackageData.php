<?php

declare(strict_types=1);

namespace Qiick\Excubia\Data;

/**
 * One package pinned by Composer or by an npm lockfile.
 *
 * `version` is the string from the lockfile, not a normalized semver, so
 * later checks can show the version the project actually installed.
 */
final readonly class LockedPackageData
{
    public function __construct(
        public string $name,
        public string $version,
        public bool $isDirectDependency,
        public bool $isDevelopmentDependency,
    ) {}
}
