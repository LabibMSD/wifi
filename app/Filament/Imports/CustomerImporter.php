<?php

namespace App\Filament\Imports;

use App\Filament\Imports\Downloaders\CustomerFailureRowsDownloader;
use App\Models\Customer;
use App\Models\District;
use App\Models\Hamlet;
use App\Models\Package;
use App\Models\PrefixArea;
use App\Models\Village;
use Closure;
use Filament\Actions\Action;
use Filament\Actions\Imports\Downloaders\Contracts\Downloader;
use Filament\Actions\Imports\Exceptions\RowImportFailedException;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Number;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use League\Csv\Info;
use League\Csv\Reader;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Override;

class CustomerImporter extends Importer
{
    private const float SIMILARITY_WARNING_THRESHOLD = 85;

    /**
     * @var array<string, string>
     */
    private array $similarIncomingLocations = [];

    /**
     * @var array<string, true>
     */
    private array $duplicateIncomingCodes = [];

    protected static ?string $model = Customer::class;

    /**
     * @param  array<string, string>  $columnMap
     * @param  array<string, mixed>  $options
     */
    public function __construct(Import $import, array $columnMap, array $options)
    {
        parent::__construct($import, $columnMap, $options);

        if (filled($import->file_path)) {
            $fileName = (string) $import->file_name;
            $worksheet = is_string($options['worksheet'] ?? null) ? $options['worksheet'] : null;
            $records = self::recordsForFile($import->file_path, $fileName, $worksheet);

            $this->similarIncomingLocations = self::findSimilarIncomingLocations($records, $columnMap);
            $this->duplicateIncomingCodes = self::findDuplicateIncomingCodes($records, $columnMap);
        }
    }

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('code')
                ->label(__('customers.fields.code'))
                ->guess(['ID'])
                ->requiredMapping()
                ->castStateUsing(fn (mixed $state): mixed => is_string($state) ? trim($state) : $state)
                ->rules([
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('customers', 'code'),
                ]),
            ImportColumn::make('name')
                ->label(__('customers.fields.name'))
                ->guess(['NAMA'])
                ->requiredMapping()
                ->rules(['required', 'string', 'max:255', 'regex:/[\pL\pN]/u']),
            ImportColumn::make('hamlet')
                ->label(__('customers.fields.hamlet'))
                ->guess(['DUSUN'])
                ->requiredMapping()
                ->fillRecordUsing(function (Customer $record, string $state, array $data): void {
                    $hamlet = self::findOrCreateHamlet($state, $data);

                    if (! $hamlet) {
                        throw new RowImportFailedException(__('customers.import.invalid_location'));
                    }

                    $record->hamlet()->associate($hamlet);
                })
                ->rules(['required', 'string', 'max:255', 'regex:/[\pL\pN]/u']),
            ImportColumn::make('package')
                ->label(__('customers.fields.package'))
                ->guess(['PAKET'])
                ->requiredMapping()
                ->integer()
                ->fillRecordUsing(function (Customer $record, int $state, array $data): void {
                    $package = self::findOrCreatePackage($state, $data);

                    if (! $package) {
                        throw new RowImportFailedException(__('customers.import.invalid_package'));
                    }

                    $record->package()->associate($package);
                })
                ->rules(['required', 'integer', 'min:1']),
            ImportColumn::make('village_name')
                ->label(__('customers.fields.village'))
                ->guess(['DESA'])
                ->requiredMapping()
                ->fillRecordUsing(fn (): null => null)
                ->rules(['required', 'string', 'max:255', 'regex:/[\pL\pN]/u']),
            ImportColumn::make('district_name')
                ->label(__('customers.fields.district'))
                ->guess(['KECAMATAN'])
                ->requiredMapping()
                ->fillRecordUsing(fn (): null => null)
                ->rules(['required', 'string', 'max:255', 'regex:/[\pL\pN]/u']),
            ImportColumn::make('price')
                ->label(__('customers.fields.price'))
                ->guess(['HARGA'])
                ->requiredMapping()
                ->fillRecordUsing(fn (): null => null)
                ->rules([
                    'required',
                    function (string $attribute, mixed $state, Closure $fail): void {
                        if (self::parsePrice($state) === null) {
                            $fail(__('customers.import.invalid_price'));
                        }
                    },
                ]),
            ImportColumn::make('system')
                ->label(__('customers.fields.system'))
                ->guess(['SYSTEM'])
                ->requiredMapping()
                ->fillRecordUsing(fn (): null => null)
                ->rules([
                    'required',
                    'string',
                    'regex:/[\pL\pN]/u',
                    function (string $attribute, mixed $state, Closure $fail): void {
                        if (
                            preg_match('/[\pL\pN]/u', (string) $state) &&
                            Str::upper(trim((string) $state)) !== Customer::SYSTEM
                        ) {
                            $fail(__('customers.import.invalid_system'));
                        }
                    },
                ]),
        ];
    }

    public static function getOptionsFormComponents(): array
    {
        return [
            Select::make('worksheet')
                ->label(__('customers.import.worksheet'))
                ->options(function (Get $get): array {
                    $file = $get('file');

                    if (! $file instanceof TemporaryUploadedFile || ! CustomerImportFileReader::isXlsx($file->getClientOriginalName())) {
                        return [];
                    }

                    $filePath = $file->getRealPath();

                    return is_string($filePath) ? CustomerImportFileReader::getSheetOptions($filePath) : [];
                })
                ->default(function (Get $get): ?string {
                    $file = $get('file');

                    if (! $file instanceof TemporaryUploadedFile || ! CustomerImportFileReader::isXlsx($file->getClientOriginalName())) {
                        return null;
                    }

                    $filePath = $file->getRealPath();

                    if (! is_string($filePath)) {
                        return null;
                    }

                    return array_key_first(CustomerImportFileReader::getSheetOptions($filePath));
                })
                ->required(fn (Get $get): bool => $get('file') instanceof TemporaryUploadedFile
                    && CustomerImportFileReader::isXlsx($get('file')->getClientOriginalName()))
                ->live()
                ->afterStateUpdated(function (?string $state, Get $get, Set $set): void {
                    $file = $get('file');

                    if (! $file instanceof TemporaryUploadedFile || ! is_string($state)) {
                        return;
                    }

                    $filePath = $file->getRealPath();

                    if (is_string($filePath)) {
                        $set('columnMap', self::guessColumnMap(CustomerImportFileReader::getHeaders($filePath, $state)));
                    }
                })
                ->visible(fn (Get $get): bool => $get('file') instanceof TemporaryUploadedFile
                    && CustomerImportFileReader::isXlsx($get('file')->getClientOriginalName())),
        ];
    }

    /**
     * @param  array<int, string>  $headers
     * @return array<string, ?string>
     */
    public static function guessColumnMap(array $headers): array
    {
        $lowercaseHeaders = array_map(Str::lower(...), $headers);
        $headerKeys = array_combine($lowercaseHeaders, $headers) ?: [];

        return array_reduce(self::getColumns(), function (array $columnMap, ImportColumn $column) use ($lowercaseHeaders, $headerKeys): array {
            $guess = Arr::first(array_intersect($lowercaseHeaders, $column->getGuesses()));
            $columnMap[$column->getName()] = $headerKeys[$guess] ?? null;

            return $columnMap;
        }, []);
    }

    public function resolveRecord(): Customer
    {
        return new Customer;
    }

    /**
     * @return array<string, string>
     */
    #[Override]
    public function getValidationMessages(): array
    {
        return [
            'code.unique' => __('customers.import.duplicate_code'),
            'name.regex' => __('customers.import.invalid_text_value'),
            'hamlet.regex' => __('customers.import.invalid_text_value'),
            'village_name.regex' => __('customers.import.invalid_text_value'),
            'district_name.regex' => __('customers.import.invalid_text_value'),
            'system.regex' => __('customers.import.invalid_text_value'),
        ];
    }

    protected function beforeValidate(): void
    {
        $fingerprint = self::rowFingerprint($this->getOriginalData());

        if (isset($this->duplicateIncomingCodes[$fingerprint])) {
            throw new RowImportFailedException(__('customers.import.duplicate_code'));
        }

        $failureMessage = $this->similarIncomingLocations[$fingerprint] ?? null;

        if (filled($failureMessage)) {
            throw new RowImportFailedException($failureMessage);
        }

        $this->failIfPackageIsAmbiguous();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        return __('customers.import.completed', [
            'imported' => Number::format($import->successful_rows),
            'failed' => Number::format($import->getFailedRowsCount()),
        ]);
    }

    #[Override]
    public static function getFailedRowsDownloader(): Downloader
    {
        return app(CustomerFailureRowsDownloader::class);
    }

    #[Override]
    public static function modifyCompletedNotification(Notification $notification, Import $import): Notification
    {
        $failedRowsCount = $import->getFailedRowsCount();

        if (! $failedRowsCount) {
            return $notification;
        }

        $authGuard = $import->getOptions()['authGuard'] ?? config('auth.defaults.guard');
        $baseUrl = rtrim(request()->getBaseUrl(), '/');
        $downloadAction = static fn (string $format, string $label): Action => Action::make('downloadFailedRows'.Str::studly($format))
            ->label($label)
            ->color($format === 'xlsx' ? 'danger' : 'gray')
            ->url($baseUrl.URL::signedRoute('filament.imports.failed-rows.download', [
                'authGuard' => $authGuard,
                'import' => $import,
                'format' => $format,
            ], absolute: false), shouldOpenInNewTab: true)
            ->markAsRead();

        return $notification->actions([
            $downloadAction('xlsx', __('customers.import.download_failed_rows_xlsx', [
                'count' => Number::format($failedRowsCount),
            ])),
            $downloadAction('csv', __('customers.import.download_failed_rows_csv', [
                'count' => Number::format($failedRowsCount),
            ])),
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private static function findOrCreateHamlet(string $name, array $data): ?Hamlet
    {
        $villageName = trim((string) ($data['village_name'] ?? ''));
        $districtName = trim((string) ($data['district_name'] ?? ''));

        if (blank($name) || blank($villageName) || blank($districtName)) {
            return null;
        }

        $district = District::query()
            ->whereRaw("LOWER(REPLACE(name, ' ', '')) = ?", [self::normalizeName($districtName)])
            ->first();

        if (! $district) {
            self::failIfSimilarName($districtName, District::query()->pluck('name'));
        }

        $village = $district
            ? Village::query()
                ->where('district_id', $district->id)
                ->whereRaw("LOWER(REPLACE(name, ' ', '')) = ?", [self::normalizeName($villageName)])
                ->first()
            : null;

        if (! $village && $district) {
            self::failIfSimilarName(
                $villageName,
                Village::query()->where('district_id', $district->id)->pluck('name'),
            );
        }

        $hamlet = $village
            ? Hamlet::query()
                ->where('village_id', $village->id)
                ->whereRaw("LOWER(REPLACE(name, ' ', '')) = ?", [self::normalizeName($name)])
                ->first()
            : null;

        if (! $hamlet && $village) {
            self::failIfSimilarName(
                $name,
                Hamlet::query()->where('village_id', $village->id)->pluck('name'),
            );
        }

        if (! $district) {
            $district = District::create(['name' => $districtName]);
        }

        if (! $village) {
            $code = (string) ($data['code'] ?? '');
            $codePrefix = preg_match('/\A([A-Z]{2})\d{5,}\z/i', $code, $matches)
                ? Str::upper($matches[1])
                : null;
            $villageLetters = preg_replace('/[^\pL]/u', '', $villageName);
            $prefix = $codePrefix ?? Str::upper(Str::substr($villageLetters, 0, 2));

            if (blank($prefix)) {
                return null;
            }

            $prefixArea = PrefixArea::firstOrCreate(['prefix' => $prefix]);

            $village = Village::create([
                'district_id' => $district->id,
                'prefix_area_id' => $prefixArea->id,
                'name' => $villageName,
            ]);
        }

        if ($hamlet) {
            return $hamlet;
        }

        return Hamlet::create([
            'village_id' => $village->id,
            'name' => trim($name),
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private static function findOrCreatePackage(int $speed, array $data): ?Package
    {
        $price = self::parsePrice($data['price'] ?? null);

        if ($speed <= 0 || $price === null) {
            return null;
        }

        $package = Package::query()
            ->where('speed_mbps', $speed)
            ->where('price', $price)
            ->first();

        if ($package) {
            return $package;
        }

        $packages = Package::query()->where('speed_mbps', $speed);
        $matchingSpeedCount = (clone $packages)->count();

        if ($matchingSpeedCount > 1) {
            throw new RowImportFailedException(__('customers.import.ambiguous_package'));
        }

        return $packages->first() ?? Package::create([
            'speed_mbps' => $speed,
            'price' => $price,
        ]);
    }

    private function failIfPackageIsAmbiguous(): void
    {
        $data = $this->getData();
        $speed = $data['package'] ?? null;
        $price = self::parsePrice($data['price'] ?? null);

        if (! is_numeric($speed) || (int) $speed < 1 || $price === null) {
            return;
        }

        if (Package::query()
            ->where('speed_mbps', (int) $speed)
            ->where('price', $price)
            ->exists()) {
            return;
        }

        if (Package::query()->where('speed_mbps', (int) $speed)->count() > 1) {
            throw new RowImportFailedException(__('customers.import.ambiguous_package'));
        }
    }

    private static function parsePrice(mixed $value): ?int
    {
        if (! is_string($value) && ! is_numeric($value)) {
            return null;
        }

        $value = preg_replace('/\s+/', '', trim((string) $value));
        $value = preg_replace('/\ARp\.?/i', '', $value);
        $value = preg_replace('/[,.]00\z/', '', $value);

        if (! preg_match('/\A(?:Rp\s*)?(?:\d+|\d{1,3}(?:,\d{3})+|\d{1,3}(?:\.\d{3})+)\z/i', $value)) {
            return null;
        }

        $hasThousandsSeparator = str_contains($value, ',') || str_contains($value, '.');
        $price = (int) preg_replace('/[,.]/', '', $value);

        if ($price < 1) {
            return null;
        }

        return ! $hasThousandsSeparator && $price < 1000
            ? $price * 1000
            : $price;
    }

    /**
     * @param  Collection<int, string>  $existingNames
     */
    private static function failIfSimilarName(string $name, Collection $existingNames): void
    {
        $normalizedName = self::normalizeName($name);

        foreach ($existingNames as $existingName) {
            if (self::areNamesSimilar($normalizedName, self::normalizeName($existingName))) {
                throw new RowImportFailedException(__('customers.import.similar_location', [
                    'name' => $name,
                    'similar' => $existingName,
                ]));
            }
        }
    }

    /**
     * @param  array<int, array<string, string>>  $rows
     * @param  array<string, string>  $columnMap
     * @return array<string, string>
     */
    private static function findSimilarIncomingLocations(array $rows, array $columnMap): array
    {
        $records = [];
        $locationGroups = [
            'district' => ['all' => []],
            'village' => [],
            'hamlet' => [],
        ];

        foreach ($rows as $record) {
            $records[] = [
                'fingerprint' => self::rowFingerprint($record),
                'district' => trim((string) ($record[$columnMap['district_name'] ?? ''] ?? '')),
                'village' => trim((string) ($record[$columnMap['village_name'] ?? ''] ?? '')),
                'hamlet' => trim((string) ($record[$columnMap['hamlet'] ?? ''] ?? '')),
            ];
        }

        foreach ($records as $record) {
            $district = self::normalizeName($record['district']);
            $village = self::normalizeName($record['village']);
            $hamlet = self::normalizeName($record['hamlet']);

            self::addLocationToGroup($locationGroups['district']['all'], $record['district'], $record['fingerprint']);

            if (filled($district)) {
                $locationGroups['village'][$district] ??= [];

                self::addLocationToGroup(
                    $locationGroups['village'][$district],
                    $record['village'],
                    $record['fingerprint'],
                );
            }
            if (filled($district) && filled($village)) {
                $parent = json_encode([$district, $village]);
                $locationGroups['hamlet'][$parent] ??= [];

                self::addLocationToGroup($locationGroups['hamlet'][$parent], $record['hamlet'], $record['fingerprint']);
            }
        }

        $failures = [];

        foreach ($locationGroups as $groups) {
            foreach ($groups as $group) {
                $names = array_keys($group);
                $namesCount = count($names);

                for ($firstIndex = 0; $firstIndex < $namesCount; $firstIndex++) {
                    for ($secondIndex = $firstIndex + 1; $secondIndex < $namesCount; $secondIndex++) {
                        $firstName = $names[$firstIndex];
                        $secondName = $names[$secondIndex];

                        if (! self::areNamesSimilar($firstName, $secondName)) {
                            continue;
                        }

                        foreach ($group[$firstName]['rows'] as $fingerprint) {
                            $failures[$fingerprint] = __('customers.import.similar_location', [
                                'name' => $group[$firstName]['name'],
                                'similar' => $group[$secondName]['name'],
                            ]);
                        }

                        foreach ($group[$secondName]['rows'] as $fingerprint) {
                            $failures[$fingerprint] = __('customers.import.similar_location', [
                                'name' => $group[$secondName]['name'],
                                'similar' => $group[$firstName]['name'],
                            ]);
                        }
                    }
                }
            }
        }

        return $failures;
    }

    private static function areNamesSimilar(string $firstName, string $secondName): bool
    {
        similar_text($firstName, $secondName, $similarity);

        if ($similarity >= self::SIMILARITY_WARNING_THRESHOLD) {
            return true;
        }

        $firstCharacters = mb_str_split($firstName);
        $secondCharacters = mb_str_split($secondName);
        $firstLength = count($firstCharacters);
        $secondLength = count($secondCharacters);

        if (abs($firstLength - $secondLength) > 1) {
            return false;
        }

        $differences = 0;
        $firstIndex = 0;
        $secondIndex = 0;

        while ($firstIndex < $firstLength && $secondIndex < $secondLength) {
            if ($firstCharacters[$firstIndex] === $secondCharacters[$secondIndex]) {
                $firstIndex++;
                $secondIndex++;

                continue;
            }

            $differences++;

            if ($differences > 1) {
                return false;
            }

            if ($firstLength > $secondLength) {
                $firstIndex++;
            } elseif ($secondLength > $firstLength) {
                $secondIndex++;
            } else {
                $firstIndex++;
                $secondIndex++;
            }
        }

        return $differences + (int) ($firstIndex < $firstLength || $secondIndex < $secondLength) <= 1;
    }

    /**
     * @param  array<int, array<string, string>>  $records
     * @param  array<string, string>  $columnMap
     * @return array<string, true>
     */
    private static function findDuplicateIncomingCodes(array $records, array $columnMap): array
    {
        $codes = [];

        foreach ($records as $record) {
            $code = Str::upper(trim((string) ($record[$columnMap['code'] ?? ''] ?? '')));

            if (blank($code)) {
                continue;
            }

            $codes[$code][] = self::rowFingerprint($record);
        }

        $duplicates = [];

        foreach ($codes as $fingerprints) {
            if (count($fingerprints) < 2) {
                continue;
            }

            foreach ($fingerprints as $fingerprint) {
                $duplicates[$fingerprint] = true;
            }
        }

        return $duplicates;
    }

    /**
     * @return array<int, array<string, string>>
     */
    private static function recordsForFile(string $filePath, string $fileName, ?string $worksheet): array
    {
        $stream = CustomerImportFileReader::isXlsx($fileName)
            ? CustomerImportFileReader::getCsvStream($filePath, $worksheet)
            : fopen($filePath, 'r');

        if (! is_resource($stream)) {
            throw new \RuntimeException('Unable to read the customer import file.');
        }

        try {
            $reader = Reader::from($stream);
            $delimiterCounts = Info::getDelimiterStats($reader, delimiters: [',', ';', '|', "\t"], limit: 10);
            $reader->setDelimiter(array_search(max($delimiterCounts), $delimiterCounts));
            $reader->setHeaderOffset(0);

            return iterator_to_array($reader->getRecords(), false);
        } finally {
            fclose($stream);
        }
    }

    /**
     * @param  array<string, array{name: string, rows: array<int, string>}>  $group
     */
    private static function addLocationToGroup(array &$group, string $name, string $fingerprint): void
    {
        $normalizedName = self::normalizeName($name);

        if (blank($normalizedName)) {
            return;
        }

        $group[$normalizedName] ??= [
            'name' => $name,
            'rows' => [],
        ];
        $group[$normalizedName]['rows'][] = $fingerprint;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private static function rowFingerprint(array $data): string
    {
        ksort($data);

        return hash('sha256', serialize($data));
    }

    private static function normalizeName(string $name): string
    {
        return Str::lower(preg_replace('/\s+/u', '', trim($name)) ?? '');
    }
}
