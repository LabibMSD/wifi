# Wifi Project — Panduan Instalasi & Development Setup

> Project Laravel 12 + Filament 3 + Sail (Docker) + Tailwind CSS v4

---

## 1. Prasyarat Sistem

| Tools | Versi Minimum | Catatan |
|-------|---------------|---------|
| **Docker Desktop** / Docker Engine | 24.x | WSL2 backend di Windows |
| **Docker Compose** | v2.x | Sudah bundle di Docker Desktop |
| **Git** | 2.x | |
| **Make** (opsional) | - | Untuk shortcut command |

> **Catatan Windows:** Gunakan **WSL2 (Ubuntu/Debian)**. Jangan jalankan `vendor/bin/sail` langsung dari PowerShell/CMD — path & permission akan bermasalah.

---

## 2. Clone & Persiapan Awal

```bash
# 1. Clone repository
git clone <repository-url> wifi
cd wifi

# 2. Copy environment file
cp .env.example .env

# 3. (Opsional) Set user ID agar file permission match di host
# Lihat WWWUSER/WWWGROUP di compose.yaml — default 1000:1000
```

---

## 3. Konfigurasi `.env` (Wajib)

Edit file `.env` dan sesuaikan minimal ini:

```env
APP_NAME="Wifi"
APP_ENV=local              # ganti dari production
APP_DEBUG=true
APP_URL=http://localhost   # atau http://wifi.test jika pakai domain lokal

APP_TIMEZONE=Asia/Jakarta
APP_LOCALE=id

# Database (Sail/MySQL) — jangan pakai sqlite saat pakai Sail
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=wifi
DB_USERNAME=sail
DB_PASSWORD=secret

# Session & Cache (database agar persistent di container)
SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database

# Mail (Mailpit — sudah include di Sail)
MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_FROM_ADDRESS="admin@wifi.local"
MAIL_FROM_NAME="${APP_NAME}"

# Vite (hot reload)
VITE_APP_NAME="${APP_NAME}"
```

> **Tip:** Jika pakai domain lokal (`wifi.test`), tambahkan ke `/etc/hosts`:
> ```
> 127.0.0.1 wifi.test
> ```

---

## 4. Build & Jalankan Container (Sail)

```bash
# 1. Install PHP dependencies (di host, sekali saja)
docker run --rm \
  -u "$(id -u):$(id -g)" \
  -v "$(pwd):/var/www/html" \
  -w /var/www/html \
  laravelsail/php84-composer:latest \
  composer install --ignore-platform-reqs

# 2. Build & start containers
./vendor/bin/sail up -d --build

# 3. Generate app key
./vendor/bin/sail artisan key:generate

# 4. Migrasi database + seeder (jika ada)
./vendor/bin/sail artisan migrate --seed

# 5. Build frontend assets
./vendor/bin/sail npm install
./vendor/bin/sail npm run build
# Atau untuk development dengan hot reload:
./vendor/bin/sail npm run dev
```

---

## 5. Akses Aplikasi

| Service | URL | Keterangan |
|---------|-----|------------|
| **Web App** | http://localhost (atau http://wifi.test) | Utama |
| **Admin Panel (Filament)** | http://localhost/admin | Login via `/admin/login` |
| **Mailpit (Email Testing)** | http://localhost:8025 | UI cek email |
| **MySQL** | `localhost:3306` | User: `sail`, Pass: `secret` |

---

## 6. Perintah Harian (Cheatsheet)

```bash
# Container management
./vendor/bin/sail up -d          # Start background
./vendor/bin/sail down           # Stop
./vendor/bin/sail down -v        # Stop + hapus volume DB (reset data)
./vendor/bin/sail ps             # Status container

# Artisan (selalu prefix ./vendor/bin/sail)
./vendor/bin/sail artisan migrate
./vendor/bin/sail artisan migrate:fresh --seed
./vendor/bin/sail artisan route:list
./vendor/bin/sail artisan queue:work
./vendor/bin/sail artisan filament:upgrade   # setelah update Filament

# Testing
./vendor/bin/sail artisan test
./vendor/bin/sail artisan test --filter=NamaTest

# Frontend
./vendor/bin/sail npm run dev      # Hot reload (Vite)
./vendor/bin/sail npm run build    # Production build

# Shell masuk container
./vendor/bin/sail shell            # Bash sebagai user sail
./vendor/bin/sail root-shell       # Bash sebagai root

# Composer
./vendor/bin/sail composer require vendor/package
./vendor/bin/sail composer dump-autoload
```

