# 🌐 Panduan Lengkap Deploy & Konfigurasi Server (Production Deployment Guide)

Buku panduan ini merangkum seluruh langkah teknis untuk melakukan instalasi, konfigurasi, pengamanan, dan deployment **Enterprise Hybrid Starter Kit (Laravel 12 + Vue 3)** pada server produksi berbasis **Linux (Ubuntu 22.04 / 24.04 LTS)**.

---

## 📑 Daftar Isi
1. [Arsitektur Server Produksi](#1-arsitektur-server-produksi)
2. [Spesifikasi & Prasyarat Server](#2-spesifikasi--prasyarat-server)
3. [Langkah demi Langkah Setup Server Baru](#3-langkah-demi-langkah-setup-server-baru)
4. [Instalasi & Konfigurasi Repositori Aplikasi](#4-instalasi--konfigurasi-repositori-aplikasi)
5. [Konfigurasi Web Server Nginx & SSL HTTPS](#5-konfigurasi-web-server-nginx--ssl-https)
6. [Konfigurasi Supervisor (Reverb WebSockets & Queue)](#6-konfigurasi-supervisor-reverb-websockets--queue)
7. [Penjadwalan Otomatis (Cron Job & Backup)](#7-penjadwalan-otomatis-cron-job--backup)
8. [Script Otomasi Deployment (Zero-Downtime)](#8-script-otomasi-deployment-zero-downtime)
9. [Troubleshooting Kendala Umum Server](#9-troubleshooting-kendala-umum-server)

---

## 1. Arsitektur Server Produksi

Aplikasi menggunakan arsitektur **LEMP Stack Terpadu** yang dilengkapi dengan server WebSocket mandiri (Laravel Reverb), cache performa tinggi (Redis), dan antrean asinkron (Supervisor):

```text
                                [ CLIENT BROWSER ]
                                        │
                         HTTPS (443)    │    WSS (443/app)
                                        ▼
                   ┌────────────────────────────────────────┐
                   │          NGINX REVERSE PROXY           │
                   │   - SSL Let's Encrypt Termination      │
                   │   - Static Asset Caching (/build/*)    │
                   │   - Security Headers & Gzip Engine     │
                   └───────────────────┬────────────────────┘
                                       │
                ┌──────────────────────┴──────────────────────┐
                │ Pass /index.php                             │ Pass /app/* (WSS)
                ▼                                             ▼
┌───────────────────────────────┐             ┌───────────────────────────────┐
│     PHP-FPM / LARAVEL 12      │             │    LARAVEL REVERB DAEMON      │
│  - Inertia.js Bridge          │             │  - Port 8080 (WebSockets)     │
│  - Traditional Vue SPA        │             │  - Managed by Supervisor      │
│  - REST API & Auth Sanctum    │             └───────────────┬───────────────┘
└───────────────┬───────────────┘                             │
                │                                             │
                ├─────────────────────────────────────────────┘
                ▼
┌─────────────────────────────────────────────────────────────┐
│                 INFRASTRUCTURE & DATA LAYER                 │
│  - MySQL 8.0: Basis data relasional (Database: production)  │
│  - Redis 7.x: Session Driver, Cache Store, & Queue Jobs     │
│  - Supervisor: Worker antrean latar belakang (2 proses)     │
│  - Spatie Backup: Pencadangan otomatis harian ke disk/S3    │
└─────────────────────────────────────────────────────────────┘
```

---

## 2. Spesifikasi & Prasyarat Server

### Rekomendasi Hardware Minimal:
* **Penyedia Layanan:** DigitalOcean, Linode/Akamai, AWS EC2, Hetzner, Vultr, IDCloudHost, atau Biznet Gio.
* **Sistem Operasi:** Ubuntu 22.04 LTS atau Ubuntu 24.04 LTS (x64).
* **CPU:** 2 vCPU atau lebih.
* **RAM:** 2 GB (Rekomendasi 4 GB untuk build aset lancar di server).
* **Penyimpanan:** 30 GB SSD / NVMe.
* **Domain / DNS:** Domain aktif yang telah mengarah (A-Record) ke IP Public server (contoh: `app.perusahaan.com`).

---

## 3. Langkah demi Langkah Setup Server Baru

Masuk ke server via SSH:
```bash
ssh root@IP_SERVER_ANDA
```

### 3.1 Perbarui Sistem & Pasang Alat Esensial
```bash
sudo apt update && sudo apt upgrade -y
sudo apt install -y curl git unzip ufw software-properties-common ca-certificates lsb-release supervisor
```

### 3.2 Konfigurasi Firewall Keamanan (UFW)
```bash
sudo ufw default deny incoming
sudo ufw default allow outgoing
sudo ufw allow 22/tcp    # SSH
sudo ufw allow 80/tcp    # HTTP Nginx
sudo ufw allow 443/tcp   # HTTPS & WSS Nginx
sudo ufw --force enable
```

### 3.3 Pasang PHP 8.2 & Ekstensi Lengkap
```bash
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update
sudo apt install -y php8.2-fpm php8.2-cli php8.2-mysql php8.2-curl \
    php8.2-gd php8.2-mbstring php8.2-xml php8.2-zip php8.2-bcmath \
    php8.2-soap php8.2-intl php8.2-redis php8.2-imagick
```

Verifikasi instalasi PHP:
```bash
php -v
sudo systemctl status php8.2-fpm
```

### 3.4 Pasang & Amankan MySQL 8.0
```bash
sudo apt install -y mysql-server
sudo mysql_secure_installation
```

Buat database dan pengguna produksi:
```bash
sudo mysql -u root -p
```
Eksekusi query berikut di dalam terminal MySQL:
```sql
CREATE DATABASE starterkit_prod CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'starterkit_user'@'127.0.0.1' IDENTIFIED BY 'PASSWORD_SUPER_AMAN_ANDA_123!';
GRANT ALL PRIVILEGES ON starterkit_prod.* TO 'starterkit_user'@'127.0.0.1';
FLUSH PRIVILEGES;
EXIT;
```

### 3.5 Pasang & Aktifkan Redis Server
```bash
sudo apt install -y redis-server
sudo systemctl enable redis-server
sudo systemctl start redis-server
redis-cli ping   # Harus menghasilkan output: PONG
```

### 3.6 Pasang Composer & Node.js LTS
```bash
# 1. Pasang Composer Global
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
composer --version

# 2. Pasang Node.js v20.x LTS
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs
node -v
npm -v
```

### 3.7 Pasang Web Server Nginx & Certbot SSL
```bash
sudo apt install -y nginx certbot python3-certbot-nginx
sudo systemctl enable nginx
sudo systemctl start nginx
```

---

## 4. Instalasi & Konfigurasi Repositori Aplikasi

### 4.1 Kloning Repositori ke Direktori Web
```bash
cd /var/www
sudo git clone https://github.com/paradigma-monolith/sadaya.git starterkit_paradigma
cd /var/www/starterkit_paradigma
```

### 4.2 Atur Hak Akses dan Kepemilikan Berkas
Web server (`www-data`) wajib memiliki hak tulis pada direktori `storage` dan `bootstrap/cache`:
```bash
sudo chown -R www-data:www-data /var/www/starterkit_paradigma
sudo chmod -R 775 /var/www/starterkit_paradigma/storage
sudo chmod -R 775 /var/www/starterkit_paradigma/bootstrap/cache
```

### 4.3 Konfigurasi Berkas `.env` Produksi
Salin berkas template lingkungan:
```bash
sudo cp .env.example .env
sudo nano .env
```

Pastikan variabel-variabel kunci berikut diisi dengan nilai produksi yang benar:

```dotenv
# Identitas Aplikasi & Keamanan
APP_NAME="Starterkit Paradigma"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

# Basis Data MySQL Produksi
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=starterkit_prod
DB_USERNAME=starterkit_user
DB_PASSWORD="PASSWORD_SUPER_AMAN_ANDA_123!"

# Redis untuk Sesi, Cache, & Antrean Cepat
SESSION_DRIVER=redis
CACHE_STORE=redis
QUEUE_CONNECTION=redis
REDIS_CLIENT=predis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379

# Laravel Reverb (WebSockets Server)
BROADCAST_CONNECTION=reverb
REVERB_APP_ID=starterkit-app
REVERB_APP_KEY=reverb-prod-key-778899
REVERB_APP_SECRET=reverb-prod-secret-998877
REVERB_HOST="0.0.0.0"
REVERB_PORT=8080
REVERB_SCHEME=http

# Client Vue Echo (Menembus Nginx Reverse Proxy via WSS Port 443)
VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="yourdomain.com"
VITE_REVERB_PORT=443
VITE_REVERB_SCHEME="https"

# Mesin Pencarian Teks Penuh
SCOUT_DRIVER=database
```

### 4.4 Pasang Dependensi & Jalankan Migrasi
```bash
# 1. Pasang dependensi PHP produksi
composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev

# 2. Generate Application Encryption Key & Storage Link
php artisan key:generate
php artisan storage:link

# 3. Jalankan migrasi tabel database dan data seeder bawaan
php artisan migrate --force --seed

# 4. Pasang dependensi Node & kompilasi bundle aset frontend
npm ci --prefer-offline --no-audit
npm run build
```

---

## 5. Konfigurasi Web Server Nginx & SSL HTTPS

Repositori ini telah menyediakan template konfigurasi Nginx resmi siap pakai di `deploy/nginx/app.conf`.

### 5.1 Pasang Konfigurasi Virtual Host Nginx
```bash
sudo cp deploy/nginx/app.conf /etc/nginx/sites-available/starterkit_paradigma
sudo nano /etc/nginx/sites-available/starterkit_paradigma
```
> **Catatan:** Ganti teks `yourdomain.com` dengan nama domain aktual Anda.

### 5.2 Aktifkan Virtual Host & Uji Konfigurasi
```bash
# Aktifkan konfigurasi via symlink
sudo ln -s /etc/nginx/sites-available/starterkit_paradigma /etc/nginx/sites-enabled/

# Hapus default host bawaan Nginx jika tidak digunakan
sudo rm -f /etc/nginx/sites-enabled/default

# Uji integritas sintaks Nginx
sudo nginx -t

# Muat ulang Nginx
sudo systemctl reload nginx
```

### 5.3 Pasang Sertifikat SSL Gratis (Let's Encrypt)
Jalankan Certbot otomatis:
```bash
sudo certbot --nginx -d yourdomain.com -d www.yourdomain.com
```
Certbot akan secara otomatis memperbarui berkas virtual host Anda dengan jalur sertifikat SSL dan mengonfigurasi auto-renewal setiap 90 hari.

---

## 6. Konfigurasi Supervisor (Reverb WebSockets & Queue)

Supervisor bertugas menjaga proses **Laravel Reverb (WebSocket Server)** dan **Laravel Queue Worker** tetap aktif terus-menerus di latar belakang (*daemon process*), serta otomatis menyalakannya kembali jika terjadi crash atau server restart.

### 6.1 Salin Konfigurasi Supervisor
```bash
sudo cp deploy/supervisor/starterkit-reverb.conf /etc/supervisor/conf.d/
sudo cp deploy/supervisor/starterkit-worker.conf /etc/supervisor/conf.d/
```

### 6.2 Muat dan Jalankan Layanan di Supervisor
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl status
```
Output yang berhasil akan berstatus `RUNNING`:
```text
starterkit-reverb:starterkit-reverb_00   RUNNING   pid 18241, uptime 0:01:15
starterkit-worker:starterkit-worker_00   RUNNING   pid 18242, uptime 0:01:15
starterkit-worker:starterkit-worker_01   RUNNING   pid 18243, uptime 0:01:15
```

---

## 7. Penjadwalan Otomatis (Cron Job & Backup)

Agar fitur otomatisasi seperti **Spatie Backup Harian**, pembersihan log aktivitas kedaluwarsa, dan pemeliharaan telemetri Laravel Pulse berjalan otomatis, pasang entri cron job Laravel:

Buka crontab untuk pengguna `www-data`:
```bash
sudo crontab -u www-data -e
```

Tambahkan baris berikut di akhir berkas:
```cron
* * * * * cd /var/www/starterkit_paradigma && php artisan schedule:run >> /dev/null 2>&1
```

Simpan dan keluar. Jadwal Laravel Scheduler sekarang berjalan otomatis setiap menit.

---

## 8. Script Otomasi Deployment (Zero-Downtime)

Untuk melakukan pembaruan kode selanjutnya (misal setelah Anda melakukan `git push` fitur baru dari lokal), gunakan script otomasi deployment bawaan di `deploy/deploy.sh`.

Beri hak eksekusi pada script:
```bash
chmod +x /var/www/starterkit_paradigma/deploy/deploy.sh
```

Kapan pun Anda ingin merilis pembaruan ke server, cukup jalankan perintah satu baris ini:
```bash
sudo /var/www/starterkit_paradigma/deploy/deploy.sh
```

### Urutan Aksi yang Dijalankan oleh `deploy.sh`:
1. Mengaktifkan mode maintenance aplikasi (`php artisan down`).
2. Menarik commit terbaru dari GitHub (`git pull origin main`).
3. Memperbarui paket Composer produksi (`--no-dev --optimize-autoloader`).
4. Mengompilasi ulang aset Vite frontend (`npm ci && npm run build`).
5. Menjalankan migrasi database baru (`php artisan migrate --force`).
6. Memperbarui seluruh cache performa (`config:cache`, `route:cache`, `view:cache`, `event:cache`).
7. Merestart worker antrean dan server Reverb (`supervisorctl restart all`).
8. Mematikan mode maintenance (`php artisan up`).

---

## 9. Troubleshooting Kendala Umum Server

### 1. Error 502 Bad Gateway pada Nginx
* **Penyebab:** PHP-FPM tidak berjalan atau versi socket tidak sesuai.
* **Solusi:**
  ```bash
  sudo systemctl status php8.2-fpm
  # Pastikan jalur socket di nginx/app.conf sesuai dengan /var/run/php/php8.2-fpm.sock
  ```

### 2. Koneksi WebSocket WSS Gagal / 403 Forbidden
* **Penyebab:** Domain tidak diizinkan di konfigurasi Reverb atau reverse proxy Nginx belum menyertakan header upgrade.
* **Solusi:**
  1. Pastikan blok `location /app/` di Nginx telah memiliki `proxy_set_header Upgrade $http_upgrade;`.
  2. Periksa berkas `config/reverb.php`, pastikan domain Anda terdaftar di `allowed_origins` atau disetel ke `['*']`.
  3. Cek log Reverb di `/var/log/supervisor/starterkit-reverb.log`.

### 3. Permission Denied pada Direktori Storage
* **Penyebab:** Hak akses berkas tertulis oleh pengguna root bukan `www-data`.
* **Solusi:**
  ```bash
  sudo chown -R www-data:www-data /var/www/starterkit_paradigma/storage
  sudo chown -R www-data:www-data /var/www/starterkit_paradigma/bootstrap/cache
  sudo chmod -R 775 /var/www/starterkit_paradigma/storage
  ```

### 4. Gagal Terhubung ke Redis (`Connection refused [tcp://127.0.0.1:6379]`)
* **Solusi:**
  ```bash
  sudo systemctl restart redis-server
  redis-cli ping
  ```

---

## 🏁 Ringkasan Checklist Sebelum Go-Live

- [x] Variabel `APP_ENV=production` dan `APP_DEBUG=false` di `.env`.
- [x] Sertifikat SSL aktif dan rute HTTP otomatis redirect ke HTTPS.
- [x] Nginx reverse proxy berhasil meneruskan traffic WebSocket WSS ke Reverb (Port 8080).
- [x] Supervisor mengontrol Reverb daemon dan Queue worker (`status: RUNNING`).
- [x] Crontab `schedule:run` terpasang pada user `www-data`.
- [x] Kredensial akun bawaan (`admin@hybrid.local`) telah diubah kata sandinya.
- [x] Endpoint telemetri [Laravel Pulse](https://yourdomain.com/pulse) dan [Scramble OpenAPI](https://yourdomain.com/docs/api) dapat diakses dengan aman.
