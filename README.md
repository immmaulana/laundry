# Laundry Soft

Aplikasi manajemen laundry multi-perusahaan berbasis **Laravel 12 + Vue 3 + Inertia (Breeze)**.  
Satu platform bisa dipakai banyak PT/tenant; setiap perusahaan punya data, karyawan, dan operasional sendiri.

## Fitur

- Multi-tenant (perusahaan / PT)
- Super Admin untuk kelola semua perusahaan
- Autentikasi Breeze (login / register perusahaan baru)
- Dashboard omzet & status pesanan
- Kasir POS
- Manajemen pesanan, pelanggan, layanan & harga
- Laporan (admin PT)
- Karyawan & role (`super_admin`, `admin`, `kasir`)
- Tema warna panel per PT (atur di **Profil PT** / edit perusahaan)
- Upload logo perusahaan (tampil di sidebar admin)
- Ikon / logo layanan (preset + upload custom, tampil di Kasir & daftar layanan)

## Stack

| Layer | Teknologi |
|-------|-----------|
| Backend | Laravel 12, PHP 8.2+ |
| Frontend | Vue 3, Inertia.js, Tailwind CSS |
| Auth | Laravel Breeze |
| Database | MySQL 8 |

## Akun demo

Jalankan `php artisan migrate --seed` lalu login dengan:

| Role | Email | Password | Akses |
|------|-------|----------|-------|
| Super Admin | `owner@laundry.test` | `owner12345` | Kelola semua PT (`/companies`) |
| Admin PTA — PT Fresh Wash | `admin@pta.test` | `rahasia123` | Operasional PTA |
| Kasir PTA | `kasir@pta.test` | `kasir12345` | Kasir / pesanan PTA |
| Admin PTB — PT Bersih Kilat | `admin@ptb.test` | `rahasia123` | Operasional PTB |
| Kasir PTB | `kasir@ptb.test` | `kasir12345` | Kasir / pesanan PTB |

**Catatan:** data PTA dan PTB terisolasi. Login sebagai Super Admin untuk melihat daftar perusahaan.

## Instalasi lokal

### Prasyarat

- PHP 8.2+ (ext: `bcmath`, `ctype`, `curl`, `dom`, `fileinfo`, `json`, `mbstring`, `openssl`, `pdo_mysql`, `tokenizer`, `xml`, `zip`)
- Composer 2
- Node.js 20+ & npm
- MySQL 8

### Langkah

```bash
git clone <url-repo-anda> laundry-soft
cd laundry-soft

composer install
cp .env.example .env
php artisan key:generate
```

Sesuaikan database di `.env`:

```env
APP_NAME="Laundry Soft"
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laundry
DB_USERNAME=root
DB_PASSWORD=
```

Buat database lalu migrate + seed:

```bash
mysql -u root -e "CREATE DATABASE IF NOT EXISTS laundry CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

php artisan migrate --seed
npm install
npm run build
php artisan serve
```

Buka [http://127.0.0.1:8000](http://127.0.0.1:8000).

### Development frontend (hot reload)

```bash
# terminal 1
php artisan serve

# terminal 2
npm run dev
```

## Deploy production (VPS)

Contoh untuk Ubuntu 22.04/24.04 + Nginx + PHP-FPM + MySQL.

### 1. Siapkan server

```bash
sudo apt update
sudo apt install -y nginx mysql-server php8.2-fpm php8.2-cli php8.2-mysql \
  php8.2-mbstring php8.2-xml php8.2-curl php8.2-zip php8.2-bcmath php8.2-gd unzip git
```

Install Composer & Node (LTS) di server, atau build asset di CI lalu upload folder `public/build`.

### 2. Clone & install

```bash
sudo mkdir -p /var/www/laundry-soft
sudo chown -R $USER:www-data /var/www/laundry-soft
cd /var/www/laundry-soft

git clone <url-repo-anda> .
composer install --no-dev --optimize-autoloader
cp .env.example .env
php artisan key:generate
```

### 3. Konfigurasi `.env` production

```env
APP_NAME="Laundry Soft"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://laundry.domainanda.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laundry
DB_USERNAME=laundry_user
DB_PASSWORD=password_kuat

SESSION_DRIVER=database
QUEUE_CONNECTION=database
CACHE_STORE=database
```

Buat database & user MySQL:

```sql
CREATE DATABASE laundry CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'laundry_user'@'127.0.0.1' IDENTIFIED BY 'password_kuat';
GRANT ALL PRIVILEGES ON laundry.* TO 'laundry_user'@'127.0.0.1';
FLUSH PRIVILEGES;
```

### 4. Build asset & migrate

```bash
npm ci
npm run build

php artisan migrate --force --seed   # seed hanya kalau butuh data demo
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

> Production biasanya **tanpa** `--seed`. Seed hanya untuk staging / demo.

### 5. Permission

```bash
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R ug+rwx storage bootstrap/cache
```

### 6. Nginx

`/etc/nginx/sites-available/laundry-soft`:

```nginx
server {
    listen 80;
    server_name laundry.domainanda.com;
    root /var/www/laundry-soft/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;
    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Aktifkan site & SSL:

```bash
sudo ln -s /etc/nginx/sites-available/laundry-soft /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx

sudo apt install -y certbot python3-certbot-nginx
sudo certbot --nginx -d laundry.domainanda.com
```

### 7. Queue & scheduler (opsional tapi disarankan)

Supervisor untuk queue:

```ini
[program:laundry-soft-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/laundry-soft/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/var/www/laundry-soft/storage/logs/worker.log
```

Cron scheduler:

```cron
* * * * * www-data cd /var/www/laundry-soft && php artisan schedule:run >> /dev/null 2>&1
```

### 8. Update / release berikutnya

```bash
cd /var/www/laundry-soft
git pull
composer install --no-dev --optimize-autoloader
npm ci && npm run build
php artisan migrate --force
php artisan optimize
sudo systemctl reload php8.2-fpm
```

## Deploy alternatif

### Shared hosting (cPanel)

1. Upload source (kecuali `node_modules`, `.git`).
2. Document root mengarah ke folder `public`.
3. Set PHP 8.2+, aktifkan ekstensi yang dibutuhkan.
4. Isi `.env` (DB dari cPanel).
5. Jalankan via SSH: `composer install --no-dev`, `php artisan key:generate`, `php artisan migrate --force`, `php artisan storage:link`, `php artisan optimize`.
6. Build asset di lokal (`npm run build`) lalu upload folder `public/build` ke server.

### Docker / Laravel Sail

```bash
cp .env.example .env
# set DB_HOST=mysql (sesuai service Sail)
./vendor/bin/sail up -d
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate --seed
./vendor/bin/sail npm install
./vendor/bin/sail npm run build
```

## Struktur peran singkat

| Role | Menu utama |
|------|------------|
| `super_admin` | Perusahaan (tambah/edit/nonaktifkan PT) |
| `admin` | Dashboard, kasir, pesanan, pelanggan, layanan, laporan, karyawan, profil PT |
| `kasir` | Dashboard, kasir, pesanan, pelanggan |

Perusahaan baru juga bisa daftar sendiri lewat halaman **Register** (otomatis jadi admin PT tersebut).

## Lisensi

MIT
