<?php

declare(strict_types=1);

namespace Qiick\Excubia\Parsers;

use Qiick\Excubia\Exceptions\InvalidConfigurationException;

/**
 * Reads the optional `composer.json` fields the snapshot needs.
 *
 * Platform packages (`php`, `ext-*`, Composer runtime APIs) are not package
 * names. Everything else in `require` and `require-dev` is a direct dependency.
 */
final class ComposerJsonParser
{
    /**
     * @param  array<string, mixed>  $document
     * @return array{
     *     platformPhpVersion: string|null,
     *     phpConstraint: string|null,
     *     directPackageNames: list<string>,
     *     directDevelopmentPackageNames: list<string>
     * }
     */
    public function parse(array $document): array
    {
        $require = $this->constraintMap($document['require'] ?? [], 'require');
        $requireDev = $this->constraintMap($document['require-dev'] ?? [], 'require-dev');

        return [
            'platformPhpVersion' => $this->platformPhpVersion($document),
            'phpConstraint' => $require['php'] ?? null,
            'directPackageNames' => $this->packageNames($require),
            'directDevelopmentPackageNames' => $this->packageNames($requireDev),
        ];
    }

    /**
     * @param  array<string, mixed>  $document
     */
    private function platformPhpVersion(array $document): ?string
    {
        $config = $document['config'] ?? null;

        if ($config === null) {
            return null;
        }

        if (! is_array($config)) {
            throw new InvalidConfigurationException('composer.json config must be an object.');
        }

        $platform = $config['platform'] ?? null;

        if ($platform === null) {
            return null;
        }

        if (! is_array($platform)) {
            throw new InvalidConfigurationException('composer.json config.platform must be an object.');
        }

        $phpVersion = $platform['php'] ?? null;

        if ($phpVersion === null) {
            return null;
        }

        if (! is_string($phpVersion) || $phpVersion === '') {
            throw new InvalidConfigurationException('composer.json config.platform.php must be a version string.');
        }

        return $phpVersion;
    }

    /**
     * @return array<string, string>
     */
    private function constraintMap(mixed $require, string $key): array
    {
        if ($require === []) {
            return [];
        }

        if (! is_array($require)) {
            throw new InvalidConfigurationException('composer.json '.$key.' must be an object.');
        }

        $constraints = [];

        foreach ($require as $name => $constraint) {
            if (! is_string($name) || ! is_string($constraint) || $constraint === '') {
                throw new InvalidConfigurationException('composer.json '.$key.' entries must be package constraints.');
            }

            $constraints[$name] = $constraint;
        }

        return $constraints;
    }

    /**
     * @param  array<string, string>  $constraints
     * @return list<string>
     */
    private function packageNames(array $constraints): array
    {
        $names = [];

        foreach (array_keys($constraints) as $name) {
            if ($this->isPlatformRequirement($name)) {
                continue;
            }

            $names[] = $name;
        }

        return $names;
    }

    private function isPlatformRequirement(string $name): bool
    {
        return $name === 'php'
            || str_starts_with($name, 'ext-')
            || $name === 'composer-runtime-api'
            || $name === 'composer-plugin-api';
    }
}
