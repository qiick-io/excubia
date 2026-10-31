<?php

declare(strict_types=1);

namespace Qiick\Excubia\Enums;

/**
 * Renderers shipped in v0.1.0. SARIF is a later milestone and has no case.
 */
enum ReportFormatEnum: string
{
    case Table = 'table';
    case Json = 'json';
    case Markdown = 'markdown';
}
