<?php

use App\Filament\Imports\CustomerImportAction;
use App\Filament\Imports\CustomerImporter;
use App\Filament\Imports\CustomerImportFileReader;
use App\Filament\Resources\Customers\Pages\ListCustomers;
use App\Models\Customer;
use App\Models\District;
use App\Models\Hamlet;
use App\Models\Package;
use App\Models\PrefixArea;
use App\Models\User;
use App\Models\Village;
use Filament\Actions\Imports\Models\Import;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Hatchyu\Sequence\Models\Sequence;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use League\Csv\Reader;
use Livewire\Livewire;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Writer as XlsxWriter;

uses(LazilyRefreshDatabase::class);

const CUSTOMER_IMPORT_COLUMN_MAP = [
    'code' => 'ID',
    'name' => 'NAMA',
    'hamlet' => 'DUSUN',
    'package' => 'PAKET',
    'village_name' => 'DESA',
    'district_name' => 'KECAMATAN',
    'price' => 'HARGA',
    'system' => 'SYSTEM',
];

const CUSTOMER_IMPORT_ROW = [
    'NAMA' => 'TRI FIRDIYANTI',
    'ID' => 'SK00006',
    'DUSUN' => 'DAYA BATA',
    'DESA' => 'SAWAH MULYA',
    'KECAMATAN' => 'SANGKAPURA',
    'PAKET' => '5 MBPS',
    'HARGA' => ' Rp175,000 ',
    'SYSTEM' => 'PPOE',
];

/**
 * @param  array<string, array<int, array<int, mixed>>>  $sheets
 */
function createCustomerWorkbook(array $sheets): string
{
    $temporaryPath = tempnam(sys_get_temp_dir(), 'customer-import');
    $filePath = $temporaryPath.'.xlsx';
    rename($temporaryPath, $filePath);

    $writer = new XlsxWriter;
    $writer->openToFile($filePath);

    foreach ($sheets as $index => $rows) {
        $sheet = $index === array_key_first($sheets)
            ? $writer->getCurrentSheet()
            : $writer->addNewSheetAndMakeItCurrent();
        $sheet->setName($index);

        foreach ($rows as $row) {
            $writer->addRow(Row::fromValues($row));
        }
    }

    $writer->close();

    return $filePath;
}

it('imports a customer from the customer CSV columns', function () {
    $district = District::factory()->create(['name' => 'SANGKAPURA']);
    $prefixArea = PrefixArea::factory()->create(['prefix' => 'SK']);
    $otherVillage = Village::factory()->for($district)->for($prefixArea)->create(['name' => 'KOTA KUSUMA']);
    Hamlet::factory()->for($otherVillage)->create(['name' => 'DAYA BATA']);
    $targetVillage = Village::factory()->for($district)->for($prefixArea)->create(['name' => 'SAWAH MULYA']);
    $targetHamlet = Hamlet::factory()->for($targetVillage)->create(['name' => 'DAYA BATA']);
    Package::factory()->create([
        'speed_mbps' => 5,
        'price' => 200000,
    ]);
    $package = Package::factory()->create([
        'speed_mbps' => 5,
        'price' => 175000,
    ]);
    Sequence::query()->create([
        'name' => 'customers.code',
        'group_by' => '["SK"]',
        'last_number' => 1,
    ]);

    CustomerImporter::test(CUSTOMER_IMPORT_COLUMN_MAP)
        ->import(CUSTOMER_IMPORT_ROW)
        ->assertImported();

    $this->assertDatabaseHas('customers', [
        'code' => 'SK00006',
        'name' => 'TRI FIRDIYANTI',
        'hamlet_id' => $targetHamlet->id,
        'package_id' => $package->id,
    ]);
});

it('imports customer data when the code column is named KODE', function () {
    $row = array_replace(CUSTOMER_IMPORT_ROW, ['KODE' => 'SK00006']);
    unset($row['ID']);

    CustomerImporter::test(CustomerImporter::guessColumnMap(array_keys($row)))
        ->import($row)
        ->assertImported();

    $this->assertDatabaseHas('customers', [
        'code' => 'SK00006',
        'name' => 'TRI FIRDIYANTI',
    ]);
});

it('reads the selected Excel worksheet headers and rows', function () {
    $headers = array_keys(CUSTOMER_IMPORT_ROW);
    $secondRow = array_values(array_replace(CUSTOMER_IMPORT_ROW, ['NAMA' => 'Second customer']));

    $filePath = createCustomerWorkbook([
        'Notes' => [['Not customer data']],
        'Customers' => [$headers, array_values(CUSTOMER_IMPORT_ROW), array_slice($secondRow, 0, -1)],
    ]);

    try {
        expect(CustomerImportFileReader::getSheetOptions($filePath))->toBe([
            'Notes' => 'Notes',
            'Customers' => 'Customers',
        ]);
        expect(CustomerImportFileReader::getHeaders($filePath, 'Customers'))->toBe($headers);
        expect(CustomerImporter::guessColumnMap($headers))->toBe(CUSTOMER_IMPORT_COLUMN_MAP);

        $stream = CustomerImportFileReader::getCsvStream($filePath, 'Customers');

        try {
            $reader = Reader::from($stream);
            $reader->setHeaderOffset(0);

            expect(iterator_to_array($reader->getRecords(), false))->toBe([
                CUSTOMER_IMPORT_ROW,
                array_replace(CUSTOMER_IMPORT_ROW, ['NAMA' => 'Second customer', 'SYSTEM' => '']),
            ]);
        } finally {
            fclose($stream);
        }
    } finally {
        unlink($filePath);
    }
});

