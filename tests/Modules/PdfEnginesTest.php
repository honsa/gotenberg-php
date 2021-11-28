<?php

declare(strict_types=1);

use Gotenberg\Gotenberg;
use Gotenberg\Stream;

it(
    'creates a valid request for the "/forms/pdfengines/merge" endpoint',
    /**
     * @param Stream[] $pdfs
     */
    function (array $pdfs, ?string $pdfFormat = null): void {
        $pdfEngines = Gotenberg::pdfEngines('');

        if ($pdfFormat !== null) {
            $pdfEngines->pdfFormat($pdfFormat);
        }

        $request = $pdfEngines->merge(...$pdfs);
        $body    = sanitize($request->getBody()->getContents());

        expect($request->getUri()->getPath())->toBe('/forms/pdfengines/merge');

        foreach ($pdfs as $index => $pdf) {
            $pdf->getStream()->rewind();
            expect($body)->toContainFormFile($index . '_' . $pdf->getFilename(), $pdf->getStream()->getContents(), 'application/pdf');
        }

        expect($body)->unless($pdfFormat === null, fn ($body) => $body->toContainFormValue('pdfFormat', $pdfFormat));
    }
)->with([
    [
        [
            Stream::string('my.pdf', 'PDF content'),
            Stream::string('my_second.pdf', 'Second PDF content'),
        ],
    ],
    [
        [
            Stream::string('my.pdf', 'PDF content'),
            Stream::string('my_second.pdf', 'Second PDF content'),
            Stream::string('my_third.pdf', 'Third PDF content'),
        ],
        'PDF/A-1a',
    ],
]);

it(
    'creates a valid request for the "/forms/pdfengines/convert" endpoint',
    function (string $pdfFormat, Stream ...$pdfs): void {
        $request = Gotenberg::pdfEngines('')->convert($pdfFormat, ...$pdfs);
        $body    = sanitize($request->getBody()->getContents());

        expect($request->getUri()->getPath())->toBe('/forms/pdfengines/convert');
        expect($body)->toContainFormValue('pdfFormat', $pdfFormat);

        foreach ($pdfs as $pdf) {
            $pdf->getStream()->rewind();
            expect($body)->toContainFormFile($pdf->getFilename(), $pdf->getStream()->getContents(), 'application/pdf');
        }
    }
)->with([
    [
        'PDF/A-1a',
        Stream::string('my.pdf', 'PDF content'),
    ],
    [
        'PDF/A-1a',
        Stream::string('my.pdf', 'PDF content'),
        Stream::string('my_second.pdf', 'Second PDF content'),
    ],
]);
