<?php

return [
    'fields' => [
        'name' => 'Name',
        'created_at' => 'Created at',
        'updated_at' => 'Updated at',
    ],
    'navigation' => [
        'region' => 'Region',
        'subscription' => 'Subscription',
    ],
    'dashboard' => [
        'stats' => [
            'total_customers' => 'Total Customers',
            'customers_description' => 'All registered customers',
            'total_villages' => 'Total Villages',
            'villages_description' => 'Active villages',
            'total_hamlets' => 'Total Hamlets',
            'hamlets_description' => 'Registered hamlets',
            'total_packages' => 'Total Packages',
            'packages_description' => 'Available service packages',
        ],
        'revenue_summary' => [
            'heading' => 'Estimated Monthly Revenue',
            'description' => 'Total customers × price per package',
        ],
        'charts' => [
            'speed_label' => 'Speed (Mbps)',
            'price_label' => 'Price (Rp)',
            'customers_label' => 'Customers',
        ],
    ],
];