it('allows xlsx files in the customer import action', function () {
    $action = CustomerImportAction::make()
        ->importer(CustomerImporter::class);

    expect($action->getFileValidationRules())->toContain('extensions:csv,txt,xlsx');
});

it('opens the customer import modal with worksheet support', function () {
    $this->actingAs(User::factory()->create());

    Livewire::test(ListCustomers::class)
        ->assertActionExists('import')
        ->mountAction('import')
        ->assertActionMounted('import')
        ->assertSchemaComponentExists(
            'file',
            checkComponentUsing: fn (FileUpload $component): bool => $component->getPlaceholder() === __('customers.import.file_placeholder'),
        );
});

it('rejects similar names in the selected Excel worksheet before creating records', function () {
    $headers = array_keys(CUSTOMER_IMPORT_ROW);
    $rows = [
        array_replace(CUSTOMER_IMPORT_ROW, ['KECAMATAN' => 'GREJEG']),
        array_replace(CUSTOMER_IMPORT_ROW, ['ID' => 'SK00007', 'KECAMATAN' => 'GREJEK']),
    ];
    $filePath = createCustomerWorkbook([
        'Notes' => [['NAMA', 'ID'], ['Unrelated sheet', 'SK99999']],
        'Customers' => [
            $headers,
            array_values($rows[0]),
            array_values($rows[1]),
        ],
    ]);
    $import = new Import;
    $import->file_name = 'customers.xlsx';
    $import->file_path = $filePath;

    try {
        CustomerImporter::test(
            CUSTOMER_IMPORT_COLUMN_MAP,
            options: ['worksheet' => 'Customers'],
            import: $import,
        )
            ->import($rows[0])
            ->assertHasRowFailure(__('customers.import.similar_location', [
                'name' => 'GREJEG',
                'similar' => 'GREJEK',
            ]));

        CustomerImporter::test(
            CUSTOMER_IMPORT_COLUMN_MAP,
            options: ['worksheet' => 'Customers'],
            import: $import,
        )
            ->import($rows[1])
            ->assertHasRowFailure(__('customers.import.similar_location', [
                'name' => 'GREJEK',
                'similar' => 'GREJEG',
            ]));
    } finally {
        unlink($filePath);
    }

    $this->assertDatabaseCount('customers', 0);
    $this->assertDatabaseCount('districts', 0);
    $this->assertDatabaseCount('villages', 0);
    $this->assertDatabaseCount('hamlets', 0);
    $this->assertDatabaseCount('packages', 0);
    $this->assertDatabaseCount('prefix_areas', 0);
});

it('rejects every duplicate customer code in the selected Excel worksheet', function () {
    $headers = array_keys(CUSTOMER_IMPORT_ROW);
    $rows = [
        CUSTOMER_IMPORT_ROW,
        array_replace(CUSTOMER_IMPORT_ROW, ['NAMA' => 'Second customer']),
    ];
    $filePath = createCustomerWorkbook([
        'Customers' => [
            $headers,
            array_values($rows[0]),
            array_values($rows[1]),
        ],
    ]);
    $import = new Import;
    $import->file_name = 'customers.xlsx';
    $import->file_path = $filePath;

    try {
        foreach ($rows as $row) {
            CustomerImporter::test(
                CUSTOMER_IMPORT_COLUMN_MAP,
                options: ['worksheet' => 'Customers'],
                import: $import,
            )
                ->import($row)
                ->assertHasRowFailure(__('customers.import.duplicate_code'));
        }
    } finally {
        unlink($filePath);
    }

    $this->assertDatabaseCount('customers', 0);
    $this->assertDatabaseCount('districts', 0);
    $this->assertDatabaseCount('villages', 0);
    $this->assertDatabaseCount('hamlets', 0);
    $this->assertDatabaseCount('packages', 0);
    $this->assertDatabaseCount('prefix_areas', 0);
});

it('ignores similar location names in Excel worksheets that were not selected', function () {
    $headers = array_keys(CUSTOMER_IMPORT_ROW);
    $filePath = createCustomerWorkbook([
        'Notes' => [
            $headers,
            array_values(array_replace(CUSTOMER_IMPORT_ROW, ['KECAMATAN' => 'GREJEG'])),
            array_values(array_replace(CUSTOMER_IMPORT_ROW, ['ID' => 'SK00007', 'KECAMATAN' => 'GREJEK'])),
        ],
        'Customers' => [$headers, array_values(CUSTOMER_IMPORT_ROW)],
    ]);
    $import = new Import;
    $import->file_name = 'customers.xlsx';
    $import->file_path = $filePath;

    try {
        CustomerImporter::test(
            CUSTOMER_IMPORT_COLUMN_MAP,
            options: ['worksheet' => 'Customers'],
            import: $import,
        )
            ->import(CUSTOMER_IMPORT_ROW)
            ->assertImported();
    } finally {
        unlink($filePath);
    }

    $this->assertDatabaseCount('customers', 1);
    $this->assertDatabaseHas('customers', [
        'code' => 'SK00006',
        'name' => 'TRI FIRDIYANTI',
    ]);
});

