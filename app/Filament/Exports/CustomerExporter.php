<?php

namespace App\Filament\Exports;

use App\Filament\Resources\Customers\CustomerSpreadsheetLayout;
use App\Models\Customer;
use App\Models\District;
use App\Models\Hamlet;
use App\Models\Package;
use App\Models\PrefixArea;
use App\Models\Village;
use Filament\Actions\Exports\Enums\ExportFormat;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Number;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Writer\XLSX\Options as XlsxWriterOptions;
use OpenSpout\Writer\XLSX\Writer as XlsxWriter;

class CustomerExporter extends Exporter
{
    protected static ?string $model = Customer::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('code')
                ->label('KODE')
                ->preventFormulaInjection(),
            ExportColumn::make('name')
                ->label('NAMA')
                ->preventFormulaInjection(),
            ExportColumn::make('hamlet.name')
                ->label('DUSUN')
                ->preventFormulaInjection(),
            ExportColumn::make('hamlet.village.name')
                ->label('DESA')
                ->preventFormulaInjection(),
            ExportColumn::make('hamlet.village.district.name')
                ->label('KECAMATAN')
                ->preventFormulaInjection(),
            ExportColumn::make('package.speed_mbps')
                ->label('PAKET')
                ->formatStateUsing(fn (mixed $state): string => (int) $state.' MBPS'),
            ExportColumn::make('package.price')
                ->label('HARGA'),
            ExportColumn::make('system')
                ->label('SYSTEM')
                ->state(fn (): string => Customer::SYSTEM)
                ->preventFormulaInjection(),
        ];
    }

    public function getXlsxWriterOptions(): ?XlsxWriterOptions
    {
        $options = new XlsxWriterOptions;
        CustomerSpreadsheetLayout::configureOptions($options, self::getColumnLabels());

        return $options;
    }

    public function getXlsxCellStyle(): ?Style
    {
        return CustomerSpreadsheetLayout::bodyStyle();
    }

    public function getXlsxHeaderCellStyle(): ?Style
    {
        return CustomerSpreadsheetLayout::headerStyle();
    }

    public function makeXlsxRow(array $values, ?Style $style = null): Row
    {
        $priceColumnIndex = array_search('package.price', array_keys($this->columnMap), true);

        if ($priceColumnIndex !== false) {
            $values[$priceColumnIndex] = CustomerSpreadsheetLayout::toNumericPrice($values[$priceColumnIndex] ?? null);
        }

        return CustomerSpreadsheetLayout::dataRow(
            $values,
            rowStyle: $style,
            priceColumnIndex: $priceColumnIndex === false ? null : $priceColumnIndex,
        );
    }

    public function makeXlsxHeaderRow(array $values, ?Style $style = null): Row
    {
        return CustomerSpreadsheetLayout::headerRow($values, $style);
    }

    public function configureXlsxWriterBeforeClose(XlsxWriter $writer): XlsxWriter
    {
        CustomerSpreadsheetLayout::configureSheet(
            $writer->getCurrentSheet(),
            $this->export->total_rows,
            self::getColumnLabels(),
        );
        $writer->getCurrentSheet()->setName(__('customers.spreadsheet.sheet_name'));

        return $writer;
    }

    /**
     * @return array<int, string>
     */
    private static function getColumnLabels(): array
    {
        return array_map(
            fn (ExportColumn $column): string => (string) $column->getLabel(),
            self::getColumns(),
        );
    }

    public static function getOptionsFormComponents(): array
    {
        return [
            Section::make(__('customers.export.filters.heading'))
                ->description(__('customers.export.filters.help'))
                ->schema([
                    Select::make('district_ids')
                        ->label(__('customers.fields.district'))
                        ->placeholder(__('customers.export.filters.placeholder'))
                        ->multiple()
                        ->searchable()
                        ->live()
                        ->afterStateUpdated(function (Set $set): void {
                            $set('village_ids', []);
                            $set('hamlet_ids', []);
                            $set('prefix_area_ids', []);
                        })
                        ->options(fn (): array => District::query()
                            ->whereHas('villages.hamlets.customers')
                            ->orderBy('name')
                            ->pluck('name', 'id')
                            ->all()),
                    Select::make('village_ids')
                        ->label(__('customers.fields.village'))
                        ->placeholder(__('customers.export.filters.placeholder'))
                        ->multiple()
                        ->searchable()
                        ->live()
                        ->afterStateUpdated(function (Set $set): void {
                            $set('hamlet_ids', []);
                            $set('prefix_area_ids', []);
                        })
                        ->options(fn (Get $get): array => Village::query()
                            ->whereHas('hamlets.customers')
                            ->when(
                                filled($districtIds = self::selectedIds($get('district_ids'))),
                                fn (Builder $query): Builder => $query->whereIn('district_id', $districtIds),
                            )
                            ->with('district:id,name')
                            ->orderBy('name')
                            ->get(['id', 'district_id', 'name'])
                            ->mapWithKeys(fn (Village $village): array => [
                                $village->getKey() => "{$village->name} ({$village->district->name})",
                            ])
                            ->all()),
                    Select::make('hamlet_ids')
                        ->label(__('customers.fields.hamlet'))
                        ->placeholder(__('customers.export.filters.placeholder'))
                        ->multiple()
                        ->searchable()
                        ->options(fn (Get $get): array => Hamlet::query()
                            ->whereHas('customers')
                            ->when(
                                filled($villageIds = self::selectedIds($get('village_ids'))),
                                fn (Builder $query): Builder => $query->whereIn('village_id', $villageIds),
                                fn (Builder $query): Builder => $query->when(
                                    filled($districtIds = self::selectedIds($get('district_ids'))),
                                    fn (Builder $query): Builder => $query->whereHas(
                                        'village',
                                        fn (Builder $query): Builder => $query->whereIn('district_id', $districtIds),
                                    ),
                                ),
                            )
                            ->with('village.district:id,name')
                            ->orderBy('name')
                            ->get(['id', 'village_id', 'name'])
                            ->mapWithKeys(fn (Hamlet $hamlet): array => [
                                $hamlet->getKey() => "{$hamlet->name} ({$hamlet->village->name} - {$hamlet->village->district->name})",
                            ])
                            ->all()),
                    Select::make('prefix_area_ids')
                        ->label(__('customers.export.filters.prefix'))
                        ->placeholder(__('customers.export.filters.placeholder'))
                        ->multiple()
                        ->searchable()
                        ->options(fn (Get $get): array => PrefixArea::query()
                            ->whereHas('villages.hamlets.customers')
                            ->when(
                                filled($districtIds = self::selectedIds($get('district_ids'))),
                                fn (Builder $query): Builder => $query->whereHas(
                                    'villages',
                                    fn (Builder $query): Builder => $query->whereIn('district_id', $districtIds),
                                ),
                            )
                            ->when(
                                filled($villageIds = self::selectedIds($get('village_ids'))),
                                fn (Builder $query): Builder => $query->whereHas(
                                    'villages',
                                    fn (Builder $query): Builder => $query->whereKey($villageIds),
                                ),
                            )
                            ->when(
                                filled($hamletIds = self::selectedIds($get('hamlet_ids'))),
                                fn (Builder $query): Builder => $query->whereHas(
                                    'villages.hamlets',
                                    fn (Builder $query): Builder => $query->whereKey($hamletIds),
                                ),
                            )
                            ->orderBy('prefix')
                            ->pluck('prefix', 'id')
                            ->all()),
                    Select::make('package_ids')
                        ->label(__('customers.fields.package'))
                        ->placeholder(__('customers.export.filters.placeholder'))
                        ->multiple()
                        ->searchable()
                        ->options(fn (): array => Package::query()
                            ->whereHas('customers')
                            ->orderBy('speed_mbps')
                            ->orderBy('price')
                            ->get()
                            ->mapWithKeys(fn (Package $package): array => [
                                $package->getKey() => $package->display_name,
                            ])
                            ->all()),
                ]),
        ];
    }

    /**
     * @param  Builder<Customer>  $query
     * @param  array<string, mixed>  $options
     * @return Builder<Customer>
     */
    public static function applyFilters(Builder $query, array $options): Builder
    {
        $districtIds = self::selectedIds($options['district_ids'] ?? []);
        $villageIds = self::selectedIds($options['village_ids'] ?? []);
        $hamletIds = self::selectedIds($options['hamlet_ids'] ?? []);
        $prefixAreaIds = self::selectedIds($options['prefix_area_ids'] ?? []);
        $packageIds = self::selectedIds($options['package_ids'] ?? []);

        return $query
            ->when(
                filled($districtIds),
                fn (Builder $query): Builder => $query->whereHas(
                    'hamlet.village.district',
                    fn (Builder $query): Builder => $query->whereKey($districtIds),
                ),
            )
            ->when(
                filled($villageIds),
                fn (Builder $query): Builder => $query->whereHas(
                    'hamlet.village',
                    fn (Builder $query): Builder => $query->whereKey($villageIds),
                ),
            )
            ->when(
                filled($hamletIds),
                fn (Builder $query): Builder => $query->whereHas(
                    'hamlet',
                    fn (Builder $query): Builder => $query->whereKey($hamletIds),
                ),
            )
            ->when(
                filled($prefixAreaIds),
                fn (Builder $query): Builder => $query->whereHas(
                    'hamlet.village.prefixArea',
                    fn (Builder $query): Builder => $query->whereKey($prefixAreaIds),
                ),
            )
            ->when(
                filled($packageIds),
                fn (Builder $query): Builder => $query->whereHas(
                    'package',
                    fn (Builder $query): Builder => $query->whereKey($packageIds),
                ),
            );
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        return __('customers.export.completed', [
            'exported' => Number::format($export->successful_rows),
            'failed' => Number::format($export->getFailedRowsCount()),
        ]);
    }

    public function getFormats(): array
    {
        return [ExportFormat::Xlsx];
    }

    public function getFileName(Export $export): string
    {
        return __('customers.export.file_name', [
            'export_id' => $export->getKey(),
        ]);
    }

    /**
     * @return array<int, mixed>
     */
    private static function selectedIds(mixed $value): array
    {
        return is_array($value) ? array_values($value) : [];
    }
}
