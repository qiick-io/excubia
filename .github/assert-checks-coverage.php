<?php

declare(strict_types=1);

/**
 * Enforces the spec gate: line coverage of src/Checks is at least 90%.
 *
 * Before any check class exists the gate does not fail the build. The
 * percentage is computed only from files under src/Checks, not from the
 * rest of the package.
 */

$cloverPath = $argv[1] ?? '';
$checksDirectory = $argv[2] ?? dirname(__DIR__).'/src/Checks';
$checkFiles = glob($checksDirectory.'/*.php');

if ($checkFiles === false || $checkFiles === []) {
    fwrite(STDOUT, "No check classes yet. The src/Checks coverage gate is not applied.\n");

    exit(0);
}

if ($cloverPath === '' || ! is_file($cloverPath)) {
    fwrite(STDERR, "Clover report was not found.\n");

    exit(1);
}

$report = simplexml_load_file($cloverPath);

if ($report === false) {
    fwrite(STDERR, "Clover report could not be read.\n");

    exit(1);
}

$coveredLines = 0;
$executableLines = 0;

foreach ($report->xpath('//file') ?: [] as $file) {
    $path = (string) $file['name'];

    if (! str_contains($path, DIRECTORY_SEPARATOR.'src'.DIRECTORY_SEPARATOR.'Checks'.DIRECTORY_SEPARATOR)) {
        continue;
    }

    foreach ($file->line as $line) {
        if ((string) $line['type'] !== 'stmt') {
            continue;
        }

        $executableLines++;

        if ((int) $line['count'] > 0) {
            $coveredLines++;
        }
    }
}

if ($executableLines === 0) {
    fwrite(STDERR, "src/Checks has classes but the coverage report has no executable lines for them.\n");

    exit(1);
}

$percentage = ($coveredLines / $executableLines) * 100;

if ($percentage < 90) {
    fwrite(STDERR, sprintf("src/Checks coverage is %.1f%%, below 90%%.\n", $percentage));

    exit(1);
}

fwrite(STDOUT, sprintf("src/Checks coverage is %.1f%%.\n", $percentage));

exit(0);