it('continues the customer sequence after importing a code with the same prefix', function () {
    $district = District::factory()->create(['name' => 'SANGKAPURA']);
    $prefixArea = PrefixArea::factory()->create(['prefix' => 'SK']);
    $village = Village::factory()->for($district)->for($prefixArea)->create(['name' => 'SAWAH MULYA']);
    $hamlet = Hamlet::factory()->for($village)->create(['name' => 'DAYA BATA']);
    $package = Package::factory()->create([
        'speed_mbps' => 5,
        'price' => 175000,
    ]);

    CustomerImporter::test(CUSTOMER_IMPORT_COLUMN_MAP)
        ->import(CUSTOMER_IMPORT_ROW)
        ->assertImported();

    Customer::factory()->for($hamlet)->for($package)->create(['name' => 'New customer']);

    $this->assertDatabaseHas('customers', [
        'code' => 'SK00007',
        'name' => 'New customer',
    ]);
});

it('reuses an existing package when the imported speed matches but the price differs', function () {
    $package = Package::factory()->create([
        'speed_mbps' => 5,
        'price' => 200000,
    ]);

    CustomerImporter::test(CUSTOMER_IMPORT_COLUMN_MAP)
        ->import(CUSTOMER_IMPORT_ROW)
        ->assertImported();

    $this->assertDatabaseCount('packages', 1);
    $this->assertDatabaseHas('customers', [
        'name' => 'TRI FIRDIYANTI',
        'package_id' => $package->id,
    ]);
});

it('rejects every row with a customer ID repeated in the uploaded file', function () {
    $rows = [
        CUSTOMER_IMPORT_ROW,
        array_replace(CUSTOMER_IMPORT_ROW, ['NAMA' => 'Second customer']),
    ];
    $csvPath = tempnam(sys_get_temp_dir(), 'customer-import');
    file_put_contents($csvPath, "NAMA;ID;DUSUN;DESA;KECAMATAN;PAKET;HARGA;SYSTEM\n");

    foreach ($rows as $row) {
        file_put_contents($csvPath, implode(';', $row)."\n", FILE_APPEND);
    }

    $import = new Import;
    $import->file_path = $csvPath;

    try {
        foreach ($rows as $row) {
            CustomerImporter::test(CUSTOMER_IMPORT_COLUMN_MAP, import: $import)
                ->import($row)
                ->assertHasRowFailure(__('customers.import.duplicate_code'));
        }
    } finally {
        unlink($csvPath);
    }

    $this->assertDatabaseCount('customers', 0);
    $this->assertDatabaseCount('districts', 0);
    $this->assertDatabaseCount('packages', 0);
});

it('accepts a customer ID that differs from its village prefix', function () {
    $district = District::factory()->create(['name' => 'SANGKAPURA']);
    $prefixArea = PrefixArea::factory()->create(['prefix' => 'TB']);
    Village::factory()->for($district)->for($prefixArea)->create(['name' => 'SAWAH MULYA']);

    CustomerImporter::test(CUSTOMER_IMPORT_COLUMN_MAP)
        ->import(CUSTOMER_IMPORT_ROW)
        ->assertImported();

    $this->assertDatabaseHas('customers', ['code' => 'SK00006']);
    $this->assertDatabaseCount('hamlets', 1);
    $this->assertDatabaseCount('packages', 1);
});

it('rejects an import when multiple packages match the speed but not the price', function () {
    Package::factory()->create(['speed_mbps' => 5, 'price' => 200000]);
    Package::factory()->create(['speed_mbps' => 5, 'price' => 250000]);

    CustomerImporter::test(CUSTOMER_IMPORT_COLUMN_MAP)
        ->import(CUSTOMER_IMPORT_ROW)
        ->assertHasRowFailure(__('customers.import.ambiguous_package'));

    $this->assertDatabaseCount('customers', 0);
    $this->assertDatabaseCount('packages', 2);
    $this->assertDatabaseCount('districts', 0);
});

it('rejects a customer ID that already exists', function () {
    $district = District::factory()->create();
    $prefixArea = PrefixArea::factory()->create();
    $village = Village::factory()->for($district)->for($prefixArea)->create();
    $hamlet = Hamlet::factory()->for($village)->create();
    $package = Package::factory()->create();
    Customer::factory()->for($hamlet)->for($package)->create([
        'code' => 'SK00006',
        'name' => 'Existing customer',
    ]);

    $import = CustomerImporter::test(CUSTOMER_IMPORT_COLUMN_MAP)
        ->import(CUSTOMER_IMPORT_ROW)
        ->assertHasErrors(['code' => 'unique']);

    $this->assertDatabaseCount('customers', 1);
    $this->assertDatabaseHas('customers', [
        'code' => 'SK00006',
        'name' => 'Existing customer',
    ]);
    expect($import->errors()->first('code'))->toBe(__('customers.import.duplicate_code'));
});

