<?php

return [
    'labels' => [
        'model' => 'Customer',
        'plural_model' => 'Customers',
    ],
    'fields' => [
        'code' => 'Code',
        'name' => 'Name',
        'district' => 'District',
        'village' => 'Village',
        'hamlet' => 'Hamlet',
        'package' => 'Package',
        'price' => 'Price',
        'system' => 'System',
    ],
    'import' => [
        'completed' => 'Imported: :imported; failed: :failed.',
        'ambiguous_package' => 'Multiple packages have the same speed but different prices. Confirm the package price before importing.',
        'duplicate_code' => 'The customer ID is already in use.',
        'download_failed_rows_csv' => 'Download CSV report to fix :count failed rows',
        'download_failed_rows_xlsx' => 'Download Excel to fix :count failed rows',
        'failure_reason_header' => 'FAILURE REASON',
        'failure_file_name' => 'customer-import-failed-rows-:import_id.:format',
        'file_help' => 'Choose a .xlsx Excel file or .csv file. Legacy .xls files are not supported; save them as .xlsx first. The first row must contain the column headers.',
        'file_label' => 'Customer data file',
        'file_placeholder' => 'Drag an .xlsx or .csv file here, or click to choose a file',
        'invalid_location' => 'The district, village, or hamlet could not be resolved.',
        'invalid_package' => 'The package speed or price is invalid.',
        'invalid_price' => 'The price format is invalid. Whole numbers below 1000 are treated as thousands of rupiah (200 means Rp200,000). Full amounts can be written as 200000, Rp200,000, or Rp200.000.',
        'invalid_system' => 'The system must be PPOE.',
        'invalid_text_value' => 'The value must contain at least one letter or number, not only punctuation.',
        'similar_location' => 'Warning: ":name" is similar to existing data ":similar". Check the district, village, or hamlet name in this row.',
        'worksheet' => 'Worksheet',
    ],
    'export' => [
        'completed' => 'Export completed. Rows exported: :exported. Rows failed: :failed.',
        'file_name' => 'customer-data-:export_id',
        'filters' => [
            'heading' => 'Choose which customers to export',
            'help' => 'Choose the areas or packages to export. Leave a selection empty to include all data in that category. If every selection is empty, all customers matching the current table filters will be exported.',
            'placeholder' => 'Search and choose, or leave empty for all',
            'prefix' => 'Area prefix',
        ],
    ],
    'spreadsheet' => [
        'sheet_name' => 'Customer Data',
    ],
];
