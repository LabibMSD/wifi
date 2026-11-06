<?php

use App\Filament\Exports\CustomerExporter;
use App\Filament\Imports\CustomerImporter;
use App\Filament\Resources\Customers\Pages\ListCustomers;
use App\Models\Customer;
use App\Models\District;
use App\Models\Hamlet;
use App\Models\Package;
use App\Models\PrefixArea;
use App\Models\User;
use App\Models\Village;
use Filament\Actions\ExportAction;
use Filament\Actions\Exports\Enums\ExportFormat;
use Filament\Actions\Exports\Models\Export;
use Filament\Forms\Components\Select;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Livewire\Livewire;

uses(LazilyRefreshDatabase::class);

it('exports customer data in a format that can be imported again', function () {
    $district = District::factory()->create(['name' => 'SANGKAPURA']);
    $prefixArea = PrefixArea::factory()->create(['prefix' => 'SK']);
    $village = Village::factory()
        ->for($district)
        ->for($prefixArea)
        ->create(['name' => 'SAWAH MULYA']);
    $hamlet = Hamlet::factory()
        ->for($village)
        ->create(['name' => 'DAYA BATA']);
    $package = Package::factory()->create([
        'speed_mbps' => 5,
        'price' => 175000,
    ]);
    $customer = Customer::factory()
        ->for($hamlet)
        ->for($package)
        ->create(['name' => 'TRI FIRDIYANTI']);
    $formulaCustomer = Customer::factory()
        ->for($hamlet)
        ->for($package)
        ->create(['name' => '=1+1']);

    $columns = CustomerExporter::getColumns();
    $headers = array_map(fn ($column): ?string => $column->getLabel(), $columns);
    $values = CustomerExporter::test()->export($customer);
    $formulaValues = CustomerExporter::test()->export($formulaCustomer);

    expect($headers)->toBe(['NAMA', 'ID', 'DUSUN', 'DESA', 'KECAMATAN', 'PAKET', 'HARGA', 'SYSTEM']);
    expect(CustomerImporter::guessColumnMap($headers))->toBe([
        'code' => 'ID',
        'name' => 'NAMA',
        'hamlet' => 'DUSUN',
        'package' => 'PAKET',
        'village_name' => 'DESA',
        'district_name' => 'KECAMATAN',
        'price' => 'HARGA',
        'system' => 'SYSTEM',
    ]);
    expect($values)->toBe([
        'TRI FIRDIYANTI',
        $customer->code,
        'DAYA BATA',
        'SAWAH MULYA',
        'SANGKAPURA',
        '5',
        '175000',
        Customer::SYSTEM,
    ]);
    expect($formulaValues[0])->toBe("'=1+1");
});

it('exports customer records as xlsx from the customer list', function () {
    $this->actingAs(User::factory()->create());

    $exporter = new CustomerExporter(new Export, [], []);
    $export = new Export;
    $export->id = 42;

    expect($exporter->getFormats())->toBe([ExportFormat::Xlsx]);
    expect($exporter->getFileName($export))->toBe(__('customers.export.file_name', [
        'export_id' => 42,
    ]));

    Livewire::test(ListCustomers::class)
        ->assertActionExists('export')
        ->mountAction('export')
        ->assertActionMounted('export')
        ->assertSchemaComponentExists(
            'district_ids',
            checkComponentUsing: fn (Select $component): bool => $component->getPlaceholder() === __('customers.export.filters.placeholder'),
        )
        ->assertSchemaComponentExists(
            'village_ids',
            checkComponentUsing: fn (Select $component): bool => $component->getPlaceholder() === __('customers.export.filters.placeholder'),
        )
        ->assertSchemaComponentExists(
            'hamlet_ids',
            checkComponentUsing: fn (Select $component): bool => $component->getPlaceholder() === __('customers.export.filters.placeholder'),
        )
        ->assertSchemaComponentExists(
            'prefix_area_ids',
            checkComponentUsing: fn (Select $component): bool => $component->getPlaceholder() === __('customers.export.filters.placeholder'),
        )
        ->assertSchemaComponentExists(
            'package_ids',
            checkComponentUsing: fn (Select $component): bool => $component->getPlaceholder() === __('customers.export.filters.placeholder'),
        );
});