it('rejects a customer ID repeated in the same import', function () {
    CustomerImporter::test(CUSTOMER_IMPORT_COLUMN_MAP)
        ->import(CUSTOMER_IMPORT_ROW)
        ->assertImported();

    CustomerImporter::test(CUSTOMER_IMPORT_COLUMN_MAP)
        ->import(CUSTOMER_IMPORT_ROW)
        ->assertHasErrors(['code' => 'unique']);

    $this->assertDatabaseCount('customers', 1);
});

it('accepts a custom customer ID and trims surrounding whitespace', function () {
    CustomerImporter::test(CUSTOMER_IMPORT_COLUMN_MAP)
        ->import(array_replace(CUSTOMER_IMPORT_ROW, ['ID' => ' PLN-SUWARI ']))
        ->assertImported();

    $this->assertDatabaseHas('customers', ['code' => 'PLN-SUWARI']);
    $this->assertDatabaseHas('prefix_areas', ['prefix' => 'SA']);
});

it('accepts punctuation-only custom customer IDs', function () {
    CustomerImporter::test(CUSTOMER_IMPORT_COLUMN_MAP)
        ->import(array_replace(CUSTOMER_IMPORT_ROW, ['ID' => '-']))
        ->assertImported();

    $this->assertDatabaseHas('customers', ['code' => '-']);
});

it('rejects a customer ID containing only whitespace', function () {
    CustomerImporter::test(CUSTOMER_IMPORT_COLUMN_MAP)
        ->import(array_replace(CUSTOMER_IMPORT_ROW, ['ID' => '   ']))
        ->assertHasErrors(['code' => 'required']);

    $this->assertDatabaseCount('customers', 0);
});

it('accepts a customer ID sequence longer than five digits', function () {
    CustomerImporter::test(CUSTOMER_IMPORT_COLUMN_MAP)
        ->import(array_replace(CUSTOMER_IMPORT_ROW, ['ID' => 'SK100000']))
        ->assertImported();

    $this->assertDatabaseHas('customers', ['code' => 'SK100000']);
});

it('rejects every row with similar locations in the uploaded file before creating records', function () {
    $rows = [
        ['TRI 1', 'SK00006', 'DUSUN 1', 'DESA 1', 'SANGKAPURA', '5 MBPS', '175000', 'PPOE'],
        ['TRI 2', 'SK00007', 'DUSUN 2', 'DESA 2', 'SANGKAPURAA', '5 MBPS', '175000', 'PPOE'],
        ['TRI 3', 'SK00008', 'DUSUN SATU', 'SUNGAI TELUK', 'TAMBAK', '5 MBPS', '175000', 'PPOE'],
        ['TRI 4', 'SK00009', 'DUSUN DUA', 'SUNGA TELUK', 'TAMBAK', '5 MBPS', '175000', 'PPOE'],
        ['TRI 5', 'SK00010', 'PERIKANAN', 'SUNGAI TELUK', 'SUKAPURA', '5 MBPS', '175000', 'PPOE'],
        ['TRI 6', 'SK00011', 'PERIKANANAN', 'SUNGAI TELUK', 'SUKAPURA', '5 MBPS', '175000', 'PPOE'],
    ];
    $csvPath = tempnam(sys_get_temp_dir(), 'customer-import');
    file_put_contents($csvPath, "NAMA;ID;DUSUN;DESA;KECAMATAN;PAKET;HARGA;SYSTEM\n");

    foreach ($rows as $row) {
        file_put_contents($csvPath, implode(';', $row)."\n", FILE_APPEND);
    }

    $import = new Import;
    $import->file_path = $csvPath;

    try {
        foreach ([
            [$rows[0], 'SANGKAPURA', 'SANGKAPURAA'],
            [$rows[1], 'SANGKAPURAA', 'SANGKAPURA'],
            [$rows[2], 'SUNGAI TELUK', 'SUNGA TELUK'],
            [$rows[3], 'SUNGA TELUK', 'SUNGAI TELUK'],
            [$rows[4], 'PERIKANAN', 'PERIKANANAN'],
            [$rows[5], 'PERIKANANAN', 'PERIKANAN'],
        ] as [$row, $name, $similar]) {
            $data = array_combine(
                ['NAMA', 'ID', 'DUSUN', 'DESA', 'KECAMATAN', 'PAKET', 'HARGA', 'SYSTEM'],
                $row,
            );

            CustomerImporter::test(CUSTOMER_IMPORT_COLUMN_MAP, import: $import)
                ->import($data)
                ->assertHasRowFailure(__('customers.import.similar_location', [
                    'name' => $name,
                    'similar' => $similar,
                ]));
        }
    } finally {
        unlink($csvPath);
    }

    $this->assertDatabaseCount('customers', 0);
    $this->assertDatabaseCount('districts', 0);
    $this->assertDatabaseCount('villages', 0);
    $this->assertDatabaseCount('hamlets', 0);
    $this->assertDatabaseCount('packages', 0);
    $this->assertDatabaseCount('prefix_areas', 0);
});

