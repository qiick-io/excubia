<?php

declare(strict_types=1);

namespace Qiick\Excubia\Data;

/**
 * The two GitHub fields the stale-package check needs. Nothing else is kept.
 */
final readonly class GitHubRepositoryData
{
    public function __construct(
        public string $fullName,
        public bool $isArchived,
        public ?string $pushedAt,
    ) {}
}
