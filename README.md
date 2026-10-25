# 🚀 Hybrid Architecture Enterprise Starter Kit (Inertia.js Bridge & Traditional SPA via REST API)

Repositori ini adalah **Clean Production-Ready Starter Kit** berbasis **Laravel 12 & Vue 3** yang dirancang sebagai fondasi arsitektur enterprise untuk membangun sistem baru. Seluruh halaman demo dummy telah dibersihkan secara tuntas, namun **seluruh tumpukan teknologi modern (WebSockets, ECharts, MapLibre, RBAC, Audit Trail, Export, Monitoring, Theme Switcher) tetap utuh 100% dan siap digunakan**.

---

## 🏛️ Arsitektur Ganda (Hybrid Monolith)

Aplikasi ini menggabungkan dua paradigma frontend Vue 3 dalam satu backend Laravel terpadu dengan basis data **MySQL**, diamankan menggunakan **Laravel Sanctum (Stateful Cookie-Based Session)**:

1. **Autentikasi Terpadu (Single Sign-On Stateful Session)**:
   - Akses Login: `/login` (Dilengkapi proteksi CSRF, remember me, & forgot password).
   - Akun Pengguna Bawaan: `admin@hybrid.local` / `password`.
   - Sesi berbasis HTTP-Only Cookie yang otomatis berlaku serentak untuk halaman Inertia.js dan Traditional SPA.

2. **Inertia.js Bridge (Server-Driven Monolith Experience)**:
   - Akses via: `/` dan `/inertia/dashboard`.
   - Berkomunikasi langsung dari controller Laravel via `Inertia::render()` dengan props Eloquent tanpa perlu membuat boilerplate endpoint API manual.
   - Dilengkapi navbar terintegrasi, profil akun, audit log, dan tombol logout.

3. **Traditional SPA (Decoupled Client-Side Routing via REST API)**:
   - Akses via: `/spa/*`.
   - Berjalan mandiri di browser menggunakan **Vue Router (History Mode)** dan **Pinia Store**.
   - Berkomunikasi asinkron via **Axios** ke endpoint `/api/v1/*`.

---

## ⚡ Panduan Instalasi Cepat (Git Clone)

Pastikan sistem Anda telah terpasang **PHP 8.2+**, **Composer**, **Node.js LTS (v20+)**, dan **MySQL 8.0**.

```bash
# 1. Kloning repositori resmi dari GitHub
git clone https://github.com/paradigma-monolith/sadaya.git
cd sadaya

# 2. Salin berkas lingkungan
cp .env.example .env

# 3. Pasang seluruh dependensi PHP dan Node.js
composer install
npm install

# 4. Generate Application Key & Storage Symlink
php artisan key:generate
php artisan storage:link

# 5. Konfigurasi kredensial MySQL di .env, lalu jalankan migrasi & seeder
php artisan migrate --seed

# 6. Jalankan server lokal
# Terminal 1 (Backend):
php artisan serve
# Terminal 2 (Vite HMR Frontend):
npm run dev
# Terminal 3 (Opsional - WebSockets Reverb):
php artisan reverb:start
```

---

## 🛠️ Stack Teknologi Lengkap (Preserved & Ready)

