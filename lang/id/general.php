<?php

return [
    'fields' => [
        'name' => 'Nama',
        'created_at' => 'Dibuat pada',
        'updated_at' => 'Diperbarui pada',
    ],
    'navigation' => [
        'region' => 'Wilayah',
        'subscription' => 'Langganan',
    ],
    'dashboard' => [
        'stats' => [
            'total_customers' => 'Total Pelanggan',
            'customers_description' => 'Semua pelanggan terdaftar',
            'total_villages' => 'Total Desa',
            'villages_description' => 'Desa yang aktif',
            'total_hamlets' => 'Total Dusun',
            'hamlets_description' => 'Dusun yang terdaftar',
            'total_packages' => 'Total Paket',
            'packages_description' => 'Paket layanan tersedia',
        ],
        'revenue_summary' => [
            'heading' => 'Estimasi Pendapatan Bulanan',
            'description' => 'Total pelanggan × harga per paket',
        ],
        'charts' => [
            'speed_label' => 'Kecepatan (Mbps)',
            'price_label' => 'Harga (Rp)',
            'customers_label' => 'Pelanggan',
        ],
    ],
];
