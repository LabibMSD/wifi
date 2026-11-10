<?php

namespace App\Filament\Resources\Customers;

use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Border;
use OpenSpout\Common\Entity\Style\BorderPart;
use OpenSpout\Common\Entity\Style\CellAlignment;
use OpenSpout\Common\Entity\Style\CellVerticalAlignment;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Writer\AutoFilter;
use OpenSpout\Writer\Common\Entity\Sheet;
use OpenSpout\Writer\XLSX\Entity\SheetView;
use OpenSpout\Writer\XLSX\Options;

final class CustomerSpreadsheetLayout
{
    /**
     * @var array<string, int>
     */
    private const array CUSTOMER_COLUMN_WIDTHS = [
        'KODE' => 16,
        'NAMA' => 40,
        'DUSUN' => 24,
        'DESA' => 24,
        'KECAMATAN' => 20,
        'PAKET' => 16,
        'HARGA' => 20,
        'SYSTEM' => 16,
    ];

    public static function headerStyle(): Style
    {
        return (new Style)
            ->setBorder(self::cellBorder())
            ->setFontName('Arial')
            ->setFontSize(10)
            ->setFontBold()
            ->setBackgroundColor('D6DCE4')
            ->setCellAlignment(CellAlignment::CENTER)
            ->setCellVerticalAlignment(CellVerticalAlignment::CENTER)
            ->setShouldWrapText();
    }

    public static function bodyStyle(): Style
    {
        return (new Style)
            ->setBorder(self::cellBorder())
            ->setFontName('Arial')
            ->setFontSize(10)
            ->setCellVerticalAlignment(CellVerticalAlignment::CENTER);
    }

    public static function priceStyle(): Style
    {
        return self::bodyStyle()
            ->setFormat('"Rp." #,##0')
            ->setCellAlignment(CellAlignment::RIGHT);
    }

    public static function formatPrice(mixed $value): string
    {
        $price = self::numericPrice($value);

        if ($price === null) {
            return is_scalar($value) ? trim((string) $value) : '';
        }

        return 'Rp. '.number_format($price, 0, ',', '.');
    }

    public static function toNumericPrice(mixed $value): mixed
    {
        $price = self::numericPrice($value);

        return $price ?? (is_scalar($value) || $value === null ? $value : '');
    }

    public static function failureReasonStyle(): Style
    {
        return self::bodyStyle()
            ->setBackgroundColor('FEF3C7')
            ->setShouldWrapText();
    }

    /**
     * @param  array<int, mixed>  $values
     */
    public static function headerRow(array $values, ?Style $rowStyle = null): Row
    {
        return Row::fromValues($values, $rowStyle ?? self::headerStyle());
    }

    /**
     * @param  array<int, mixed>  $values
     */
    public static function failureHeaderRow(array $values, string $failureReasonHeader): Row
    {
        $columnStyles = [];
        $failureReasonIndex = array_search($failureReasonHeader, $values, true);

        if ($failureReasonIndex !== false) {
            $columnStyles[$failureReasonIndex] = self::headerStyle()
                ->setCellAlignment(CellAlignment::LEFT);
        }

        return Row::fromValuesWithStyles($values, self::headerStyle(), $columnStyles);
    }

    /**
     * @param  array<int, mixed>  $values
     */
    public static function dataRow(
        array $values,
        bool $hasFailureReason = false,
        ?Style $rowStyle = null,
        ?int $priceColumnIndex = 6,
    ): Row {
        $columnStyles = [];

        foreach (array_keys($values) as $index) {
            if ($index === $priceColumnIndex) {
                $columnStyles[$index] = self::priceStyle();
            }

            if ($hasFailureReason && ($index === array_key_last($values))) {
                $columnStyles[$index] = self::failureReasonStyle();
            }
        }

        return Row::fromValuesWithStyles($values, $rowStyle ?? self::bodyStyle(), $columnStyles);
    }

    /**
     * @param  array<int, string>  $headers
     */
    public static function configureOptions(Options $options, array $headers): void
    {
        foreach ($headers as $index => $header) {
            $options->setColumnWidth(self::columnWidth($header), $index + 1);
        }
    }

    /**
     * @param  array<int, string>  $headers
     * @param  array<string, int>  $columnWidths
     */
    public static function configureSheet(
        Sheet $sheet,
        int $dataRows,
        array $headers,
        array $columnWidths = [],
    ): void {
        $sheet->setSheetView(
            (new SheetView)
                ->setFreezeRow(2)
                ->setShowGridLines(true),
        );

        foreach ($headers as $index => $header) {
            $sheet->setColumnWidth($columnWidths[$header] ?? self::columnWidth($header), $index + 1);
        }

        if ($dataRows > 0) {
            $sheet->setAutoFilter(new AutoFilter(0, 1, count($headers) - 1, $dataRows + 1));
        }
    }

    public static function fitColumnWidth(string $header, int $contentLength): int
    {
        return min(255, max(mb_strlen($header), $contentLength) + 2);
    }

    private static function columnWidth(string $header): int
    {
        if ($header === __('customers.import.failure_reason_header')) {
            return 48;
        }

        return self::CUSTOMER_COLUMN_WIDTHS[$header] ?? 20;
    }

    private static function cellBorder(): Border
    {
        return new Border(
            new BorderPart(Border::LEFT, 'D1D5DB', Border::WIDTH_THIN),
            new BorderPart(Border::RIGHT, 'D1D5DB', Border::WIDTH_THIN),
            new BorderPart(Border::TOP, 'D1D5DB', Border::WIDTH_THIN),
            new BorderPart(Border::BOTTOM, 'D1D5DB', Border::WIDTH_THIN),
        );
    }

    private static function numericPrice(mixed $value): ?int
    {
        if (! is_string($value) && ! is_numeric($value)) {
            return null;
        }

        $formattedValue = preg_replace('/\s+/', '', trim((string) $value));

        if (blank($formattedValue)) {
            return null;
        }

        $formattedValue = preg_replace('/\ARp\.?/i', '', $formattedValue);
        $formattedValue = preg_replace('/[,.]00\z/', '', $formattedValue);

        if (! preg_match('/\A(?:\d+|\d{1,3}(?:,\d{3})+|\d{1,3}(?:\.\d{3})+)\z/', $formattedValue)) {
            return null;
        }

        $hasThousandsSeparator = str_contains($formattedValue, ',') || str_contains($formattedValue, '.');
        $price = (int) preg_replace('/[,.]/', '', $formattedValue);

        if ($price < 1) {
            return null;
        }

        if (! $hasThousandsSeparator && $price < 1000) {
            $price *= 1000;
        }

        return $price;
    }
}
