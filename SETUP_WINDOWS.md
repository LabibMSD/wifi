# Setup Windows Native (Laravel Herd)

## Persiapan Awal

Laravel Herd menyediakan stack PHP, MySQL, Nginx, dan Mailpit dalam satu installer. Tidak perlu Docker/WSL lagi.

### 1. Install Laravel Herd

1. **Download** installer dari [herd.laravel.com/windows](https://herd.laravel.com/windows)
2. **Install** (jika kamu punya Herd sebelumnya, update:
    ````powershell

    herd install --upgrade
   
    ````
3. **Restart** PowerShell / VS Code terintegrasi Herd
4. **Verifikasi**:
    ```powershell
    php -v            # PHP 8.4+
    composer -v         # Composer
    node -v           # Node.js (by Herd)
    npm -v
    herd --version
    ```

> Herd berbeda dari Laravel Valet (Herd adalah installer, Valet mirip). Herd otomatis map project `*.test` ke `http://localhost` dengan HTTPS.

### 2. Clone Project

```powershell
# Clone ke workspace kamu (misalnya C:\Users\Kamu\Projects)
cd C:\Users\Kamu\Projects
git clone <repo-url> wifi
cd wifi
```

### 3. Env Default (Herd)

Herd sudah aktifkan MySQL (port 3306) dan Mailpit (port 8025). Edit `/.env` saja:

```env
APP_NAME="Wifi"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://wifi.test

# Database (Herd default: root, no pass)
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=wifi
DB_USERNAME=root
DB_PASSWORD=

# Session & Cache lokal
SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=sync

# Mail (Herd include Mailpit di 1025/8025)
MAIL_MAILER=smtp
MAIL_HOST=127.0. approached local
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_FROM_ADDRESS="admin@wifi.test"
MAIL_FROM_NAME="${APP_NAME}"

VITE_APP_NAME="${APP_NAME}"
```

> **Note:** Kalau Herd belum buat DB `wifi`, ciptakan manual:
>
> ```powershell
> mysql -u root -e "CREATE DATABASE IF NOT EXISTS wifi;"
> ```

### 4. Dependency & Build

```powershell
# Install PHP deps
composer install

# Key & DB
php artisan key:generate
php artisan migrate --seed

# Frontend
npm install
npm run build
# Untuk dev hot reload:
npm run dev
```

### 5. Akses Aplikasi

| Service         | URL                            |
| --------------- | ------------------------------ |
| **Web App**     | https://wifi.test              |
| **Admin Panel** | https://wifi.test/admin        |
| **Mailpit**     | http://localhost:8025          |
| **MySQL**       | 127.0.0.1:3306 (root, no pass) |

> Herd otomatis expose project ke \*.test. Kalau kamu jalankan `herd link` di folder `wifi`, kamu bisa akses langsung.

### 6. Herd Management

```powershell
# Link project (bikin *.test tersambung)
herd link

# List linked sites
herd list

# Buka di browser (buka URL secara otomatis)
herd open

# Share public (ngrok)
herd share

# Server services (start/stop MySQL, Redis, Mailpit)
herd services

# Test dengan ngrok
herd share > ngrok-url.txt
```

### 7. Troubleshooting

| Masalah                               | Solusi                                                                    |
| ------------------------------------- | ------------------------------------------------------------------------- |
| **Path contain spaces**               | Pindahkan ke folder tanpa spasi: `C:\Users\Kamu\Projects\wifi`            |
| **Symlink permission denied**         | Jalankan `herd link` atau `php artisan storage:link` **as Administrator** |
| **PHP version mismatched**            | `herd use php@8.4` (Herd punya multiple PHP)                              |
| **Tailwind/Vite tidak jalan**         | Herd jalankan browser helper — pastikan `VITE_APP_NAME` benar             |
| **Filament exports permission error** | `icacls storage\app\private\filament_exports /grant "Everyone":(OI)(CI)F` |

### 8. Pilih Mana?

| Situasi                                       | Alat                                        |
| --------------------------------------------- | ------------------------------------------- |
| Team pakai Docker / CI/CD / Production parity | **Sail (WSL2)** — lihat file SETUP.md       |
| Solo dev Windows, cepat, no VM                | **Laravel Herd (Windows native)**           |
| Deploy ke Laravel Cloud                       | **Sail** (maka environment sama production) |
| Butuh switch PHP version cepat                | **Herd** punya built-in PHP version manager |

## Konsep Baru: Herd vs Sail

| Fitur             | **Laravel Herd (Native)**       | **Sail (Docker/WSL2)**           |
| ----------------- | ------------------------------- | -------------------------------- |
| Instalasi         | PHP 8.4+, MySQL, Nginx, Mailpit | Container Linux Docker           |
| Belajar           | Cepat, built-in Windows tools   | Diingatkan dari WSL2 / container |
| Resource          | Lebih berat di RAM host         | Diinginkan di Docker             |
| Debugging         | Lebih mudah (native stack)      | Harus remote ke container        |
| Production parity | Tidak persis (local)            | Lebih mirip (Docker = cloud)     |

> **Rekomendasi:** Tim pakai **Sail** (Docker) untuk parity, individu Windows dev pakai **Herd** untuk kenyamanan cepat.

---

## 9. Migrasi dari Sail ke Herd (Opsional)

Kalau kamu sudah develop pakai Sail dan ingin beralih ke Herd, jalankan di folder project:

```powershell
# Stop Sail containers
vendor\bin\sail down

# Hapus Docker volumes (opsional)
docker compose down -v

# Verifikasi MySQL lokal (port 3306)
mysql --protocol=tcp -u root -e "SHOW DATABASES;"

# Kalau MySQL butuh password:
# - Edit .env:
#   DB_USERNAME=root
#   DB_PASSWORD=<password>

# Lalu jalankan setup di atas
```

---

> **Catatan:** Dokumentasi ini satu file (SETUP.md) untuk WSL2/Sail. File **SETUP_WINDOWS.md** ini adalah referensi Windows Native (Herd) yang terpisah.
