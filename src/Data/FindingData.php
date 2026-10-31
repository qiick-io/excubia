<?php

declare(strict_types=1);

namespace Qiick\Excubia\Data;

use Qiick\Excubia\Enums\FindingStatusEnum;
use Qiick\Excubia\Enums\SeverityEnum;

/**
 * One row of a health report.
 *
 * The required fields are the v0.1.0 finding shape. `advisoryId` is optional
 * so an ignore rule can name one advisory. It is omitted from the required
 * JSON schema fields.
 */
final readonly class FindingData
{
    public function __construct(
        public string $checkId,
        public SeverityEnum $severity,
        public FindingStatusEnum $status,
        public string $subject,
        public string $message,
        public string $fixHint,
        public ?string $evidenceUrl,
        public ?string $advisoryId = null,
    ) {}
}
