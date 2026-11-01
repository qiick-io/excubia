<?php

declare(strict_types=1);

namespace Qiick\Excubia\Registries;

use LogicException;
use Qiick\Excubia\Contracts\HealthCheckContract;
use Qiick\Excubia\Exceptions\InvalidConfigurationException;

/**
 * The ordered checks a run may execute, after `--only` and `--skip`.
 *
 * An unknown id is a usage error (exit code 2). When both flags are set,
 * `--only` selects first and `--skip` removes from that selection. The
 * registry does not construct checks; the runner passes them in spec order.
 */
final class HealthCheckRegistry
{
    /**
     * @param  list<HealthCheckContract>  $healthChecks
     */
    public function __construct(private readonly array $healthChecks)
    {
        $seenIdentifiers = [];

        foreach ($this->healthChecks as $healthCheck) {
            if (isset($seenIdentifiers[$healthCheck->id()])) {
                throw new LogicException('Health check id ['.$healthCheck->id().'] is registered twice.');
            }

            $seenIdentifiers[$healthCheck->id()] = true;
        }
    }

    /**
     * Checks that should run, in registry order unless `--only` reorders them.
     *
     * Null means the flag was omitted. An empty `--only` list runs nothing.
     *
     * @param  list<string>|null  $onlyCheckIds
     * @param  list<string>|null  $skippedCheckIds
     * @return list<HealthCheckContract>
     *
     * @throws InvalidConfigurationException
     */
    public function healthChecksFor(?array $onlyCheckIds, ?array $skippedCheckIds): array
    {
        $healthChecksById = [];

        foreach ($this->healthChecks as $healthCheck) {
            $healthChecksById[$healthCheck->id()] = $healthCheck;
        }

        $this->assertKnownIdentifiers($onlyCheckIds, $healthChecksById);
        $this->assertKnownIdentifiers($skippedCheckIds, $healthChecksById);

        $selected = $this->healthChecks;

        if ($onlyCheckIds !== null) {
            $selected = [];

            foreach ($onlyCheckIds as $checkId) {
                $selected[] = $healthChecksById[$checkId];
            }
        }

        if ($skippedCheckIds === null) {
            return $selected;
        }

        $skipped = array_fill_keys($skippedCheckIds, true);

        $remaining = [];

        foreach ($selected as $healthCheck) {
            if (isset($skipped[$healthCheck->id()])) {
                continue;
            }

            $remaining[] = $healthCheck;
        }

        return $remaining;
    }

    /**
     * @param  list<string>|null  $checkIds
     * @param  array<string, HealthCheckContract>  $healthChecksById
     */
    private function assertKnownIdentifiers(?array $checkIds, array $healthChecksById): void
    {
        if ($checkIds === null) {
            return;
        }

        $seen = [];

        foreach ($checkIds as $checkId) {
            if (isset($seen[$checkId])) {
                throw new InvalidConfigurationException('Check id ['.$checkId.'] was given more than once.');
            }

            $seen[$checkId] = true;

            if (! isset($healthChecksById[$checkId])) {
                throw new InvalidConfigurationException('Unknown check id ['.$checkId.'].');
            }
        }
    }
}
