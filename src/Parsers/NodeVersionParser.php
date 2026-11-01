<?php

declare(strict_types=1);

namespace Qiick\Excubia\Parsers;

/**
 * Picks the Node version declaration the end-of-life check will read.
 *
 * The spec lists the sources as `.nvmrc`, `.node-version`, then
 * `package.json` `engines.node`. The first non-empty value wins. The text is
 * returned unchanged apart from trimming, so a range such as `>=20` stays a
 * range for the check to interpret.
 */
final class NodeVersionParser
{
    public function declarationFrom(?string $nvmrcContents, ?string $nodeVersionContents, ?string $enginesNode): ?string
    {
        foreach ([$nvmrcContents, $nodeVersionContents, $enginesNode] as $candidate) {
            $declaration = $this->firstNonEmptyLine($candidate);

            if ($declaration !== null) {
                return $declaration;
            }
        }

        return null;
    }

    private function firstNonEmptyLine(?string $contents): ?string
    {
        if ($contents === null) {
            return null;
        }

        $lines = preg_split("/\R/", $contents);

        if ($lines === false) {
            return null;
        }

        foreach ($lines as $line) {
            $trimmed = trim($line);

            if ($trimmed !== '') {
                return $trimmed;
            }
        }

        return null;
    }
}