it('rejects both rows when district names differ by one character', function () {
    District::factory()->create(['name' => 'GREJEG']);

    $rows = [
        array_replace(CUSTOMER_IMPORT_ROW, ['KECAMATAN' => 'GREJEG']),
        array_replace(CUSTOMER_IMPORT_ROW, ['ID' => 'SK00007', 'KECAMATAN' => 'GREJEK']),
    ];
    $csvPath = tempnam(sys_get_temp_dir(), 'customer-import');
    file_put_contents($csvPath, "NAMA;ID;DUSUN;DESA;KECAMATAN;PAKET;HARGA;SYSTEM\n");

    foreach ($rows as $row) {
        file_put_contents($csvPath, implode(';', $row)."\n", FILE_APPEND);
    }

    $import = new Import;
    $import->file_path = $csvPath;

    try {
        CustomerImporter::test(CUSTOMER_IMPORT_COLUMN_MAP, import: $import)
            ->import($rows[0])
            ->assertHasRowFailure(__('customers.import.similar_location', [
                'name' => 'GREJEG',
                'similar' => 'GREJEK',
            ]));

        CustomerImporter::test(CUSTOMER_IMPORT_COLUMN_MAP, import: $import)
            ->import($rows[1])
            ->assertHasRowFailure(__('customers.import.similar_location', [
                'name' => 'GREJEK',
                'similar' => 'GREJEG',
            ]));
    } finally {
        unlink($csvPath);
    }

    $this->assertDatabaseCount('customers', 0);
    $this->assertDatabaseCount('districts', 1);
    $this->assertDatabaseCount('villages', 0);
    $this->assertDatabaseCount('hamlets', 0);
    $this->assertDatabaseCount('packages', 0);
    $this->assertDatabaseCount('prefix_areas', 0);
});

it('rejects a district name that differs by one character from an existing district', function () {
    District::factory()->create(['name' => 'GREJEG']);

    CustomerImporter::test(CUSTOMER_IMPORT_COLUMN_MAP)
        ->import(array_replace(CUSTOMER_IMPORT_ROW, ['KECAMATAN' => 'GREJEK']))
        ->assertHasRowFailure(__('customers.import.similar_location', [
            'name' => 'GREJEK',
            'similar' => 'GREJEG',
        ]));

    $this->assertDatabaseCount('customers', 0);
    $this->assertDatabaseCount('districts', 1);
    $this->assertDatabaseCount('villages', 0);
    $this->assertDatabaseCount('hamlets', 0);
    $this->assertDatabaseCount('packages', 0);
});

it('downloads failed customer rows as an editable xlsx file by default', function () {
    $user = User::factory()->create();
    $import = Import::query()->create([
        'file_name' => 'customers.xlsx',
        'file_path' => 'imports/customers.xlsx',
        'importer' => CustomerImporter::class,
        'processed_rows' => 1,
        'total_rows' => 1,
        'successful_rows' => 0,
        'user_id' => $user->id,
    ]);
    $import->failedRows()->create([
        'data' => [
            'SYSTEM' => 'PPOE',
            'HARGA' => 'Rp175,000',
            'KECAMATAN' => 'SANGKAPURA',
            'PAKET' => '5 MBPS',
            'DESA' => 'SAWAH MULYA',
            'DUSUN' => 'DAYA BATA',
            'ID' => 'SK00006',
            'NAMA' => 'TRI FIRDIYANTI',
        ],
        'validation_error' => 'Pelanggan tidak dapat diimpor karena kode pelanggan sudah terdaftar di database.',
    ]);

    $response = CustomerImporter::getFailedRowsDownloader()($import);

    ob_start();
    $response->sendContent();
    $contents = ob_get_clean();

    $temporaryPath = tempnam(sys_get_temp_dir(), 'customer-failures');
    $filePath = $temporaryPath.'.xlsx';
    rename($temporaryPath, $filePath);

    try {
        file_put_contents($filePath, (string) $contents);

        expect($response->headers->get('Content-Type'))
            ->toBe('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        expect($response->headers->get('Content-Disposition'))->toContain(__('customers.import.failure_file_name', [
            'import_id' => $import->getKey(),
            'format' => 'xlsx',
        ]));
        expect(CustomerImportFileReader::getHeaders($filePath))->toBe([
            'KODE',
            'NAMA',
            'DUSUN',
            'DESA',
            'KECAMATAN',
            'PAKET',
            'HARGA',
            'SYSTEM',
            'ALASAN GAGAL',
        ]);
        expect(CustomerImporter::guessColumnMap(CustomerImportFileReader::getHeaders($filePath))['code'])->toBe('KODE');

        $archive = new ZipArchive;
        expect($archive->open($filePath))->toBeTrue();

        try {
            $worksheet = $archive->getFromName('xl/worksheets/sheet1.xml');
            $styles = $archive->getFromName('xl/styles.xml');
            preg_match('/<c r="A1" s="(\d+)"/', $worksheet, $firstHeaderCell);
            preg_match('/<c r="I1" s="(\d+)"/', $worksheet, $failureReasonHeaderCell);
            preg_match('/<cellXfs[^>]*>(.*?)<\/cellXfs>/s', $styles, $cellXfs);
            preg_match_all('/<xf\b[^>]*\/>|<xf\b[^>]*>.*?<\/xf>/s', $cellXfs[1], $cellStyles);
            preg_match_all('/<col min="(\d+)" max="(\d+)" width="([^"]+)"/', $worksheet, $columnWidths);

            expect($worksheet)->toContain('ySplit="1"');
            expect($worksheet)->toContain('showGridLines="true"');
            expect($worksheet)->toContain('ref="A1:I2"');
            expect($worksheet)->not->toContain(' ht="');
            expect($worksheet)->toContain('customHeight="0"');
            expect($worksheet)->toContain('<c r="G2"');
            expect($worksheet)->toContain('<v>175000</v>');
            expect($columnWidths[3])->toBe(['16', '40', '24', '24', '20', '16', '20', '16', '82']);
            expect((float) $columnWidths[3][8])->toBeGreaterThan(mb_strlen(
                'Pelanggan tidak dapat diimpor karena kode pelanggan sudah terdaftar di database.',
            ));
            expect($cellStyles[0][(int) $failureReasonHeaderCell[1]])
                ->toContain('horizontal="left"');
            expect($cellStyles[0][(int) $firstHeaderCell[1]])
                ->toContain('horizontal="center"');
            expect($styles)->toContain('D6DCE4');
            expect($styles)->toContain('FEF3C7');
            expect($styles)->toContain('style="thin"');
            expect($styles)->toContain('formatCode="&quot;Rp.&quot; #,##0"');
        } finally {
            $archive->close();
        }

        $stream = CustomerImportFileReader::getCsvStream($filePath);

        try {
            $reader = Reader::from($stream);
            $reader->setHeaderOffset(0);

            expect(array_values(iterator_to_array($reader->getRecords())))->toBe([[
                'KODE' => 'SK00006',
                'NAMA' => 'TRI FIRDIYANTI',
                'DUSUN' => 'DAYA BATA',
                'DESA' => 'SAWAH MULYA',
                'KECAMATAN' => 'SANGKAPURA',
                'PAKET' => '5 MBPS',
                'HARGA' => '175000',
                'SYSTEM' => 'PPOE',
                'ALASAN GAGAL' => 'Pelanggan tidak dapat diimpor karena kode pelanggan sudah terdaftar di database.',
            ]]);
        } finally {
            fclose($stream);
        }
    } finally {
        unlink($filePath);
    }
});