- **Backend Framework**: Laravel 12.x / PHP 8.2+
- **Basis Data**: MySQL 8.0 (Database: `hybrid_laravel`)
- **Frontend Framework**: Vue 3 (Composition API)
- **Bundler & Tooling**: Vite 7 + `@vitejs/plugin-vue` + Tailwind CSS v4
- **State Management & Routing (SPA)**: Pinia (`appStore.js`) + Vue Router
- **Bridge Monolith**: `@inertiajs/vue3` + `inertiajs/inertia-laravel`
- **HTTP Client**: Axios
- **Role & Permission (RBAC)**: `spatie/laravel-permission` v6 (Super Admin, Admin, Staff)
- **Audit Trail & Activity Log**: `spatie/laravel-activitylog` v4 (Terekam di model `User` & modul kustom)
- **Interactive OpenAPI Documentation**: `dedoc/scramble` (`/docs/api`)
- **Dokumen & Spreadsheet Export**: `maatwebsite/excel` (Excel/CSV) + `barryvdh/laravel-dompdf` (PDF)
- **Mesin Pencarian Teks Penuh (Full-Text Search)**: Laravel Scout v11 (`laravel/scout`) - Terkoneksi ke Command Palette (`Ctrl+K`)
- **Server-Side Query Builder**: Spatie QueryBuilder v6 (`spatie/laravel-query-builder`) - Standarisasi filtering, sorting, & pagination REST API
- **Komponen Tabel Data Enterprise**: `BaseDataTable.vue` - Mendukung pagination, multi-column sort, pencarian, & custom cell slots
- **Standarisasi Waktu & Tanggal Lokal**: Day.js (`dayjs`) via composable `useDate.js` (Locale Indonesia `id` & *relativeTime*)
- **User Impersonation**: `lab404/laravel-impersonate` - Super Admin dapat berpindah akun user lain dengan safety warning banner
- **Manajemen Berkas & Lampiran**: Spatie MediaLibrary v11 (`spatie/laravel-medialibrary`) - Konversi gambar WebP otomatis & multi-koleksi
- **Otomatisasi Backup Sistem**: Spatie Laravel Backup v9 (`spatie/laravel-backup`) - Pencadangan database MySQL & storage
- **Headless UI Primitives & Command Palette**: Reka UI (`reka-ui`) + `CommandPalette.vue` (`Ctrl+K` / `Cmd+K`)
- **Modern Notification Toasts**: Vue Sonner (`vue-sonner`) - Toast melayang responsif dark mode & real-time audio chime
- **Mesin Visualisasi Grafik**: Apache ECharts 6 (`BaseChart.vue`) - Reaktif terhadap Dark/Light Mode
- **Mesin Geospasial / Peta**: MapLibre GL 6 (`BaseMap.vue`) - Mendukung layer 3D, marker interaktif, & tema auto
- **In-Memory Cache & Data Store**: Redis (`predis/predis` v3.7 + `Illuminate\Support\Facades\Redis`)
- **High-Performance Application Engine**: Laravel Octane + RoadRunner (`laravel/octane` + `rr.exe`)
- **Real-Time WebSockets**: Laravel Reverb + Laravel Echo (`laravel/reverb` + `laravel-echo` + `pusher-js`)
- **Application Health & Telemetri**: Laravel Pulse (`laravel/pulse`) - Memantau query lambat, antrean queue, & resource sistem
- **Error Tracking & Monitoring**: Sentry Full-Stack (`sentry/sentry-laravel` + `@sentry/vue`)

---

## 📁 Struktur Direktori Bersih

```text
app/
├── Exports/
│   └── AuditLogsExport.php                 # Mesin Ekspor Data Audit Trail (Excel & CSV)
├── Http/
│   ├── Controllers/
│   │   ├── ActivityLogController.php       # Controller Viewer Audit Trail Inertia
│   │   ├── ChatController.php              # Real-Time WebSocket Chat (Laravel Reverb)
│   │   ├── ExportController.php            # Controller Unduhan Excel, CSV, & PDF
│   │   ├── NotificationController.php      # Controller Notifikasi Real-Time (Database & Broadcast)
│   │   ├── ProfileController.php           # Manajemen Profil, Password, & Upload Avatar
│   │   ├── Api/
│   │   │   ├── ActivityLogApiController.php# Endpoint REST API Audit Trail
│   │   │   ├── ProfileApiController.php    # Endpoint REST API Profil Pengguna
│   │   │   └── StatsApiController.php      # Endpoint REST API Telemetri Sistem
│   │   ├── Auth/
│   │   │   ├── AuthController.php          # Login & Logout Sanctum
│   │   │   └── PasswordResetController.php # Lupa Sandi & Reset Password
│   │   └── Inertia/
│   │       └── DashboardController.php     # Controller Dashboard Utama
│   └── Middleware/
│       └── HandleInertiaRequests.php       # Shared Props (Auth, Flash, Roles, Permissions)
├── Models/
│   ├── ChatMessage.php                     # Model Pesan Chat Real-Time
│   ├── LoginHistory.php                    # Model Riwayat Sesi Login Pengguna
│   └── User.php                            # Model Pengguna + Spatie RBAC & Activitylog
routes/
├── web.php                                 # Rute Dashboard, Inertia, Profil, Audit, & SPA
├── api.php                                 # Rute RESTful JSON v1 (/api/v1/*)
├── channels.php                            # Definisi Saluran Broadcast WebSocket Reverb
resources/
├── js/
│   ├── Components/
│   │   ├── AdminSidebar.vue                # Navigasi Sidebar Collapsible (Docked & Drawer)
│   │   ├── BaseChart.vue                   # Komponen Reusable Apache ECharts
│   │   ├── BaseMap.vue                     # Komponen Reusable MapLibre GL
│   │   ├── FloatingChat.vue                # Widget Live Chat WebSocket Kanan Bawah
│   │   ├── HybridNavbar.vue                # Navbar Terpadu (Inertia & SPA)
│   │   └── ThemeSwitcher.vue               # 3-Way Switcher: Dark, Light, & Mengikuti OS
│   ├── Pages/
│   │   ├── AuditLog/Index.vue              # Viewer Log Audit & Riwayat Sesi
│   │   ├── Auth/Login.vue                  # Halaman Masuk Bersih
│   │   ├── Inertia/Dashboard.vue           # Dashboard Utama Bersih (Ready-to-Develop)
│   │   └── Profile/Edit.vue                # Pengaturan Akun, Sandi, & Avatar
│   └── spa/
│       ├── App.vue                         # Shell Aplikasi SPA
│       ├── router/index.js                 # Vue Router Configuration
│       ├── stores/appStore.js              # Pinia State Management
│       └── views/HomeView.vue              # Dashboard / Tampilan Utama SPA
```

