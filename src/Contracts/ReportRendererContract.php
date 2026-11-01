<?php

declare(strict_types=1);

namespace Qiick\Excubia\Contracts;

use Qiick\Excubia\Data\HealthReportData;

interface ReportRendererContract
{
    /**
     * Turn a report into the text the CLI writes to stdout or `--output`.
     *
     * Rendering has no side effects and does not read the network.
     */
    public function render(HealthReportData $healthReport): string;
}
