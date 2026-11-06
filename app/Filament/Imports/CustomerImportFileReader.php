<?php

namespace App\Filament\Imports;

use DateInterval;
use DateTimeInterface;
use OpenSpout\Reader\SheetInterface;
use OpenSpout\Reader\XLSX\Reader;
use RuntimeException;

final class CustomerImportFileReader
{
    public static function isXlsx(string $fileName): bool
    {
        return strtolower(pathinfo($fileName, PATHINFO_EXTENSION)) === 'xlsx';
    }

    /**
     * @return array<string, string>
     */
    public static function getSheetOptions(string $filePath): array
    {
        $reader = new Reader;
        $reader->open($filePath);

        try {
            $sheets = [];

            foreach ($reader->getSheetIterator() as $sheet) {
                $sheets[$sheet->getName()] = $sheet->getName();
            }

            return $sheets;
        } finally {
            $reader->close();
        }
    }

    /**
     * @return array<int, string>
     */
    public static function getHeaders(string $filePath, ?string $sheetName = null): array
    {
        [$reader, $sheet] = self::openSheet($filePath, $sheetName);

        try {
            foreach ($sheet->getRowIterator() as $row) {
                return array_map(self::cellValueToString(...), $row->toArray());
            }

            return [];
        } finally {
            $reader->close();
        }
    }

    /**
     * @return resource
     */
    public static function getCsvStream(string $filePath, ?string $sheetName = null)
    {
        [$reader, $sheet] = self::openSheet($filePath, $sheetName);
        $stream = fopen('php://temp/maxmemory:5242880', 'w+b');

        if ($stream === false) {
            $reader->close();

            throw new RuntimeException('Unable to create a temporary CSV stream for the workbook.');
        }

        $streamIsReady = false;
        $headerColumnCount = null;

        try {
            foreach ($sheet->getRowIterator() as $row) {
                $values = array_map(self::cellValueToString(...), $row->toArray());
                $headerColumnCount ??= count($values);

                if (count($values) < $headerColumnCount) {
                    $values = array_pad($values, $headerColumnCount, '');
                }

                if (fputcsv($stream, $values, ',', '"', '') === false) {
                    throw new RuntimeException('Unable to convert a worksheet row to CSV.');
                }
            }

            rewind($stream);
            $streamIsReady = true;

            return $stream;
        } finally {
            $reader->close();

            if (! $streamIsReady) {
                fclose($stream);
            }
        }
    }

    /**
     * @return array{Reader, SheetInterface}
     */
    private static function openSheet(string $filePath, ?string $sheetName): array
    {
        $reader = new Reader;
        $reader->open($filePath);
        $selectedSheet = null;

        try {
            foreach ($reader->getSheetIterator() as $sheet) {
                if (($sheetName === null) || ($sheet->getName() === $sheetName)) {
                    $selectedSheet = $sheet;

                    break;
                }
            }
        } finally {
            if ($selectedSheet === null) {
                $reader->close();
            }
        }

        if ($selectedSheet === null) {
            throw new RuntimeException('The selected worksheet does not exist in the workbook.');
        }

        return [$reader, $selectedSheet];
    }

    private static function cellValueToString(mixed $value): string
    {
        return match (true) {
            $value === null => '',
            $value instanceof DateTimeInterface => $value->format('Y-m-d H:i:s'),
            $value instanceof DateInterval => $value->format('%r%h:%i:%s'),
            is_bool($value) => $value ? '1' : '0',
            default => (string) $value,
        };
    }
}
