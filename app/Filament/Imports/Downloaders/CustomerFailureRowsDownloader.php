<?php

namespace App\Filament\Imports\Downloaders;

use App\Filament\Resources\Customers\CustomerSpreadsheetLayout;
use Filament\Actions\Imports\Downloaders\Contracts\Downloader;
use Filament\Actions\Imports\Models\FailedImportRow;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Facades\Lang;
use League\Csv\Bom;
use League\Csv\Writer;
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
        'KODE',
        'NAMA',
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
        $failedRowsCount = $import->failedRows()->count();
        $writer = new XlsxWriter;
        $writer->openToFile($temporaryPath);
        $sheet = $writer->getCurrentSheet();
        $sheet->setName(__('customers.spreadsheet.sheet_name'));
        $headersWithFailureReason = [
            ...$headers,
            __('customers.import.failure_reason_header'),
        ];
        $writer->addRow(CustomerSpreadsheetLayout::failureHeaderRow(
            $headersWithFailureReason,
            __('customers.import.failure_reason_header'),
        ));

        $failureReasonHeader = __('customers.import.failure_reason_header');
        $longestFailureReasonLength = mb_strlen($failureReasonHeader);

        $import->failedRows()
            ->lazyById(100)
            ->each(function (FailedImportRow $failedImportRow) use (
                $writer,
                $headers,
                &$longestFailureReasonLength,
            ): void {
                $rowValues = $this->rowValues($failedImportRow, $headers, formatForXlsx: true);
                $failureReason = $rowValues[array_key_last($rowValues)];
                $longestFailureReasonLength = max($longestFailureReasonLength, mb_strlen((string) $failureReason));

                $writer->addRow(CustomerSpreadsheetLayout::dataRow(
                    $rowValues,
                    hasFailureReason: true,
                ));
            });

        CustomerSpreadsheetLayout::configureSheet(
            $sheet,
            $failedRowsCount,
            $headersWithFailureReason,
            [
                $failureReasonHeader => CustomerSpreadsheetLayout::fitColumnWidth(
                    $failureReasonHeader,
                    $longestFailureReasonLength,
                ),
            ],
        );

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
    private function rowValues(
        FailedImportRow $failedImportRow,
        array $headers,
        bool $formatForXlsx = false,
    ): array {
        $row = array_map(
            fn (string $header): mixed => $this->getValueForHeader($failedImportRow, $header, $formatForXlsx),
            $headers,
        );

        return [
            ...$row,
            $failedImportRow->validation_error ?? __('filament-actions::import.failure_csv.system_error'),
        ];
    }

    private function getValueForHeader(
        FailedImportRow $failedImportRow,
        string $header,
        bool $formatForXlsx,
    ): mixed {
        if ($header === 'HARGA') {
            $price = $failedImportRow->data[$header] ?? '';

            return $formatForXlsx
                ? CustomerSpreadsheetLayout::toNumericPrice($price)
                : CustomerSpreadsheetLayout::formatPrice($price);
        }

        if ($header !== 'KODE') {
            return $failedImportRow->data[$header] ?? '';
        }

        return $failedImportRow->data['KODE']
            ?? $failedImportRow->data['CODE']
            ?? $failedImportRow->data['ID']
            ?? '';
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
        $headers = array_map(
            fn (string $header): string => in_array(strtoupper(trim($header)), ['CODE', 'ID'], true)
                ? 'KODE'
                : $header,
            $headers,
        );
        $headers = array_values(array_unique($headers));
        $knownHeaders = array_values(array_intersect(self::CUSTOMER_COLUMN_ORDER, $headers));
        $otherHeaders = array_values(array_diff($headers, $knownHeaders));

        return [...$knownHeaders, ...$otherHeaders];
    }
}
