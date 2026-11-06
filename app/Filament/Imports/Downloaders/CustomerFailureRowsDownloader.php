<?php

namespace App\Filament\Imports\Downloaders;

use Filament\Actions\Imports\Downloaders\Contracts\Downloader;
use Filament\Actions\Imports\Models\FailedImportRow;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Facades\Lang;
use League\Csv\Bom;
use League\Csv\Writer;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Writer as XlsxWriter;
use RuntimeException;
use SplTempFileObject;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CustomerFailureRowsDownloader implements Downloader
{
    /**
     * @var array<int, string>
     */
    private const array CUSTOMER_COLUMN_ORDER = [
        'NAMA',
        'ID',
        'DUSUN',
        'DESA',
        'KECAMATAN',
        'PAKET',
        'HARGA',
        'SYSTEM',
    ];

    public function __invoke(Import $import): Response
    {
        $format = request()->query('format', 'xlsx');

        abort_unless(is_string($format) && in_array($format, ['xlsx', 'csv'], true), 404);

        return match ($format) {
            'csv' => $this->downloadCsv($import),
            'xlsx' => $this->downloadXlsx($import),
        };
    }

    private function downloadCsv(Import $import): StreamedResponse
    {
        $csv = Writer::createFromFileObject(new SplTempFileObject);
        $csv->setDelimiter(';');
        $csv->setOutputBOM(Bom::Utf8);

        $headers = $this->getHeaders($import);

        $csv->insertOne([
            ...$headers,
            __('customers.import.failure_reason_header'),
        ]);

        $import->failedRows()
            ->lazyById(100)
            ->each(function (FailedImportRow $failedImportRow) use ($csv, $headers): void {
                $csv->insertOne($this->rowValues($failedImportRow, $headers));
            });

        return response()->streamDownload(function () use ($csv): void {
            foreach ($csv->chunk(1000) as $offset => $chunk) {
                echo $chunk;

                if ($offset % 1000) {
                    flush();
                }
            }
        }, $this->getFileName($import, 'csv'), [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function downloadXlsx(Import $import): BinaryFileResponse
    {
        $temporaryPath = tempnam(sys_get_temp_dir(), 'customer-failures');

        if ($temporaryPath === false) {
            throw new RuntimeException('Unable to create a temporary customer failure report.');
        }

        $headers = $this->getHeaders($import);
        $writer = new XlsxWriter;
        $writer->openToFile($temporaryPath);
        $writer->addRow(Row::fromValues([
            ...$headers,
            __('customers.import.failure_reason_header'),
        ]));

        $import->failedRows()
            ->lazyById(100)
            ->each(function (FailedImportRow $failedImportRow) use ($writer, $headers): void {
                $writer->addRow(Row::fromValues($this->rowValues($failedImportRow, $headers)));
            });

        $writer->close();

        return response()
            ->download($temporaryPath, $this->getFileName($import, 'xlsx'), [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ])
            ->deleteFileAfterSend();
    }

    /**
     * @return array<int, string>
     */
    private function getHeaders(Import $import): array
    {
        /** @var ?FailedImportRow $firstFailedRow */
        $firstFailedRow = $import->failedRows()->first();

        return $this->orderHeaders(array_keys($firstFailedRow?->data ?? []));
    }

    /**
     * @param  array<int, string>  $headers
     * @return array<int, mixed>
     */
    private function rowValues(FailedImportRow $failedImportRow, array $headers): array
    {
        $row = array_map(
            fn (string $header): mixed => $failedImportRow->data[$header] ?? '',
            $headers,
        );

        return [
            ...$row,
            $failedImportRow->validation_error ?? __('filament-actions::import.failure_csv.system_error'),
        ];
    }

    private function getFileName(Import $import, string $format): string
    {
        return __('customers.import.failure_file_name', [
            'import_id' => $import->getKey(),
            'format' => $format,
        ]);
    }

    /**
     * @param  array<int, string>  $headers
     * @return array<int, string>
     */
    private function orderHeaders(array $headers): array
    {
        $failureReasonHeaders = [
            Lang::get('customers.import.failure_reason_header', [], 'en'),
            Lang::get('customers.import.failure_reason_header', [], 'id'),
        ];
        $headers = array_values(array_diff($headers, $failureReasonHeaders));
        $knownHeaders = array_values(array_intersect(self::CUSTOMER_COLUMN_ORDER, $headers));
        $otherHeaders = array_values(array_diff($headers, $knownHeaders));

        return [...$knownHeaders, ...$otherHeaders];
    }
}