it('offers xlsx and csv download choices for failed customer imports', function () {
    $user = User::factory()->create();
    $import = Import::query()->create([
        'file_name' => 'customers.xlsx',
        'file_path' => 'imports/customers.xlsx',
        'importer' => CustomerImporter::class,
        'processed_rows' => 1,
        'total_rows' => 1,
        'successful_rows' => 0,
        'user_id' => $user->id,
    ]);
    $notification = CustomerImporter::modifyCompletedNotification(Notification::make(), $import);
    $actions = $notification->getActions();

    expect(count($actions))->toBe(2);

    foreach (['xlsx', 'csv'] as $index => $format) {
        parse_str((string) parse_url($actions[$index]->getUrl(), PHP_URL_QUERY), $query);

        expect($query['format'] ?? null)->toBe($format);
        expect($query['authGuard'] ?? null)->toBe(config('auth.defaults.guard'));
    }
});

it('downloads failed customer rows as a semicolon-delimited CSV with readable columns', function () {
    $user = User::factory()->create();
    $import = Import::query()->create([
        'file_name' => 'customers.csv',
        'file_path' => 'imports/customers.csv',
        'importer' => CustomerImporter::class,
        'processed_rows' => 1,
        'total_rows' => 1,
        'successful_rows' => 0,
        'user_id' => $user->id,
    ]);
    $import->failedRows()->create([
        'data' => [
            'SYSTEM' => 'PPOE',
            'HARGA' => 'Rp. 175.000',
            'KECAMATAN' => 'SANGKAPURA',
            'PAKET' => '5 MBPS',
            'DESA' => 'SAWAH MULYA',
            'DUSUN' => 'DAYA BATA',
            'ID' => 'SK00006',
            'NAMA' => 'TRI FIRDIYANTI',
        ],
        'validation_error' => 'ID pelanggan sudah digunakan.',
    ]);

    request()->query->set('format', 'csv');

    $response = CustomerImporter::getFailedRowsDownloader()($import);

    ob_start();
    $response->sendContent();
    $contents = ob_get_clean();

    $csv = Reader::createFromString($contents);
    $csv->setDelimiter(';');
    $csv->setHeaderOffset(0);
    $rows = array_values(iterator_to_array($csv->getRecords()));

    expect($response->headers->get('Content-Type'))->toBe('text/csv; charset=UTF-8');
    expect($response->headers->get('Content-Disposition'))->toContain(__('customers.import.failure_file_name', [
        'import_id' => $import->getKey(),
        'format' => 'csv',
    ]));
    expect(array_keys($rows[0]))->toBe([
        'KODE',
        'NAMA',
        'DUSUN',
        'DESA',
        'KECAMATAN',
        'PAKET',
        'HARGA',
        'SYSTEM',
        'ALASAN GAGAL',
    ]);
    expect($rows[0])->toBe([
        'KODE' => 'SK00006',
        'NAMA' => 'TRI FIRDIYANTI',
        'DUSUN' => 'DAYA BATA',
        'DESA' => 'SAWAH MULYA',
        'KECAMATAN' => 'SANGKAPURA',
        'PAKET' => '5 MBPS',
        'HARGA' => 'Rp. 175.000',
        'SYSTEM' => 'PPOE',
        'ALASAN GAGAL' => 'ID pelanggan sudah digunakan.',
    ]);
});

