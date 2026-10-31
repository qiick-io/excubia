<?php

declare(strict_types=1);

namespace Qiick\Excubia\Parsers;

use Qiick\Excubia\Data\LockedPackageData;
use Qiick\Excubia\Exceptions\InvalidConfigurationException;

/**
 * Reads `composer.lock` packages and the locked platform PHP version.
 *
 * Packages from `packages-dev` are marked as development dependencies.
 * Direct versus transitive is decided by the caller, which knows
 * `composer.json`.
 */
final class ComposerLockParser
{
    /**
     * @param  array<string, mixed>  $document
     * @param  list<string>  $directPackageNames
     * @return array{packages: list<LockedPackageData>, platformPhpVersion: string|null}
     */
    public function parse(array $document, array $directPackageNames): array
    {
        $direct = array_fill_keys($directPackageNames, true);

        return [
            'packages' => [
                ...$this->lockedPackages($document['packages'] ?? [], false, $direct),
                ...$this->lockedPackages($document['packages-dev'] ?? [], true, $direct),
            ],
            'platformPhpVersion' => $this->platformPhpVersion($document),
        ];
    }

    /**
     * @param  array<string, bool>  $directPackageNames
     * @return list<LockedPackageData>
     */
    private function lockedPackages(mixed $packages, bool $isDevelopmentDependency, array $directPackageNames): array
    {
        if ($packages === []) {
            return [];
        }

        if (! is_array($packages) || ! array_is_list($packages)) {
            throw new InvalidConfigurationException('composer.lock packages must be a list.');
        }

        $lockedPackages = [];

        foreach ($packages as $package) {
            if (! is_array($package)) {
                throw new InvalidConfigurationException('composer.lock package entries must be objects.');
            }

            $name = $package['name'] ?? null;
            $version = $package['version'] ?? null;

            if (! is_string($name) || $name === '' || ! is_string($version) || $version === '') {
                throw new InvalidConfigurationException('composer.lock package entries need a name and a version.');
            }

            $lockedPackages[] = new LockedPackageData(
                name: $name,
                version: $version,
                isDirectDependency: isset($directPackageNames[$name]),
                isDevelopmentDependency: $isDevelopmentDependency,
            );
        }

        return $lockedPackages;
    }

    /**
     * @param  array<string, mixed>  $document
     */
    private function platformPhpVersion(array $document): ?string
    {
        $platform = $document['platform'] ?? null;

        if ($platform === null) {
            return null;
        }

        if (! is_array($platform)) {
            throw new InvalidConfigurationException('composer.lock platform must be an object.');
        }

        $phpVersion = $platform['php'] ?? null;

        if ($phpVersion === null) {
            return null;
        }

        if (! is_string($phpVersion) || $phpVersion === '') {
            throw new InvalidConfigurationException('composer.lock platform.php must be a version string.');
        }

        return $phpVersion;
    }
}