---

## 7. Struktur Project Penting

```
wifi/
├── app/
│   ├── Filament/           # Admin panel: Resources, Widgets, Pages
│   ├── Models/             # Eloquent models
│   └── Providers/Filament/AdminPanelProvider.php  # Konfigurasi panel
├── resources/
│   ├── css/app.css         # Tailwind + custom colors (brand eponet)
│   ├── js/app.js           # Entry point Vite
│   └── views/              # Blade templates (welcome.blade.php)
├── public/
│   ├── logo.png            # Brand logo (favicon source)
│   └── favicon.ico         # Generated dari logo.png
├── database/
│   ├── migrations/
│   └── seeders/
├── compose.yaml            # Docker Compose (Sail)
├── .env                    # Environment config (JANGAN commit)
└── vite.config.js          # Vite + Laravel plugin + Tailwind v4
```

---

## 8. Troubleshooting Umum

| Masalah | Solusi |
|---------|--------|
| **Permission denied (storage, bootstrap/cache)** | `./vendor/bin/sail root-shell -c "chown -R sail:sail storage bootstrap/cache && chmod -R 775 storage bootstrap/cache"` |
| **Vite manifest not found / hot reload tidak jalan** | Pastikan `./vendor/bin/sail npm run dev` jalan di terminal terpisah. Cek `APP_URL` di `.env` match port yang dibuka. |
| **DB Connection refused** | Tunggu MySQL healthy: `./vendor/bin/sail ps` → cek `mysql` status `healthy`. Atau `./vendor/bin/sail artisan migrate` ulang. |
| **Class "Filament\..." not found** | `./vendor/bin/sail composer dump-autoload` |
| **File permission error di `storage/app/private/filament_exports/`** | `./vendor/bin/sail root-shell -c "chown -R sail:sail storage/app/private/filament_exports && chmod -R 775 storage/app/private/filament_exports"` |
| **Port 80/3306/5173 sudah dipakai** | Edit `APP_PORT`, `FORWARD_DB_PORT`, `VITE_PORT` di `.env` (misal `APP_PORT=8080`) |

---

## 9. Deployment (Ringkas)

