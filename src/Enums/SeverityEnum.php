<?php

declare(strict_types=1);

namespace Qiick\Excubia\Enums;

/**
 * How serious a finding is. A higher weight outranks a lower one when
 * `--fail-on` decides the process exit code.
 */
enum SeverityEnum: string
{
    case Critical = 'critical';
    case High = 'high';
    case Medium = 'medium';
    case Low = 'low';
    case Info = 'info';

    /**
     * True when this severity is the threshold or more serious than it.
     *
     * Critical outranks high, high outranks medium, medium outranks low,
     * and low outranks info.
     */
    public function isAtLeastAsSevereAs(self $threshold): bool
    {
        return $this->weight() >= $threshold->weight();
    }

    /**
     * Comparable rank used by `isAtLeastAsSevereAs`. Not a user-facing score.
     */
    public function weight(): int
    {
        return match ($this) {
            self::Critical => 5,
            self::High => 4,
            self::Medium => 3,
            self::Low => 2,
            self::Info => 1,
        };
    }
}
