<?php

declare(strict_types=1);

namespace Qiick\Excubia\Data;

/**
 * Manifests and lockfiles reduced to the values the checks are allowed to see.
 *
 * Paths, file contents, and hostnames are not stored. Only versions and
 * package names leave this object toward a data source.
 */
final readonly class ProjectSnapshotData
{
    /**
     * @param  list<LockedPackageData>  $composerPackages
     * @param  list<LockedPackageData>  $npmPackages
     */
    public function __construct(
        public ?string $phpVersion,
        public ?string $laravelFrameworkVersion,
        public array $composerPackages,
        public array $npmPackages,
        public ?string $nodeVersionDeclaration,
    ) {}
}