**Laravel Cloud (Rekomendasi):**
1. Push ke GitHub/GitLab
2. Connect di [cloud.laravel.com](https://cloud.laravel.com)
3. Set environment variables dari `.env` (production)
4. Deploy

**Manual VPS (Docker):**
```bash
# Di server
git clone <repo> /var/www/wifi
cd /var/www/wifi
cp .env.example .env   # isi production values
docker compose -f compose.yaml up -d --build
docker compose exec laravel.test php artisan migrate --force
docker compose exec laravel.test php artisan config:cache
docker compose exec laravel.test php artisan route:cache
docker compose exec laravel.test php artisan view:cache
```

---

## 10. Kontak & Dukungan

- **Maintainer:** Tim Wifi Project
- **Stack:** Laravel 12 • Filament 3 • Tailwind CSS 4 • MySQL 8.4 • PHP 8.4
- **Brand Assets:** `public/logo.png` (eponet), `public/favicon.ico`

---

## 11. Alternatif: Windows Native (Tanpa WSL/Docker) — **Laravel Herd**

> **Cocok untuk:** Developer Windows yang tidak mau install WSL2/Docker.  
> **Tool:** [Laravel Herd](https://herd.laravel.com/windows) — PHP, Nginx, MySQL, Redis, Mailpit native di Windows.

### 11.1 Install Laravel Herd

1. Download & install dari [herd.laravel.com/windows](https://herd.laravel.com/windows)
2. Restart terminal / VS Code setelah install
3. Verifikasi:
   ```powershell
   php -v          # PHP 8.4+
   composer -v     # Composer included
   node -v         # Node.js included (via Herd)
   npm -v
   ```

### 11.2 Setup Project (PowerShell / Command Prompt)

```powershell
# 1. Clone
git clone <repository-url> wifi
cd wifi

# 2. Copy env & configure untuk LOCAL (bukan Docker)
cp .env.example .env
```

Edit `.env` — **konfigurasi native Windows**:

```env
APP_NAME="Wifi"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://wifi.test   # Herd auto-mapping *.test ke localhost

APP_TIMEZONE=Asia/Jakarta
APP_LOCALE=id

# Database: pakai MySQL bawaan Herd (port 3306, user root, no password default)
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=wifi
DB_USERNAME=root
DB_PASSWORD=

# Session & Cache: file/database local (bukan container)
SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=sync

# Mail: Herd include Mailpit di port 1025/8025
MAIL_MAILER=smtp
MAIL_HOST=127.0.0.1
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_FROM_ADDRESS="admin@wifi.local"
MAIL_FROM_NAME="${APP_NAME}"

VITE_APP_NAME="${APP_NAME}"
```

> **Note:** Herd otomatis buat database `wifi` kalau belum ada. Atau buat manual via `mysql -u root -e "CREATE DATABASE wifi;"`

### 11.3 Install Dependencies & Jalankan

```powershell
# 1. PHP deps
composer install

# 2. Generate key
php artisan key:generate

# 3. Migrasi
php artisan migrate --seed

# 4. Frontend deps & build
npm install
npm run build
# Atau development hot reload:
npm run dev
```

### 11.4 Akses Aplikasi (Herd)

| Service | URL |
|---------|-----|
| **Web App** | http://wifi.test (auto HTTPS: https://wifi.test) |
| **Admin Panel** | http://wifi.test/admin |
| **Mailpit** | http://localhost:8025 |
| **MySQL** | `127.0.0.1:3306` (user: `root`, pass: kosong) |

> Herd otomatis serve folder project di `~/Herd/wifi` atau link manual:
> ```powershell
> herd link   # di dalam folder project
> # lalu akses http://wifi.test
> ```

### 11.5 Perintah Harian (Native)

```powershell
# Artisan
php artisan migrate
php artisan migrate:fresh --seed
php artisan route:list
php artisan queue:work
php artisan test

# Frontend
npm run dev      # Hot reload
npm run build    # Production

# Herd management
herd list        # List linked sites
herd open        # Buka site di browser
herd share       # Share via ngrok (public URL)
herd services    # Start/stop MySQL, Redis, Mailpit, PostgreSQL
```

### 11.6 Catatan Penting Windows Native

| Hal | Solusi |
|-----|--------|
| **Path spaces** | Hindari folder dengan spasi: `C:\Users\Nama\Projects\wifi` OK, `C:\My Projects\wifi` bisa bermasalah |
| **Symlink permission** | Jalankan terminal **as Administrator** saat pertama `herd link` atau `php artisan storage:link` |
| **PHP version** | Herd default PHP 8.4. Ubah via `herd use php@8.3` kalau butuh versi lain |
| **Tailwind v4 + Vite** | Sudah kompatibel native, tidak butuh Docker |
| **Filament exports permission** | `icacls storage\app\private\filament_exports /grant Everyone:(OI)(CI)F` kalau error permission |

---

## 12. Pilih Mana? Quick Decision

| Kebutuhan | Rekomendasi |
|-----------|-------------|
| **Team pakai Docker / CI/CD / Production parity** | **Sail (WSL2)** — Section 1-10 |
| **Solo dev Windows, mau cepat, ringan, no VM** | **Herd (Native)** — Section 11 |
| **Deploy ke Laravel Cloud / VPS Docker** | **Sail** (environment sama production) |
| **Butuh multiple PHP version / quick switch** | **Herd** (built-in PHP version manager) |

---

> **Catatan untuk Developer:**  
> Dokumentasi ini cover kedua cara. Tim bebas pilih — **kode & database sama**, cuma beda *runtime* (container vs native). File `.env` beda config DB/Session, jadi **jangan commit `.env`**.