it('allows editing and reimporting a failed CSV while replacing the previous failure reason', function () {
    $user = User::factory()->create();
    $import = Import::query()->create([
        'file_name' => 'customers.csv',
        'file_path' => 'imports/customers.csv',
        'importer' => CustomerImporter::class,
        'processed_rows' => 1,
        'total_rows' => 1,
        'successful_rows' => 0,
        'user_id' => $user->id,
    ]);
    $import->failedRows()->create([
        'data' => [
            'NAMA' => 'TRI FIRDIYANTI',
            'ID' => 'SK00006',
            'DUSUN' => 'DAYA BATA',
            'DESA' => 'SAWAH MULYA',
            'KECAMATAN' => 'SANGKAPURA',
            'PAKET' => '5 MBPS',
            'HARGA' => '200',
            'SYSTEM' => 'IPOE',
            'ALASAN GAGAL' => 'Alasan gagal sebelumnya.',
        ],
        'validation_error' => __('customers.import.invalid_system'),
    ]);

    request()->query->set('format', 'csv');

    $response = CustomerImporter::getFailedRowsDownloader()($import);

    ob_start();
    $response->sendContent();
    $contents = ob_get_clean();

    $csv = Reader::createFromString($contents);
    $csv->setDelimiter(';');
    $csv->setHeaderOffset(0);
    $rows = array_values(iterator_to_array($csv->getRecords()));

    expect(array_keys($rows[0]))->toBe([
        'KODE',
        'NAMA',
        'DUSUN',
        'DESA',
        'KECAMATAN',
        'PAKET',
        'HARGA',
        'SYSTEM',
        'ALASAN GAGAL',
    ]);
    expect($rows[0]['ALASAN GAGAL'])->toBe(__('customers.import.invalid_system'));
    $rows[0]['SYSTEM'] = 'PPOE';

    CustomerImporter::test(array_replace(CUSTOMER_IMPORT_COLUMN_MAP, ['code' => 'KODE']))
        ->import($rows[0])
        ->assertImported();

    $this->assertDatabaseHas('packages', [
        'speed_mbps' => 5,
        'price' => 200000,
    ]);
    $this->assertDatabaseHas('customers', [
        'code' => 'SK00006',
        'name' => 'TRI FIRDIYANTI',
    ]);
});

it('rejects a package price that is not a positive amount', function () {
    CustomerImporter::test(CUSTOMER_IMPORT_COLUMN_MAP)
        ->import(array_replace(CUSTOMER_IMPORT_ROW, ['HARGA' => 'not a price']))
        ->assertHasErrors(['price']);

    $this->assertDatabaseCount('customers', 0);
    $this->assertDatabaseCount('packages', 0);
});

it('rejects a price that contains unrelated text even when it has digits', function () {
    CustomerImporter::test(CUSTOMER_IMPORT_COLUMN_MAP)
        ->import(array_replace(CUSTOMER_IMPORT_ROW, ['HARGA' => 'promo 175000']))
        ->assertHasErrors(['price']);

    $this->assertDatabaseCount('customers', 0);
    $this->assertDatabaseCount('packages', 0);
});

it('parses a rupiah price that uses a period as the thousands separator', function () {
    CustomerImporter::test(CUSTOMER_IMPORT_COLUMN_MAP)
        ->import(array_replace(CUSTOMER_IMPORT_ROW, ['HARGA' => 'Rp175.000']))
        ->assertImported();

    $this->assertDatabaseHas('packages', [
        'speed_mbps' => 5,
        'price' => 175000,
    ]);
});

it('treats a two-decimal Excel rupiah value under 1000 as thousands of rupiah', function () {
    CustomerImporter::test(CUSTOMER_IMPORT_COLUMN_MAP)
        ->import(array_replace(CUSTOMER_IMPORT_ROW, ['HARGA' => 'Rp200,00']))
        ->assertImported();

    $this->assertDatabaseHas('packages', [
        'speed_mbps' => 5,
        'price' => 200000,
    ]);
});

it('rejects a customer row when the system is not PPOE', function () {
    $import = CustomerImporter::test(CUSTOMER_IMPORT_COLUMN_MAP)
        ->import(array_replace(CUSTOMER_IMPORT_ROW, ['SYSTEM' => 'IPOE']))
        ->assertHasErrors(['system']);

    expect($import->errors()->first('system'))->toBe(__('customers.import.invalid_system'));
    $this->assertDatabaseCount('customers', 0);
    $this->assertDatabaseCount('packages', 0);
});

it('accepts PPOE in any letter case with surrounding spaces', function () {
    CustomerImporter::test(CUSTOMER_IMPORT_COLUMN_MAP)
        ->import(array_replace(CUSTOMER_IMPORT_ROW, ['SYSTEM' => ' ppoe ']))
        ->assertImported();

    $this->assertDatabaseHas('customers', ['name' => 'TRI FIRDIYANTI']);
});

