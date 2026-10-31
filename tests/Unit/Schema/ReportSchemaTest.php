<?php

declare(strict_types=1);

it('documents schema version 1 and the finding fields from the spec', function () {
    $schema = json_decode(
        (string) file_get_contents(dirname(__DIR__, 3).'/docs/report-schema.json'),
        true,
        512,
        JSON_THROW_ON_ERROR,
    );

    expect($schema['properties']['schemaVersion']['const'])->toBe(1)
        ->and($schema['$defs']['finding']['required'])->toBe([
            'checkId',
            'severity',
            'status',
            'subject',
            'message',
            'fixHint',
            'evidenceUrl',
        ])
        ->and($schema['$defs']['finding']['properties']['advisoryId']['type'])->toBe(['string', 'null']);
});