it('filters customer exports by selected location and prefix options', function () {
    $district = District::factory()->create(['name' => 'SANGKAPURA']);
    $otherDistrict = District::factory()->create(['name' => 'TAMBAK']);
    $prefix = PrefixArea::factory()->create(['prefix' => 'SK']);
    $otherPrefix = PrefixArea::factory()->create(['prefix' => 'TB']);
    $village = Village::factory()->for($district)->for($prefix)->create(['name' => 'SAWAH MULYA']);
    $unselectedVillage = Village::factory()->for($district)->for($prefix)->create(['name' => 'KOTA KUSUMA']);
    $otherPrefixVillage = Village::factory()->for($district)->for($otherPrefix)->create(['name' => 'SUNGAI TELUK']);
    $otherDistrictVillage = Village::factory()->for($otherDistrict)->for($prefix)->create(['name' => 'TANJUNG ORI']);
    $hamlet = Hamlet::factory()->for($village)->create(['name' => 'DAYA BATA']);
    $unselectedHamlet = Hamlet::factory()->for($village)->create(['name' => 'DAYA BARU']);
    $unselectedVillageHamlet = Hamlet::factory()->for($unselectedVillage)->create(['name' => 'KOTA BARU']);
    $otherPrefixHamlet = Hamlet::factory()->for($otherPrefixVillage)->create(['name' => 'SUNGAI BARU']);
    $otherDistrictHamlet = Hamlet::factory()->for($otherDistrictVillage)->create(['name' => 'TANJUNG BARU']);
    $package = Package::factory()->create(['speed_mbps' => 5, 'price' => 175000]);
    $otherPackage = Package::factory()->create(['speed_mbps' => 10, 'price' => 250000]);
    $includedCustomer = Customer::factory()->for($hamlet)->for($package)->create(['name' => 'Included']);
    Customer::factory()->for($hamlet)->for($otherPackage)->create(['name' => 'Other package']);
    Customer::factory()->for($unselectedHamlet)->for($package)->create(['name' => 'Unselected hamlet']);
    Customer::factory()->for($unselectedVillageHamlet)->for($package)->create(['name' => 'Unselected village']);
    Customer::factory()->for($otherPrefixHamlet)->for($package)->create(['name' => 'Other prefix']);
    Customer::factory()->for($otherDistrictHamlet)->for($package)->create(['name' => 'Other district']);
    $this->actingAs(User::factory()->create());

    $exportFake = ExportAction::fake();

    Livewire::test(ListCustomers::class)
        ->callAction('export', [
            'district_ids' => [$district->id],
            'village_ids' => [$village->id, $otherPrefixVillage->id],
            'hamlet_ids' => [
                $hamlet->id,
                $otherPrefixHamlet->id,
            ],
            'prefix_area_ids' => [$prefix->id],
            'package_ids' => [$package->id],
        ]);

    $exportFake->assertDispatched(
        CustomerExporter::class,
        fn (Export $export, Builder $query, array $columnMap, array $options): bool => $query
            ->orderBy('id')
            ->pluck('id')
            ->all() === [$includedCustomer->id]
            && $options['district_ids'] === [$district->id]
            && $options['village_ids'] === [$village->id, $otherPrefixVillage->id]
            && $options['hamlet_ids'] === [$hamlet->id, $otherPrefixHamlet->id]
            && $options['prefix_area_ids'] === [$prefix->id]
            && $options['package_ids'] === [$package->id],
    );
});

it('exports all customers when no location filters are selected', function () {
    $district = District::factory()->create(['name' => 'SANGKAPURA']);
    $prefix = PrefixArea::factory()->create(['prefix' => 'SK']);
    $village = Village::factory()->for($district)->for($prefix)->create(['name' => 'SAWAH MULYA']);
    $hamlet = Hamlet::factory()->for($village)->create(['name' => 'DAYA BATA']);
    $package = Package::factory()->create(['speed_mbps' => 5, 'price' => 175000]);
    $customer = Customer::factory()->for($hamlet)->for($package)->create(['name' => 'Included']);
    $this->actingAs(User::factory()->create());

    $exportFake = ExportAction::fake();

    Livewire::test(ListCustomers::class)
        ->callAction('export');

    $exportFake->assertDispatched(
        CustomerExporter::class,
        fn (Export $export, Builder $query): bool => $query->pluck('id')->all() === [$customer->id],
    );
});