it('rejects text fields containing only punctuation', function (string $field, string $header) {
    $import = CustomerImporter::test(CUSTOMER_IMPORT_COLUMN_MAP)
        ->import(array_replace(CUSTOMER_IMPORT_ROW, [$header => '-']))
        ->assertHasErrors([$field => 'regex']);

    expect($import->errors()->first($field))->toBe(__('customers.import.invalid_text_value'));
    $this->assertDatabaseCount('customers', 0);
    $this->assertDatabaseCount('packages', 0);
})->with([
    'customer name' => ['name', 'NAMA'],
    'hamlet name' => ['hamlet', 'DUSUN'],
    'village name' => ['village_name', 'DESA'],
    'district name' => ['district_name', 'KECAMATAN'],
    'system name' => ['system', 'SYSTEM'],
]);

it('rejects a package value containing only punctuation', function () {
    CustomerImporter::test(CUSTOMER_IMPORT_COLUMN_MAP)
        ->import(array_replace(CUSTOMER_IMPORT_ROW, ['PAKET' => '-']))
        ->assertHasErrors(['package']);

    $this->assertDatabaseCount('customers', 0);
    $this->assertDatabaseCount('packages', 0);
});

it('rejects the row when any customer CSV column is empty', function () {
    foreach (CUSTOMER_IMPORT_COLUMN_MAP as $field => $header) {
        CustomerImporter::test(CUSTOMER_IMPORT_COLUMN_MAP)
            ->import(array_replace(CUSTOMER_IMPORT_ROW, [$header => '']))
            ->assertHasErrors([$field => 'required']);
    }

    $this->assertDatabaseCount('customers', 0);
});

it('warns and rejects a row when its district name is similar to an existing one', function () {
    District::factory()->create(['name' => 'SANGKAPURA']);

    CustomerImporter::test(CUSTOMER_IMPORT_COLUMN_MAP)
        ->import(array_replace(CUSTOMER_IMPORT_ROW, ['KECAMATAN' => 'SANGKAPURAA']))
        ->assertHasRowFailure(__('customers.import.similar_location', [
            'name' => 'SANGKAPURAA',
            'similar' => 'SANGKAPURA',
        ]));

    $this->assertDatabaseCount('customers', 0);
    $this->assertDatabaseCount('districts', 1);
});

it('warns and rejects a row when its village name is similar to an existing one', function () {
    $district = District::factory()->create(['name' => 'SANGKAPURA']);
    $prefixArea = PrefixArea::factory()->create();
    Village::factory()->for($district)->for($prefixArea)->create(['name' => 'SUNGAI TELUK']);

    CustomerImporter::test(CUSTOMER_IMPORT_COLUMN_MAP)
        ->import(array_replace(CUSTOMER_IMPORT_ROW, ['DESA' => 'SUNGA TELUK']))
        ->assertHasRowFailure(__('customers.import.similar_location', [
            'name' => 'SUNGA TELUK',
            'similar' => 'SUNGAI TELUK',
        ]));

    $this->assertDatabaseCount('customers', 0);
    $this->assertDatabaseCount('villages', 1);
});

it('warns and rejects a row when its hamlet name is similar to an existing one', function () {
    $district = District::factory()->create(['name' => 'SANGKAPURA']);
    $prefixArea = PrefixArea::factory()->create(['prefix' => 'SK']);
    $village = Village::factory()->for($district)->for($prefixArea)->create(['name' => 'SUNGAI TELUK']);
    Hamlet::factory()->for($village)->create(['name' => 'PERIKANAN']);

    CustomerImporter::test(CUSTOMER_IMPORT_COLUMN_MAP)
        ->import(array_replace(CUSTOMER_IMPORT_ROW, [
            'DESA' => 'SUNGAI TELUK',
            'DUSUN' => 'PERIKANANAN',
        ]))
        ->assertHasRowFailure(__('customers.import.similar_location', [
            'name' => 'PERIKANANAN',
            'similar' => 'PERIKANAN',
        ]));

    $this->assertDatabaseCount('customers', 0);
    $this->assertDatabaseCount('hamlets', 1);
});

it('creates missing location and package records and reuses them on later rows', function () {
    $row = [
        'NAMA' => 'New customer',
        'ID' => 'TB00001',
        'DUSUN' => 'DUSUN BARU',
        'DESA' => 'DESA BARU',
        'KECAMATAN' => 'TAMBAK',
        'PAKET' => '25 MBPS',
        'HARGA' => ' Rp275,000 ',
        'SYSTEM' => 'PPOE',
    ];

    CustomerImporter::test(CUSTOMER_IMPORT_COLUMN_MAP)
        ->import($row)
        ->assertImported();

    CustomerImporter::test(CUSTOMER_IMPORT_COLUMN_MAP)
        ->import(array_replace($row, [
            'NAMA' => 'Second customer',
            'ID' => 'TB00002',
            'DUSUN' => 'DUSUNBARU',
            'DESA' => 'DESABARU',
        ]))
        ->assertImported();

    $customer = Customer::query()->where('name', 'New customer')->firstOrFail();

    $this->assertDatabaseCount('districts', 1);
    $this->assertDatabaseCount('villages', 1);
    $this->assertDatabaseCount('hamlets', 1);
    $this->assertDatabaseCount('packages', 1);
    $this->assertDatabaseHas('customers', [
        'name' => 'New customer',
        'hamlet_id' => $customer->hamlet_id,
        'package_id' => $customer->package_id,
    ]);
});
