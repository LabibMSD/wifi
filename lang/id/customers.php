<?php

return [
    'labels' => [
        'model' => 'Pelanggan',
        'plural_model' => 'Pelanggan',
    ],
    'fields' => [
        'code' => 'Kode',
        'name' => 'Nama',
        'district' => 'Kecamatan',
        'village' => 'Desa',
        'hamlet' => 'Dusun',
        'package' => 'Paket',
        'price' => 'Harga',
        'system' => 'Sistem',
    ],
    'import' => [
        'completed' => 'Berhasil diimpor: :imported; gagal: :failed.',
        'ambiguous_package' => 'Ada beberapa paket dengan kecepatan yang sama tetapi harga berbeda. Pastikan harga paket sesuai sebelum mengimpor.',
        'duplicate_code' => 'ID pelanggan sudah digunakan.',
        'download_failed_rows_csv' => 'Unduh laporan CSV untuk memperbaiki :count baris gagal',
        'download_failed_rows_xlsx' => 'Unduh Excel untuk memperbaiki :count baris gagal',
        'failure_reason_header' => 'ALASAN GAGAL',
        'failure_file_name' => 'baris-gagal-impor-pelanggan-:import_id.:format',
        'file_help' => 'Pilih file Excel .xlsx atau .csv. File Excel lama .xls belum didukung; simpan ulang sebagai .xlsx. Baris pertama harus berisi nama kolom.',
        'file_label' => 'File data pelanggan',
        'file_placeholder' => 'Seret file Excel .xlsx atau .csv ke sini, atau klik untuk memilih file',
        'invalid_location' => 'Kecamatan, desa, atau dusun tidak dapat diproses.',
        'invalid_package' => 'Kecepatan atau harga paket tidak valid.',
        'invalid_price' => 'Format harga tidak valid. Angka bulat di bawah 1000 dianggap ribuan rupiah (200 berarti Rp200.000). Harga penuh dapat ditulis 200000, Rp200,000, atau Rp200.000.',
        'invalid_system' => 'Sistem harus PPOE.',
        'invalid_text_value' => 'Nilai harus berisi setidaknya satu huruf atau angka, bukan hanya tanda baca.',
        'similar_location' => 'Peringatan: nama ":name" mirip dengan data ":similar". Periksa nama kecamatan, desa, atau dusun pada baris ini.',
        'worksheet' => 'Lembar kerja',
    ],
    'export' => [
        'completed' => 'Ekspor selesai. Baris berhasil diekspor: :exported. Baris gagal: :failed.',
        'file_name' => 'data-pelanggan-:export_id',
        'filters' => [
            'heading' => 'Pilih pelanggan yang akan diekspor',
            'help' => 'Pilih wilayah atau paket yang ingin diekspor. Kosongkan pilihan agar semua data pada bagian itu ikut. Jika semua pilihan kosong, semua pelanggan sesuai filter tabel saat ini akan diekspor.',
            'placeholder' => 'Cari dan pilih, atau kosongkan untuk semua',
            'prefix' => 'Prefix area',
        ],
    ],
];