---

## 🚀 Panduan Membangun Sistem Baru

### 1. Menambahkan Halaman Baru di Inertia.js Bridge
1. Buat controller baru di `app/Http/Controllers/`:
   ```php
   public function index() {
       return Inertia::render('MyModule/Index', [
           'items' => MyModel::latest()->paginate(10),
       ]);
   }
   ```
2. Daftarkan rute di `routes/web.php`.
3. Buat komponen Vue di `resources/js/Pages/MyModule/Index.vue`.
4. Anda dapat menggunakan `<BaseChart :option="chartOption" />` atau `<BaseMap :markers="locations" />` langsung di halaman baru Anda!

### 2. Menambahkan Halaman Baru di Traditional SPA
1. Buat view baru di `resources/js/spa/views/MyModuleView.vue`.
2. Daftarkan rute di `resources/js/spa/router/index.js`:
   ```javascript
   { path: '/my-module', name: 'spa-my-module', component: MyModuleView }
   ```
3. Kelola state di `resources/js/spa/stores/` menggunakan Pinia dan konsumsi endpoint `/api/v1/*`.

### 3. Menggunakan Komponen Reusable

#### Mesin Grafik Apache ECharts ([BaseChart.vue](resources/js/Components/BaseChart.vue))
```vue
<script setup>
import BaseChart from '@/Components/BaseChart.vue';

const myChartOption = {
  xAxis: { type: 'category', data: ['Sen', 'Sel', 'Rab', 'Kam', 'Jum'] },
  yAxis: { type: 'value' },
  series: [{ data: [120, 200, 150, 80, 70], type: 'bar' }]
};
</script>

<template>
  <BaseChart :option="myChartOption" height="320px" />
</template>
```

#### Mesin Peta Geospasial MapLibre GL ([BaseMap.vue](resources/js/Components/BaseMap.vue))
```vue
<script setup>
import BaseMap from '@/Components/BaseMap.vue';

const markers = [
  { id: 1, name: 'Kantor Cabang Jakarta', coordinates: [106.8272, -6.1751], status: 'online' }
];
</script>

<template>
  <BaseMap :markers="markers" height="480px" />
</template>
```

#### Tabel Data Enterprise ([BaseDataTable.vue](resources/js/Components/BaseDataTable.vue))
```vue
<script setup>
import BaseDataTable from '@/Components/BaseDataTable.vue';
import { useDate } from '@/composables/useDate';

const { formatDate } = useDate();

const columns = [
  { key: 'name', label: 'Nama Pengguna', sortable: true },
  { key: 'email', label: 'Email', sortable: true },
  { key: 'created_at', label: 'Terdaftar Sejak', sortable: true },
];

const users = [
  { id: 1, name: 'Admin', email: 'admin@hybrid.local', created_at: '2026-10-25' }
];
</script>

<template>
  <BaseDataTable :columns="columns" :data="users" :clientSide="true">
    <template #cell(created_at)="{ value }">
      {{ formatDate(value) }}
    </template>
  </BaseDataTable>
</template>
```

---

## 🔒 Akun Bawaan & Otorisasi RBAC

- **Super Admin**: `admin@hybrid.local` / `password`
- **Staff Operator**: `staff@hybrid.local` / `password`

Audit trail akan otomatis mencatat seluruh perubahan data pengguna ke tabel `activity_log`.

---

## 🌐 Panduan Deploy & Konfigurasi Server Produksi

Panduan langkah demi langkah untuk melakukan deploy ke server produksi (Ubuntu 22.04/24.04 LTS, Nginx, SSL Let's Encrypt, Supervisor, dan Crontab) telah disediakan secara lengkap di:
👉 **[DEPLOYMENT.md](DEPLOYMENT.md)**

Berkas konfigurasi siap pakai yang telah disertakan:
- **Nginx Reverse Proxy & WSS:** [`deploy/nginx/app.conf`](deploy/nginx/app.conf)
- **Supervisor WebSocket Reverb:** [`deploy/supervisor/starterkit-reverb.conf`](deploy/supervisor/starterkit-reverb.conf)
- **Supervisor Queue Worker:** [`deploy/supervisor/starterkit-worker.conf`](deploy/supervisor/starterkit-worker.conf)
- **Supervisor Octane RoadRunner:** [`deploy/supervisor/starterkit-octane.conf`](deploy/supervisor/starterkit-octane.conf)
- **Automated Deployment Script:** [`deploy/deploy.sh`](deploy/deploy.sh)

---

## 📜 Lisensi

Proyek ini dilisensikan di bawah [MIT License](LICENSE).
