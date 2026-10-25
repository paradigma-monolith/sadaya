<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Manual Book - Enterprise Hybrid Starter Kit</title>
    <style>
        @page {
            margin: 32px 42px 36px 42px;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #1e293b;
            font-size: 9.5px;
            line-height: 1.5;
        }

        /* Running Header */
        .running-header {
            position: fixed;
            top: -24px;
            left: 0;
            right: 0;
            height: 16px;
            border-bottom: 1px solid #cbd5e1;
            font-size: 8px;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.8px;
        }
        .running-header-right {
            float: right;
            font-weight: bold;
            color: #4f46e5;
        }

        /* Cover Page - Fits strictly on Page 1 */
        .cover {
            text-align: center;
            padding-top: 15px;
        }
        .cover-tagline {
            display: inline-block;
            background-color: #e0e7ff;
            color: #3730a3;
            padding: 4px 14px;
            border-radius: 999px;
            font-size: 8.5px;
            font-weight: 800;
            letter-spacing: 1px;
            text-transform: uppercase;
            margin-bottom: 14px;
            border: 1px solid #c7d2fe;
        }
        .cover-title {
            font-size: 23px;
            font-weight: 900;
            color: #0f172a;
            line-height: 1.25;
            margin-bottom: 6px;
            letter-spacing: -0.5px;
        }
        .cover-subtitle {
            font-size: 10.5px;
            color: #475569;
            max-width: 520px;
            margin: 0 auto 16px auto;
            line-height: 1.45;
        }
        .cover-accent {
            width: 70px;
            height: 3px;
            background-color: #4f46e5;
            margin: 0 auto 18px auto;
            border-radius: 2px;
        }
        .cover-cards {
            margin: 10px auto 16px auto;
            width: 95%;
            border-collapse: collapse;
        }
        .cover-card-td {
            width: 33.33%;
            padding: 5px;
            vertical-align: top;
        }
        .cover-card {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 8px 6px;
            text-align: center;
        }
        .cover-card-num {
            font-size: 15px;
            font-weight: 900;
            color: #4f46e5;
        }
        .cover-card-label {
            font-size: 8px;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            margin-top: 2px;
        }
        .cover-meta-table {
            width: 95%;
            margin: 0 auto;
            border-collapse: collapse;
            font-size: 9px;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            background-color: #ffffff;
        }
        .cover-meta-table td {
            padding: 4.5px 9px;
            border-bottom: 1px solid #f1f5f9;
        }
        .cover-meta-label {
            font-weight: 700;
            color: #475569;
            width: 30%;
            background-color: #f8fafc;
        }
        .cover-meta-val {
            color: #0f172a;
            font-weight: 500;
        }

        /* Clean Chapter Breaks: Every chapter starts on fresh page without accidental blank pages */
        .toc-page {
            page-break-before: always;
        }
        .chapter-container {
            page-break-before: always;
        }

        /* Section Headings */
        .section-header {
            border-bottom: 2px solid #4f46e5;
            padding-bottom: 4px;
            margin-top: 10px;
            margin-bottom: 10px;
            page-break-after: avoid;
        }
        .section-title {
            font-size: 12.5px;
            font-weight: 900;
            color: #1e1b4b;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        .section-desc {
            font-size: 8.5px;
            color: #64748b;
            margin-top: 1px;
        }
        h3 {
            font-size: 10.5px;
            font-weight: 800;
            color: #0f172a;
            margin-top: 12px;
            margin-bottom: 4px;
            border-left: 3px solid #4f46e5;
            padding-left: 7px;
            page-break-after: avoid;
        }
        h4 {
            font-size: 9.5px;
            font-weight: 700;
            color: #334155;
            margin-top: 8px;
            margin-bottom: 3px;
            page-break-after: avoid;
        }
        p {
            margin-bottom: 5px;
            text-align: justify;
        }

        /* Hyperlinks */
        a {
            color: #4f46e5;
            text-decoration: none;
            font-weight: 600;
        }
        a:hover {
            text-decoration: underline;
        }

        /* Data Tables */
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin: 6px 0 10px 0;
            font-size: 8.5px;
            page-break-inside: avoid;
        }
        table.data-table th, table.data-table td {
            padding: 4.5px 6.5px;
            border: 1px solid #e2e8f0;
            text-align: left;
            vertical-align: top;
        }
        table.data-table th {
            background-color: #f1f5f9;
            color: #1e293b;
            font-weight: 800;
            text-transform: uppercase;
            font-size: 7.5px;
            letter-spacing: 0.4px;
        }
        table.data-table tr:nth-child(even) td {
            background-color: #f8fafc;
        }

        /* Pill Badges */
        .badge {
            display: inline-block;
            padding: 1px 4.5px;
            border-radius: 3px;
            font-size: 7px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .badge-get { background-color: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .badge-post { background-color: #e0e7ff; color: #3730a3; border: 1px solid #c7d2fe; }
        .badge-patch { background-color: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
        .badge-delete { background-color: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
        .badge-tech { background-color: #f1f5f9; color: #334155; border: 1px solid #cbd5e1; }

        /* Code Blocks */
        pre {
            background-color: #0f172a;
            color: #f8fafc;
            padding: 6px 9px;
            border-radius: 4px;
            font-family: 'Courier New', Courier, monospace;
            font-size: 8px;
            line-height: 1.35;
            margin: 5px 0 8px 0;
            page-break-inside: avoid;
            border: 1px solid #1e293b;
        }
        code.inline {
            background-color: #f1f5f9;
            color: #4f46e5;
            padding: 1px 3px;
            border-radius: 3px;
            font-family: 'Courier New', Courier, monospace;
            font-size: 8px;
            border: 1px solid #e2e8f0;
        }

        /* Callouts */
        .callout {
            padding: 7px 9px;
            border-radius: 4px;
            margin: 6px 0;
            font-size: 8.5px;
            page-break-inside: avoid;
        }
        .callout-info {
            background-color: #eff6ff;
            border-left: 3px solid #3b82f6;
            color: #1e40af;
        }
        .callout-success {
            background-color: #f0fdf4;
            border-left: 3px solid #10b981;
            color: #065f46;
        }
        .callout-warning {
            background-color: #fffbeb;
            border-left: 3px solid #f59e0b;
            color: #92400e;
        }
        .callout-purple {
            background-color: #faf5ff;
            border-left: 3px solid #a855f7;
            color: #6b21a8;
        }
        .callout-title {
            font-weight: 800;
            margin-bottom: 2px;
            font-size: 9px;
        }

        /* Architecture Diagram Boxes */
        .diagram-container {
            background-color: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 5px;
            padding: 7px;
            margin: 6px 0;
            font-family: 'Courier New', Courier, monospace;
            font-size: 7.5px;
            line-height: 1.25;
            page-break-inside: avoid;
        }

        /* Lists */
        ul, ol {
            margin: 2px 0 6px 14px;
            padding: 0;
        }
        li {
            margin-bottom: 2px;
        }
    </style>
</head>
<body>

    <!-- DOMPDF DYNAMIC CANVAS FOOTER (True Page Numbers) -->
    <script type="text/php">
    if (isset($pdf)) {
        $text = "Halaman {PAGE_NUM} dari {PAGE_COUNT}";
        $font = $fontMetrics->get_font("Helvetica", "normal");
        $size = 8;
        $color = array(0.4, 0.45, 0.53);
        $pdf->page_text(485, 810, $text, $font, $size, $color);
        $pdf->page_text(42, 810, "Paradigma Monolith Starter Kit - Manual Book Resmi (Laravel 12 + Vue 3)", $font, $size, $color);
    }
    </script>

    <!-- RUNNING HEADER ON ALL PAGES -->
    <div class="running-header">
        <span>Arsitektur Ganda (Inertia.js + Traditional SPA)</span>
        <span class="running-header-right">PANDUAN PENGEMBANGAN SISTEM ENTERPRISE</span>
    </div>

    <!-- ==================== COVER PAGE (PAGE 1) ==================== -->
    <div class="cover">
        <div class="cover-tagline">Dokumentasi Arsitektur Resmi &bull; Edisi Enterprise 2026</div>
        <div class="cover-title">
            MANUAL BOOK TEKNIS<br>ENTERPRISE STARTER KIT
        </div>
        <div class="cover-accent"></div>
        <div class="cover-subtitle">
            Buku Panduan Komprehensif Arsitektur Hybrid (Inertia.js Monolith &amp; Traditional SPA) Berbasis <strong>Laravel 12, Vue 3, Octane, Reverb, Tailwind v4, &amp; Spatie Toolchain</strong>.
        </div>

        <table class="cover-cards">
            <tr>
                <td class="cover-card-td">
                    <div class="cover-card">
                        <div class="cover-card-num">2-in-1</div>
                        <div class="cover-card-label">Hybrid Paradigma</div>
                    </div>
                </td>
                <td class="cover-card-td">
                    <div class="cover-card">
                        <div class="cover-card-num">22+</div>
                        <div class="cover-card-label">Enterprise Tools</div>
                    </div>
                </td>
                <td class="cover-card-td">
                    <div class="cover-card">
                        <div class="cover-card-num">100%</div>
                        <div class="cover-card-label">56 Tests Passed</div>
                    </div>
                </td>
            </tr>
        </table>

        <table class="cover-meta-table">
            <tr>
                <td class="cover-meta-label">Nama Repositori:</td>
                <td class="cover-meta-val"><a href="https://github.com/paradigma-monolith/sadaya" target="_blank">paradigma-monolith/sadaya ↗</a></td>
            </tr>
            <tr>
                <td class="cover-meta-label">Basis Arsitektur:</td>
                <td class="cover-meta-val">Hybrid Monolith (Inertia.js Server-Bridge + Vue 3 SPA via REST API)</td>
            </tr>
            <tr>
                <td class="cover-meta-label">Backend Engine:</td>
                <td class="cover-meta-val">Laravel 12.x &bull; PHP 8.2+ &bull; MySQL 8.0 &bull; Redis 7+ &bull; RoadRunner</td>
            </tr>
            <tr>
                <td class="cover-meta-label">Frontend Engine:</td>
                <td class="cover-meta-val">Vue 3 (Composition API) &bull; Vite 7 &bull; Tailwind CSS v4 &bull; Reka UI</td>
            </tr>
            <tr>
                <td class="cover-meta-label">Real-Time WebSockets:</td>
                <td class="cover-meta-val">Laravel Reverb &bull; Laravel Echo &bull; Pusher JS (Port 8080)</td>
            </tr>
            <tr>
                <td class="cover-meta-label">Aplikasi Aktif Lokal:</td>
                <td class="cover-meta-val"><a href="http://127.0.0.1:8000" target="_blank">http://127.0.0.1:8000 ↗</a> (Vite Dev Server Port: 5173)</td>
            </tr>
            <tr>
                <td class="cover-meta-label">Tanggal Terbit:</td>
                <td class="cover-meta-val">{{ date('d F Y') }}</td>
            </tr>
            <tr>
                <td class="cover-meta-label">Lisensi Proyek:</td>
                <td class="cover-meta-val">Open Source MIT License &bull; Hak Cipta &copy; 2026 Paradigma Ecosystem</td>
            </tr>
        </table>
    </div>

    <!-- ==================== PAGE 2: DAFTAR ISI INTERAKTIF & BLUEPRINT ==================== -->
    <div class="toc-page">
        <div class="section-header" style="margin-top: 5px;">
            <div class="section-title">DAFTAR ISI INTERAKTIF &amp; PANDUAN NAVIGASI</div>
            <div class="section-desc">Klik pada judul bab untuk langsung melompat ke halaman topik yang bersangkutan</div>
        </div>

        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 12%;">Bab</th>
                    <th style="width: 68%;">Pokok Bahasan &amp; Modul Panduan</th>
                    <th style="width: 20%; text-align: right;">Aksi Cepat</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>BAB 1</strong></td>
                    <td><a href="#bab-1"><strong>Gambaran Umum &amp; Arsitektur Hybrid Monolith</strong></a><br>Filosofi dual-paradigma, komparasi Inertia vs SPA, diagram alur, SSO, &amp; direktori.</td>
                    <td style="text-align: right;"><a href="#bab-1">Buka Bab 1 &rarr;</a></td>
                </tr>
                <tr>
                    <td><strong>BAB 2</strong></td>
                    <td><a href="#bab-2"><strong>Matriks 22+ Teknologi &amp; Persyaratan Lingkungan</strong></a><br>Daftar lengkap 22 pustaka enterprise, dependensi sistem PHP/Node, &amp; justifikasi arsitektur.</td>
                    <td style="text-align: right;"><a href="#bab-2">Buka Bab 2 &rarr;</a></td>
                </tr>
                <tr>
                    <td><strong>BAB 3</strong></td>
                    <td><a href="#bab-3"><strong>Instalasi Cepat, Konfigurasi ENV, &amp; Operasional 4 Server</strong></a><br>Langkah instalasi awal, kamus variabel `.env`, eksekusi 4 server, &amp; kredensial default.</td>
                    <td style="text-align: right;"><a href="#bab-3">Buka Bab 3 &rarr;</a></td>
                </tr>
                <tr>
                    <td><strong>BAB 4</strong></td>
                    <td><a href="#bab-4"><strong>Tutorial Menambah Halaman Baru (Inertia vs SPA)</strong></a><br>Panduan *step-by-step* membuat modul di Inertia.js dan di Traditional Vue Router SPA.</td>
                    <td style="text-align: right;"><a href="#bab-4">Buka Bab 4 &rarr;</a></td>
                </tr>
                <tr>
                    <td><strong>BAB 5</strong></td>
                    <td><a href="#bab-5"><strong>Keamanan Enterprise, RBAC, Audit Trail, &amp; Impersonasi</strong></a><br>Role &amp; permission Spatie, audit trail otomatis model, dan fitur Super Admin Impersonation.</td>
                    <td style="text-align: right;"><a href="#bab-5">Buka Bab 5 &rarr;</a></td>
                </tr>
                <tr>
                    <td><strong>BAB 6</strong></td>
                    <td><a href="#bab-6"><strong>Katalog Komponen UI, Peta, Grafik, &amp; Interaktivitas</strong></a><br>BaseDataTable, BaseChart (ECharts), BaseMap (MapLibre), CommandPalette, useDate, Sonner.</td>
                    <td style="text-align: right;"><a href="#bab-6">Buka Bab 6 &rarr;</a></td>
                </tr>
                <tr>
                    <td><strong>BAB 7</strong></td>
                    <td><a href="#bab-7"><strong>Layanan Data: Pencarian Scout, Media WebP, &amp; Backup</strong></a><br>Laravel Scout, Spatie MediaLibrary WebP, Spatie QueryBuilder, Backup, &amp; Excel/PDF.</td>
                    <td style="text-align: right;"><a href="#bab-7">Buka Bab 7 &rarr;</a></td>
                </tr>
                <tr>
                    <td><strong>BAB 8</strong></td>
                    <td><a href="#bab-8"><strong>Real-Time WebSockets Reverb, Chat, &amp; Telemetri Pulse</strong></a><br>Event broadcasting Laravel Reverb, live chat, audio chime, Laravel Pulse, &amp; Sentry.</td>
                    <td style="text-align: right;"><a href="#bab-8">Buka Bab 8 &rarr;</a></td>
                </tr>
                <tr>
                    <td><strong>BAB 9</strong></td>
                    <td><a href="#bab-9"><strong>Katalog REST API, OpenAPI Scramble, &amp; Testing Suite</strong></a><br>Tabel rute REST API lengkap, Scramble OpenAPI GUI, dan verifikasi 56 tests passed.</td>
                    <td style="text-align: right;"><a href="#bab-9">Buka Bab 9 &rarr;</a></td>
                </tr>
                <tr>
                    <td><strong>BAB 10</strong></td>
                    <td><a href="#bab-10"><strong>Panduan Deployment Produksi &amp; Konfigurasi Server</strong></a><br>Arsitektur Ubuntu LEMP, Nginx WSS reverse proxy, Supervisor daemon, &amp; deploy.sh.</td>
                    <td style="text-align: right;"><a href="#bab-10">Buka Bab 10 &rarr;</a></td>
                </tr>
            </tbody>
        </table>

        <div class="callout callout-purple">
            <div class="callout-title">Tautan Layanan Interaktif Lokal</div>
            Klik tautan berikut saat aplikasi sedang berjalan untuk mengakses modul dan dashboard secara langsung:<br>
            &bull; <strong>Aplikasi Utama / Login:</strong> <a href="http://127.0.0.1:8000/login" target="_blank">http://127.0.0.1:8000/login ↗</a> &nbsp;|&nbsp; 
            &bull; <strong>Interactive Scramble OpenAPI:</strong> <a href="http://127.0.0.1:8000/docs/api" target="_blank">http://127.0.0.1:8000/docs/api ↗</a><br>
            &bull; <strong>Laravel Pulse Telemetry:</strong> <a href="http://127.0.0.1:8000/pulse" target="_blank">http://127.0.0.1:8000/pulse ↗</a> &nbsp;|&nbsp; 
            &bull; <strong>Repositori GitHub Resmi:</strong> <a href="https://github.com/paradigma-monolith/sadaya" target="_blank">paradigma-monolith/sadaya ↗</a>
        </div>

        <div class="section-header" style="margin-top: 10px;">
            <div class="section-title">PANDUAN MEMBACA BERDASARKAN PERAN PENGEMBANG</div>
        </div>
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 25%;">Peran Pengembang</th>
                    <th style="width: 45%;">Fokus Modul Rekomendasi</th>
                    <th style="width: 30%;">Sasaran Utama</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>Backend Engineer</strong></td>
                    <td>Bab 1, Bab 3, Bab 5 (RBAC &amp; Audit), Bab 7 (Data), Bab 9 (API)</td>
                    <td>Membangun model, policy, query builder, and API contracts.</td>
                </tr>
                <tr>
                    <td><strong>Frontend Engineer</strong></td>
                    <td>Bab 1, Bab 4 (Inertia vs SPA), Bab 6 (UI Components), Bab 8 (Echo)</td>
                    <td>Mengembangkan antarmuka responsif, state Pinia, &amp; interaktivitas.</td>
                </tr>
                <tr>
                    <td><strong>DevOps / Sysadmin</strong></td>
                    <td>Bab 2 (Prasyarat), Bab 3 (4 Servers), Bab 8 (Pulse), Bab 10 (Deploy)</td>
                    <td>Menyiapkan deployment Nginx WSS, Supervisor, Redis, SSL, &amp; backup.</td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- ==================== BAB 1: GAMBARAN UMUM & ARSITEKTUR ==================== -->
    <div id="bab-1" class="chapter-container">
        <div class="section-header">
            <div class="section-title">BAB 1: GAMBARAN UMUM &amp; ARSITEKTUR HYBRID MONOLITH</div>
            <div class="section-desc">Pondasi Dual-Paradigma Antarmuka dalam Satu Backend Terpadu</div>
        </div>

        <h3>1.1 Filosofi Dual-Paradigma: Mengapa Hybrid Monolith?</h3>
        <p>
            Banyak tim rekayasa perangkat lunak terjebak dalam dilema antara memilih arsitektur <em>Server-Driven Monolith</em> (kecepatan pengembangan tinggi, SEO ramah, tanpa boilerplate API controller) atau <em>Decoupled SPA</em> (client-side routing instan, arsitektur offline-ready, isolasi state independen). Starter kit ini memecahkan dilema tersebut secara elegan dengan menghadirkan <strong>kedua paradigma secara harmonis</strong> di dalam satu repositori tunggal:
        </p>

        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 20%;">Aspek Teknis</th>
                    <th style="width: 40%;">Pendekatan Inertia.js Bridge (Monolith)</th>
                    <th style="width: 40%;">Pendekatan Traditional Vue 3 SPA</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>Manajemen Rute</strong></td>
                    <td>Didefinisikan di Laravel (<code class="inline">routes/web.php</code>)</td>
                    <td>Didefinisikan di Client-Side (<code class="inline">vue-router</code>)</td>
                </tr>
                <tr>
                    <td><strong>Pengambilan Data</strong></td>
                    <td>Otomatis via props Controller Laravel (<code class="inline">Inertia::render</code>)</td>
                    <td>Asinkron via HTTP Client (<code class="inline">axios</code> / REST API)</td>
                </tr>
                <tr>
                    <td><strong>Manajemen State</strong></td>
                    <td>State tersinkronisasi per-halaman via controller props</td>
                    <td>Global reactive store via Pinia (<code class="inline">useAuthStore</code>)</td>
                </tr>
                <tr>
                    <td><strong>Kasus Penggunaan Terbaik</strong></td>
                    <td>Dashboard administrasi internal, CRUD korporat, formulir kompleks</td>
                    <td>Portal interaktif mandiri, modul offline-ready, micro-frontend</td>
                </tr>
            </tbody>
        </table>

        <h3>1.2 Diagram Komprehensif Aliran Permintaan Sistem</h3>
        <div class="diagram-container">
+---------------------------------------------------------------------------------------------------+
|                                     PENGGUNA / BROWSER CLIENT                                     |
+---------------------------------------------------------------------------------------------------+
        |                                                                   |
        | [1] Akses Rute "/" & "/inertia/*"                                 | [2] Akses Rute "/spa/*"
        v                                                                   v
+-----------------------------------------------+   +-----------------------------------------------+
|               INERTIA.JS BRIDGE               |   |                TRADITIONAL SPA                |
| - Server-Driven Routing (Laravel Kernel)      |   | - Client-Side Routing (Vue Router HTML5)     |
| - Controller render() returning direct props  |   | - Pinia Global Reactive Store                 |
| - Zero API Boilerplate (Direct Model Binding) |   | - Asynchronous Axios HTTP Client              |
+-----------------------------------------------+   +-----------------------------------------------+
        |                                                                   |
        |                                                                   | [3] Ajax / REST API Calls
        |                                                                   v
        |                                                   +-----------------------------------------------+
        |                                                   |               LARAVEL REST API                |
        |                                                   |             Endpoint: /api/v1/*               |
        +---------------------------------+-----------------+-----------------------------------------------+
                                          |
                                          v
+---------------------------------------------------------------------------------------------------+
|                        LARAVEL 12 UNIFIED BACKEND & INFRASTRUCTURE LAYER                          |
| - Sanctum Stateful Cookie Auth (Single Sign-On otomatis untuk Inertia & SPA tanpa token di disk)  |
| - Spatie RBAC & Activitylog  |  Laravel Scout Full-Text Search  |  Spatie MediaLibrary WebP       |
| - Laravel Reverb WebSockets  |  Laravel Octane High-Perf Engine |  MySQL 8.0 & Redis 7 Cache      |
+---------------------------------------------------------------------------------------------------+
        </div>

        <h3>1.3 Single Sign-On (SSO) Stateful Session</h3>
        <p>
            Aplikasi mengadopsi <strong>Laravel Sanctum Stateful Cookie Authentication</strong>. Setelah user berhasil melakukan autentikasi di <code class="inline">/login</code>:
        </p>
        <ul>
            <li>Cookie sesi HTTP-Only aman (<code class="inline">starterkit-paradigma-session</code>) dan <code class="inline">XSRF-TOKEN</code> disimpan oleh browser tanpa risiko pencurian skrip client (XSS-immune).</li>
            <li>Halaman Inertia.js langsung membaca sesi login melalui middleware Laravel secara transparan.</li>
            <li>Halaman SPA otomatis menyertakan kredensial cookie di setiap permintaan Axios (<code class="inline">withCredentials = true</code>) tanpa perlu menyimpan access token JWT di <code class="inline">localStorage</code>.</li>
        </ul>

        <h3>1.4 Anatomi Struktur Direktori Terorganisir</h3>
        <pre>sadaya/  (Hasil git clone repositori GitHub)
├── app/
│   ├── Exports/                     # Handler ekspor multi-format (AuditLogsExport.php)
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Api/                 # REST API: Search, Media, Users, Stats, Profile
│   │   │   ├── Auth/                # AuthController (Login, Logout, Session)
│   │   │   ├── Inertia/             # DashboardController (Inertia::render responses)
│   │   │   └── ExportController.php # Download handler Excel, CSV, PDF, Manual Book
│   │   └── Middleware/              # HandleInertiaRequests (Shared props & Impersonation)
│   ├── Models/                      # Eloquent Models (User, LoginHistory, ChatMessage)
│   └── Notifications/               # Reverb Real-Time Broadcast Notification classes
├── config/                          # Konfigurasi: backup, scout, media-library, pulse, reverb
├── database/migrations/             # Skema tabel: users, media, activity_log, chat, sessions
├── resources/
│   ├── css/app.css                  # Tailwind CSS v4 + Vue Sonner + Custom Scrollbar styles
│   ├── js/
│   │   ├── Components/              # BaseDataTable, BaseChart, BaseMap, CommandPalette
│   │   ├── composables/useDate.js   # Day.js localized Indonesian date/time formatter
│   │   ├── Pages/                   # Halaman Inertia: Auth/Login, Inertia/Dashboard
│   │   └── spa/                     # Traditional SPA: router, stores (Pinia), views
│   └── views/docs/                  # Blade template manual book PDF & Scramble OpenAPI
└── routes/                          # web.php, api.php, channels.php, console.php</pre>
    </div>

    <!-- ==================== BAB 2: MATRIKS 22+ TEKNOLOGI ==================== -->
    <div id="bab-2" class="chapter-container">
        <div class="section-header">
            <div class="section-title">BAB 2: MATRIKS 22+ TEKNOLOGI &amp; PERSYARATAN SISTEM</div>
            <div class="section-desc">Katalog Lengkap Seluruh Pustaka, Mesin, &amp; Dependensi yang Terintegrasi</div>
        </div>

        <h3>2.1 Matriks 22+ Teknologi Terintegrasi</h3>
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 22%;">Teknologi &amp; Paket</th>
                    <th style="width: 14%;">Lapisan / Versi</th>
                    <th style="width: 44%;">Tanggung Jawab &amp; Peran Sistem</th>
                    <th style="width: 20%;">Berkas Terkait</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>Laravel 12</strong></td>
                    <td><span class="badge badge-tech">Backend v12.x</span></td>
                    <td>Fondasi framework utama, MVC, routing terpadu, Eloquent ORM.</td>
                    <td><code class="inline">config/app.php</code></td>
                </tr>
                <tr>
                    <td><strong>Vue 3</strong></td>
                    <td><span class="badge badge-tech">Frontend v3.5</span></td>
                    <td>Reactive UI berbasis Composition API dengan sintaks <code class="inline">&lt;script setup&gt;</code>.</td>
                    <td><code class="inline">package.json</code></td>
                </tr>
                <tr>
                    <td><strong>Inertia.js v2</strong></td>
                    <td><span class="badge badge-tech">Bridge v2.0</span></td>
                    <td>Monolith bridge server-driven tanpa boilerplate API controller.</td>
                    <td><code class="inline">HandleInertiaRequests</code></td>
                </tr>
                <tr>
                    <td><strong>Vue Router &amp; Pinia</strong></td>
                    <td><span class="badge badge-tech">SPA Client</span></td>
                    <td>Client-side routing HTML5 history mode dan central reactive state store.</td>
                    <td><code class="inline">resources/js/spa/</code></td>
                </tr>
                <tr>
                    <td><strong>Tailwind CSS v4</strong></td>
                    <td><span class="badge badge-tech">Styling v4.0</span></td>
                    <td>Engine utility-first CSS performa tinggi dengan dark mode bawaan.</td>
                    <td><code class="inline">resources/css/app.css</code></td>
                </tr>
                <tr>
                    <td><strong>Reka UI</strong></td>
                    <td><span class="badge badge-tech">Headless UI</span></td>
                    <td>Accessible headless UI primitives untuk modal dialog dan menu navigasi.</td>
                    <td><code class="inline">package.json</code></td>
                </tr>
                <tr>
                    <td><strong>Command Palette</strong></td>
                    <td><span class="badge badge-tech">Shortcut Ctrl+K</span></td>
                    <td>Pencarian cepat modal, aksi navigasi instan, dan integrasi Scout.</td>
                    <td><code class="inline">CommandPalette.vue</code></td>
                </tr>
                <tr>
                    <td><strong>Vue Sonner</strong></td>
                    <td><span class="badge badge-tech">Toast Alerts</span></td>
                    <td>Toast floating responsive dark mode dengan efek suara audio chime.</td>
                    <td><code class="inline">resources/css/app.css</code></td>
                </tr>
                <tr>
                    <td><strong>Apache ECharts 6</strong></td>
                    <td><span class="badge badge-tech">Charts v6.0</span></td>
                    <td>Visualisasi grafik interaktif reaktif dark/light mode dan auto-resize.</td>
                    <td><code class="inline">BaseChart.vue</code></td>
                </tr>
                <tr>
                    <td><strong>MapLibre GL 6</strong></td>
                    <td><span class="badge badge-tech">Geospatial</span></td>
                    <td>Peta interaktif 3D layer, marker kustom kantor cabang, dan auto-theme.</td>
                    <td><code class="inline">BaseMap.vue</code></td>
                </tr>
                <tr>
                    <td><strong>Spatie QueryBuilder</strong></td>
                    <td><span class="badge badge-tech">REST Helper</span></td>
                    <td>Standarisasi deklaratif filter, sortir, dan pagination pada REST API.</td>
                    <td><code class="inline">routes/api.php</code></td>
                </tr>
                <tr>
                    <td><strong>BaseDataTable</strong></td>
                    <td><span class="badge badge-tech">UI Component</span></td>
                    <td>Tabel data dinamis multi-kolom sortable, local search, dan pagination.</td>
                    <td><code class="inline">BaseDataTable.vue</code></td>
                </tr>
                <tr>
                    <td><strong>Day.js (useDate)</strong></td>
                    <td><span class="badge badge-tech">Date Helper</span></td>
                    <td>Standarisasi format waktu lokal Indonesia (<code class="inline">DD MMMM YYYY</code>, <code class="inline">fromNow()</code>).</td>
                    <td><code class="inline">useDate.js</code></td>
                </tr>
                <tr>
                    <td><strong>Laravel Scout v11</strong></td>
                    <td><span class="badge badge-tech">Search Engine</span></td>
                    <td>Full-text search pada model database terintegrasi ke Command Palette.</td>
                    <td><code class="inline">config/scout.php</code></td>
                </tr>
                <tr>
                    <td><strong>Spatie MediaLibrary</strong></td>
                    <td><span class="badge badge-tech">Asset Manager</span></td>
                    <td>Manajemen lampiran berkas, auto-conversion WebP, dan REST API upload.</td>
                    <td><code class="inline">config/media-library.php</code></td>
                </tr>
                <tr>
                    <td><strong>Spatie Permission</strong></td>
                    <td><span class="badge badge-tech">RBAC Engine</span></td>
                    <td>Manajemen peran bertingkat (Super Admin, Staff, User) dan izin akses.</td>
                    <td><code class="inline">config/permission.php</code></td>
                </tr>
                <tr>
                    <td><strong>Spatie Activitylog</strong></td>
                    <td><span class="badge badge-tech">Audit Trail</span></td>
                    <td>Perekam jejak mutasi data model otomatis di tabel <code class="inline">activity_log</code>.</td>
                    <td><code class="inline">config/activitylog.php</code></td>
                </tr>
                <tr>
                    <td><strong>Laravel Impersonate</strong></td>
                    <td><span class="badge badge-tech">Support Tool</span></td>
                    <td>Super Admin dapat melakukan login sementara sebagai pengguna lain.</td>
                    <td><code class="inline">routes/web.php</code></td>
                </tr>
                <tr>
                    <td><strong>Laravel Reverb</strong></td>
                    <td><span class="badge badge-tech">WebSockets</span></td>
                    <td>Server WebSocket lokal real-time bi-directional mandiri (Pusher-free).</td>
                    <td><code class="inline">config/reverb.php</code></td>
                </tr>
                <tr>
                    <td><strong>Laravel Octane &amp; RR</strong></td>
                    <td><span class="badge badge-tech">High-Speed</span></td>
                    <td>Server worker RoadRunner (<code class="inline">rr.exe</code>) untuk throughput tinggi.</td>
                    <td><code class="inline">config/octane.php</code></td>
                </tr>
                <tr>
                    <td><strong>Laravel Pulse</strong></td>
                    <td><span class="badge badge-tech">Health Monitor</span></td>
                    <td>Dashboard telemetri CPU, memori, slow queries, dan antrean aplikasi.</td>
                    <td><code class="inline">config/pulse.php</code></td>
                </tr>
                <tr>
                    <td><strong>Spatie Backup v9</strong></td>
                    <td><span class="badge badge-tech">Data Safety</span></td>
                    <td>Pencadangan database MySQL dan penyimpanan berkas otomatis terarsip.</td>
                    <td><code class="inline">config/backup.php</code></td>
                </tr>
            </tbody>
        </table>

        <h3>2.2 Persyaratan Lingkungan (Prerequisites)</h3>
        <ul>
            <li><strong>Sistem Operasi:</strong> Windows 10/11, Ubuntu 22.04 LTS / Debian, atau macOS Sonoma/Sequoia.</li>
            <li><strong>PHP:</strong> Versi 8.2.x atau 8.3.x (Ekstensi wajib: <code class="inline">pdo_mysql</code>, <code class="inline">curl</code>, <code class="inline">zip</code>, <code class="inline">mbstring</code>, <code class="inline">openssl</code>, <code class="inline">gd</code> / <code class="inline">imagick</code>, <code class="inline">redis</code>).</li>
            <li><strong>Node.js &amp; NPM:</strong> Node.js versi LTS v20.x atau v24.x dengan NPM versi 10+.</li>
            <li><strong>Basis Data:</strong> MySQL versi 8.0+ atau MariaDB versi 10.4+.</li>
            <li><strong>In-Memory Cache (Opsional tapi Direkomendasikan):</strong> Redis Server versi 7.0+.</li>
        </ul>
    </div>

    <!-- ==================== BAB 3: INSTALASI & OPERASIONAL 4 SERVER ==================== -->
    <div id="bab-3" class="chapter-container">
        <div class="section-header">
            <div class="section-title">BAB 3: INSTALASI CEPAT, KONFIGURASI ENV, &amp; OPERASIONAL 4 SERVER</div>
            <div class="section-desc">Panduan Setup Awal dan Prosedur Menjalankan Seluruh Proses Aplikasi</div>
        </div>

        <h3>3.1 Langkah demi Langkah Instalasi Awal (Setup 1 Kali)</h3>
        <pre># 1. Kloning Repositori Resmi dari GitHub &amp; Masuk ke Folder Proyek
git clone https://github.com/paradigma-monolith/sadaya.git
cd sadaya

# 2. Salin berkas konfigurasi template lingkungan .env
cp .env.example .env

# 3. Pasang dependensi PHP dan Node.js secara lengkap
composer install --no-interaction --prefer-dist
npm install

# 4. Generate Application Encryption Key & Storage Symlink
php artisan key:generate
php artisan storage:link

# 5. Pastikan database MySQL 'hybrid_laravel' telah dibuat di MySQL Server Anda
# Jalankan migrasi seluruh tabel database dan seeder data awal:
php artisan migrate:fresh --seed</pre>

        <h3>3.2 Kamus Variabel Lingkungan Kunci (<code class="inline">.env</code>)</h3>
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 25%;">Variabel Lingkungan</th>
                    <th style="width: 25%;">Nilai Rekomendasi Lokal</th>
                    <th style="width: 50%;">Fungsi &amp; Dampak Terhadap Aplikasi</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><code class="inline">APP_ENV</code> &amp; <code class="inline">APP_DEBUG</code></td>
                    <td><code class="inline">local</code> &bull; <code class="inline">true</code></td>
                    <td>Menentukan mode debug dan detail stack trace kesalahan.</td>
                </tr>
                <tr>
                    <td><code class="inline">APP_URL</code></td>
                    <td><code class="inline">http://127.0.0.1:8000</code></td>
                    <td>URL dasar aplikasi untuk aset, tautan email, dan redirect SSO.</td>
                </tr>
                <tr>
                    <td><code class="inline">DB_DATABASE</code></td>
                    <td><code class="inline">hybrid_laravel</code></td>
                    <td>Nama database MySQL yang menampung tabel aplikasi.</td>
                </tr>
                <tr>
                    <td><code class="inline">BROADCAST_CONNECTION</code></td>
                    <td><code class="inline">reverb</code></td>
                    <td>Driver penyiaran event real-time ke client browser.</td>
                </tr>
                <tr>
                    <td><code class="inline">REVERB_SERVER_PORT</code></td>
                    <td><code class="inline">8080</code></td>
                    <td>Port TCP yang digunakan oleh proses server Laravel Reverb.</td>
                </tr>
                <tr>
                    <td><code class="inline">SCOUT_DRIVER</code></td>
                    <td><code class="inline">database</code></td>
                    <td>Engine pencarian teks penuh (dapat diubah ke Meilisearch).</td>
                </tr>
                <tr>
                    <td><code class="inline">SESSION_DRIVER</code></td>
                    <td><code class="inline">database</code> / <code class="inline">redis</code></td>
                    <td>Driver penyimpanan sesi stateful pengguna.</td>
                </tr>
            </tbody>
        </table>

        <h3>3.3 Menjalankan 4 Server Proses Simultan (Multi-Process Server)</h3>
        <p>
            Untuk memastikan seluruh fitur (Backend HTTP, Frontend HMR, WebSockets dua arah, dan Worker Performa Tinggi) berjalan secara prima, jalankan terminal proses berikut:
        </p>
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 24%;">Servis Aplikasi</th>
                    <th style="width: 44%;">Perintah Terminal Eksekusi</th>
                    <th style="width: 32%;">Port &amp; Endpoint Akses</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>Laravel Backend</strong></td>
                    <td><code class="inline">php artisan serve --port=8000</code></td>
                    <td><a href="http://127.0.0.1:8000" target="_blank">http://127.0.0.1:8000 ↗</a></td>
                </tr>
                <tr>
                    <td><strong>Vite Frontend HMR</strong></td>
                    <td><code class="inline">npm run dev</code></td>
                    <td><a href="http://localhost:5173" target="_blank">http://localhost:5173 ↗</a></td>
                </tr>
                <tr>
                    <td><strong>Reverb WebSockets</strong></td>
                    <td><code class="inline">php artisan reverb:start --port=8080</code></td>
                    <td><code class="inline">ws://127.0.0.1:8080</code> (Real-Time)</td>
                </tr>
                <tr>
                    <td><strong>Octane RoadRunner</strong></td>
                    <td><code class="inline">./rr.exe serve</code></td>
                    <td>High-Performance Worker Server</td>
                </tr>
            </tbody>
        </table>

        <div class="callout callout-success">
            <div class="callout-title">Kredensial Login Bawaan (Dihasilkan Otomatis oleh Database Seeder)</div>
            &bull; <strong>Akun Super Admin:</strong> <code class="inline">admin@hybrid.local</code> &nbsp;|&nbsp; <strong>Password:</strong> <code class="inline">password</code> (Hak Akses Penuh &amp; Impersonasi)<br>
            &bull; <strong>Akun Staff:</strong> <code class="inline">staff@hybrid.local</code> &nbsp;|&nbsp; <strong>Password:</strong> <code class="inline">password</code> (Hak Akses Operasional Standar)<br>
            &bull; <strong>Akun Regular User:</strong> <code class="inline">user@hybrid.local</code> &nbsp;|&nbsp; <strong>Password:</strong> <code class="inline">password</code> (Hak Akses Pengguna Biasa)
        </div>
    </div>

    <!-- ==================== BAB 4: TUTORIAL MENAMBAH HALAMAN ==================== -->
    <div id="bab-4" class="chapter-container">
        <div class="section-header">
            <div class="section-title">BAB 4: TUTORIAL MENAMBAH HALAMAN BARU (INERTIA VS SPA)</div>
            <div class="section-desc">Panduan Praktis Pengembangan Modul pada Sisi Inertia Bridge dan Sisi SPA</div>
        </div>

        <h3>4.1 Menambahkan Modul Baru di Inertia.js Bridge</h3>
        <ol>
            <li><strong>Langkah 1 - Buat Controller Laravel:</strong>
                <pre>namespace App\Http\Controllers;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\User;

class ProductController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Products/Index', [
            'products' => User::latest()->paginate(10),
            'title' => 'Katalog Produk Utama',
        ]);
    }
}</pre>
            </li>
            <li><strong>Langkah 2 - Daftarkan Rute di <code class="inline">routes/web.php</code>:</strong>
                <pre>Route::middleware(['auth'])->group(function () {
    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
});</pre>
            </li>
            <li><strong>Langkah 3 - Buat Komponen Tampilan di <code class="inline">resources/js/Pages/Products/Index.vue</code>:</strong>
                <pre>&lt;script setup&gt;
import HybridNavbar from '@/Components/HybridNavbar.vue';
import BaseDataTable from '@/Components/BaseDataTable.vue';

defineProps({ products: Object, title: String });

const columns = [
  { key: 'name', label: 'Nama Pengguna', sortable: true },
  { key: 'email', label: 'Email Kantor', sortable: true },
];
&lt;/script&gt;

&lt;template&gt;
  &lt;div class="min-h-screen bg-slate-50 dark:bg-slate-950"&gt;
    &lt;HybridNavbar mode="inertia" currentRoute="products" /&gt;
    &lt;main class="max-w-7xl mx-auto px-4 py-8"&gt;
      &lt;h1 class="text-xl font-bold text-slate-900 dark:text-white mb-6"&gt;@{{ title }}&lt;/h1&gt;
      &lt;BaseDataTable :columns="columns" :data="products" /&gt;
    &lt;/main&gt;
  &lt;/div&gt;
&lt;/template&gt;</pre>
            </li>
        </ol>

        <h3>4.2 Menambahkan Modul Baru di Traditional SPA</h3>
        <ol>
            <li><strong>Langkah 1 - Buat View SPA:</strong> Buat berkas di <code class="inline">resources/js/spa/views/AnalyticsView.vue</code>.</li>
            <li><strong>Langkah 2 - Daftarkan di Vue Router (<code class="inline">resources/js/spa/router/index.js</code>):</strong>
                <pre>{
  path: '/analytics',
  name: 'spa-analytics',
  component: () => import('../views/AnalyticsView.vue'),
}</pre>
            </li>
            <li><strong>Langkah 3 - Ambil Data Asinkron via Axios:</strong>
                <pre>import axios from 'axios';
import { ref, onMounted } from 'vue';

const data = ref([]);
const loading = ref(true);

onMounted(async () => {
  try {
    const res = await axios.get('/api/v1/users');
    data.value = res.data;
  } finally {
    loading.value = false;
  }
});</pre>
            </li>
        </ol>

        <h3>4.3 Matriks Keputusan: Memilih Antara Inertia dan SPA</h3>
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 30%;">Skenario Kebutuhan</th>
                    <th style="width: 25%;">Solusi Direkomendasikan</th>
                    <th style="width: 45%;">Pertimbangan Teknis</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Fitur CRUD Standar &amp; Laporan</td>
                    <td><span class="badge badge-post">Inertia.js Bridge</span></td>
                    <td>Pengembangan 3x lebih cepat, validasi form otomatis, zero API overhead.</td>
                </tr>
                <tr>
                    <td>Modul Dashboard Interaktif Real-Time</td>
                    <td><span class="badge badge-get">Traditional SPA</span></td>
                    <td>Navigasi instan antar tab tanpa reload, state caching di Pinia store.</td>
                </tr>
                <tr>
                    <td>Formulir Transaksional Kompleks</td>
                    <td><span class="badge badge-post">Inertia.js Bridge</span></td>
                    <td>Penanganan error validasi bawaan Laravel dengan helper <code class="inline">useForm()</code>.</td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- ==================== BAB 5: KEAMANAN & RBAC ==================== -->
    <div id="bab-5" class="chapter-container">
        <div class="section-header">
            <div class="section-title">BAB 5: KEAMANAN ENTERPRISE, RBAC, AUDIT TRAIL, &amp; IMPERSONASI</div>
            <div class="section-desc">Manajemen Akses Berlapis, Jejak Forensik Data, dan Keselamatan Operasional</div>
        </div>

        <h3>5.1 Otorisasi Berbasis Peran (Spatie Laravel Permission)</h3>
        <p>
            Sistem menerapkan pemisahan tugas secara ketat melalui sistem Role &amp; Permission yang dapat diuji secara deklaratif:
        </p>
        <pre>// 1. Pengecekan Izin di Controller Laravel
public function destroy(User $user) {
    if (!auth()->user()->can('delete users')) {
        abort(403, 'Akses Ditolak: Anda tidak memiliki izin menghapus pengguna.');
    }
    $user->delete();
}

// 2. Proteksi Rute Melalui Middleware
Route::get('/admin/security', [AdminController::class, 'security'])
    ->middleware(['auth', 'role:Super Admin']);</pre>

        <h3>5.2 Sistem Jejak Audit Otomatis (Spatie Laravel Activitylog)</h3>
        <p>
            Model <code class="inline">User</code> secara bawaan mengimplementasikan trait <code class="inline">LogsActivity</code>. Setiap perubahan atribut (nama, email, peran) otomatis terekam tanpa penulisan logika repetitif:
        </p>
        <pre>// Mengambil 10 riwayat audit log aktivitas terbaru dari pengguna aktif
$logs = \Spatie\Activitylog\Models\Activity::where('causer_id', auth()->id())
    ->latest()
    ->take(10)
    ->get();</pre>

        <p>
            Selain audit log mutasi, sistem juga mencatat rekaman riwayat login pengguna pada tabel <code class="inline">login_histories</code> yang menyimpan alamat IP, browser User-Agent, dan status keberhasilan login untuk deteksi anomali keamanan.
        </p>

        <h3>5.3 Super Admin Impersonation (Bantuan Pengguna Langsung)</h3>
        <p>
            Fitur esensial untuk tim dukungan teknis dan Super Admin untuk mereproduksi kendala yang dialami staf secara presisi tanpa perlu mengetahui kata sandi mereka:
        </p>
        <ul>
            <li><strong>Cara Menyamar:</strong> Akses tautan <code class="inline">/impersonate/take/{userId}</code> (hanya dapat dieksekusi oleh Super Admin).</li>
            <li><strong>Safety Warning Banner:</strong> Banner visual berwarna oranye mencolok otomatis aktif di navigasi atas (<code class="inline">HybridNavbar.vue</code>) untuk mengingatkan status penyamaran.</li>
            <li><strong>Kembali ke Akun Asli:</strong> Klik tombol *"Kembali ke Super Admin ✕"* pada banner atau kunjungi tautan rute <code class="inline">/impersonate/leave</code>.</li>
        </ul>

        <h3>5.4 Proteksi Keamanan Bawaan Lainnya</h3>
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 25%;">Fitur Keamanan</th>
                    <th style="width: 35%;">Mekanisme Penanganan</th>
                    <th style="width: 40%;">Standar Keamanan</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>CSRF Protection</strong></td>
                    <td>Handshake token <code class="inline">XSRF-TOKEN</code> otomatis via Sanctum</td>
                    <td>Mencegah serangan pemalsuan permintaan antar situs.</td>
                </tr>
                <tr>
                    <td><strong>SQL Injection Immunity</strong></td>
                    <td>Parameter Binding otomatis via PDO Eloquent ORM</td>
                    <td>Menolak injeksi query berbahaya pada seluruh model.</td>
                </tr>
                <tr>
                    <td><strong>API Rate Limiting</strong></td>
                    <td>Middleware <code class="inline">throttle:60,1</code> pada rute <code class="inline">api.php</code></td>
                    <td>Mencegah serangan Denial of Service (DoS) dan brute-force.</td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- ==================== BAB 6: KATALOG KOMPONEN UI ==================== -->
    <div id="bab-6" class="chapter-container">
        <div class="section-header">
            <div class="section-title">BAB 6: KATALOG KOMPONEN UI, PETA, GRAFIK, &amp; INTERAKTIVITAS</div>
            <div class="section-desc">Koleksi Komponen Siap Pakai yang Responsif Tema Gelap &amp; Terang</div>
        </div>

        <h3>6.1 Tabel Data Enterprise (<code class="inline">BaseDataTable.vue</code>)</h3>
        <p>
            Komponen data tabel serbaguna yang mendukung pagination data server-side dari Laravel maupun filtering lokal client-side:
        </p>
        <pre>&lt;script setup&gt;
import BaseDataTable from '@/Components/BaseDataTable.vue';
import { useDate } from '@/composables/useDate';
const { formatDate } = useDate();

const columns = [
  { key: 'name', label: 'Nama Lengkap', sortable: true },
  { key: 'email', label: 'Email Kantor', sortable: true },
  { key: 'created_at', label: 'Terdaftar Sejak', sortable: true },
];
&lt;/script&gt;

&lt;template&gt;
  &lt;BaseDataTable :columns="columns" :data="users" :searchable="true"&gt;
    &lt;template #cell(created_at)="{ value }"&gt;
      @{{ formatDate(value) }}
    &lt;/template&gt;
  &lt;/BaseDataTable&gt;
&lt;/template&gt;</pre>

        <h3>6.2 Visualisasi Grafik (<code class="inline">BaseChart.vue</code>) &amp; Peta 3D (<code class="inline">BaseMap.vue</code>)</h3>
        <pre>// 1. Grafik Apache ECharts Responsif Dark/Light Mode
&lt;BaseChart :option="{
  xAxis: { type: 'category', data: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei'] },
  yAxis: { type: 'value' },
  series: [{ data: [120, 200, 150, 280, 220], type: 'line', smooth: true }]
}" height="300px" /&gt;

// 2. Peta Geospasial MapLibre GL dengan Marker 3D
&lt;BaseMap :markers="[
  { id: 1, name: 'Pusat Operasional Jakarta', coordinates: [106.8272, -6.1751], status: 'online' }
]" height="350px" /&gt;</pre>

        <h3>6.3 Command Palette (<code class="inline">Ctrl + K</code>) &amp; Toast Notifikasi</h3>
        <ul>
            <li><strong>Membuka Modal:</strong> Tekan pintasan keyboard <code class="inline">Ctrl + K</code> (atau <code class="inline">Cmd + K</code> pada macOS).</li>
            <li><strong>Pencarian Cepat:</strong> Mengetik kata kunci akan mencari menu navigasi, fungsi sistem, dan akun pengguna via Laravel Scout secara instan.</li>
            <li><strong>Toast Alert (<code class="inline">vue-sonner</code>):</strong>
                <pre>import { toast } from 'vue-sonner';
toast.success('Data profil berhasil diperbarui!');
toast.error('Terjadi kesalahan koneksi ke server.');</pre>
            </li>
        </ul>

        <h3>6.4 Format Tanggal Lokal Indonesia (<code class="inline">useDate.js</code>)</h3>
        <p>
            Composable terpusat untuk menampilkan tanggal terstandarisasi dengan bahasa Indonesia:
        </p>
        <pre>import { useDate } from '@/composables/useDate';
const { formatDate, timeAgo } = useDate();

formatDate('2026-10-25'); // Output: '25 Oktober 2026'
timeAgo('2026-10-25 10:00:00'); // Output: '10 menit yang lalu'</pre>
    </div>

    <!-- ==================== BAB 7: LAYANAN DATA & MEDIA ==================== -->
    <div id="bab-7" class="chapter-container">
        <div class="section-header">
            <div class="section-title">BAB 7: LAYANAN DATA: PENCARIAN SCOUT, MEDIA WEBP, &amp; BACKUP</div>
            <div class="section-desc">Penyimpanan Berkas Terpusat, Full-Text Search, QueryBuilder, &amp; Cadangan Sistem</div>
        </div>

        <h3>7.1 Manajemen Berkas &amp; WebP Auto-Conversion (Spatie MediaLibrary)</h3>
        <p>
            Model pengguna secara terintegrasi mengimplementasikan interface <code class="inline">HasMedia</code> lengkap dengan thumbnail non-queued:
        </p>
        <pre>// Mengunggah dan mengaitkan berkas avatar pengguna:
$user = auth()->user();
$media = $user->addMediaFromRequest('file')->toMediaCollection('avatars');

// Mengambil URL berkas asli dan URL konversi WebP:
$fullUrl = $user->getFirstMediaUrl('avatars');
$thumbUrl = $user->getFirstMediaUrl('avatars', 'thumb'); // Ukuran 150x150 WebP hemat bandwidth</pre>

        <h3>7.2 Pencarian Teks Penuh Terindeks (Laravel Scout)</h3>
        <p>
            Model didaftarkan dengan trait <code class="inline">Searchable</code> dan metode <code class="inline">toSearchableArray()</code>. Pencarian cepat teks penuh dapat dijalankan secara instan:
        </p>
        <pre>// Menjalankan pencarian teks penuh pada model User
$users = User::search('budi santoso')->get();</pre>

        <h3>7.3 Server-Side Query Builder (Spatie QueryBuilder)</h3>
        <p>
            Memungkinkan filtering dan sorting dinamis langsung dari URL parameter tanpa penulisan logika controller yang panjang:
        </p>
        <pre>$users = QueryBuilder::for(User::class)
    ->allowedFilters(['name', 'email'])
    ->allowedSorts(['id', 'name', 'created_at'])
    ->defaultSort('-created_at')
    ->paginate(15);</pre>

        <h3>7.4 Pencadangan Database &amp; Berkas Otomatis (Spatie Backup)</h3>
        <pre># 1. Menjalankan pencadangan database MySQL saja:
php artisan backup:run --only-db

# 2. Menjalankan pencadangan penuh (Database + Seluruh Berkas Lampiran):
php artisan backup:run

# 3. Membersihkan berkas arsip backup lama sesuai batas retensi:
php artisan backup:clean</pre>
    </div>

    <!-- ==================== BAB 8: WEBSOCKETS & MONITORING ==================== -->
    <div id="bab-8" class="chapter-container">
        <div class="section-header">
            <div class="section-title">BAB 8: REAL-TIME WEBSOCKETS REVERB, CHAT, &amp; TELEMETRI PULSE</div>
            <div class="section-desc">Komunikasi Dua Arah Berkecepatan Tinggi dan Pemantauan Performa Server</div>
        </div>

        <h3>8.1 Laravel Reverb WebSocket Server</h3>
        <p>
            Laravel Reverb adalah engine WebSocket lokal berperforma sangat tinggi yang terhubung mulus dengan Laravel Echo di Vue 3 tanpa ketergantungan pada Pusher cloud:
        </p>
        <pre>// Di sisi Controller: Menyiarkan Event Real-Time
broadcast(new ChatMessageSent($message))->toOthers();

// Di sisi Vue 3: Mendengarkan Event Real-Time melalui Private Channel
window.Echo.private(`chat.${roomId}`)
  .listen('ChatMessageSent', (e) => {
    messages.value.push(e.message);
  });</pre>

        <h3>8.2 Modul Live Chat Real-Time</h3>
        <p>
            Sistem dilengkapi antarmuka live chat interaktif di mana pesan yang dikirimkan oleh satu pengguna langsung muncul di layar pengguna lain dalam hitungan milidetik lengkap dengan efek suara denting (<em>audio chime</em>).
        </p>

        <h3>8.3 Pemantauan Performa Aplikasi (Laravel Pulse)</h3>
        <p>
            Akses dashboard pemantauan internal di <a href="http://127.0.0.1:8000/pulse" target="_blank">http://127.0.0.1:8000/pulse ↗</a> untuk menganalisis metrik server:
        </p>
        <ul>
            <li><strong>Slow Queries:</strong> Query database MySQL yang memakan durasi eksekusi > 100ms.</li>
            <li><strong>Slow HTTP Requests:</strong> Endpoint HTTP yang melampaui batas ambang respon server.</li>
            <li><strong>Queue Performance:</strong> Laju pemrosesan background jobs dan log pekerjaan yang gagal.</li>
            <li><strong>Server Resources:</strong> Utilisasi CPU dan penggunaan memori RAM secara real-time.</li>
        </ul>

        <h3>8.4 Error Tracking &amp; Observabilitas (Sentry)</h3>
        <p>
            Setiap exception fatal atau kegagalan transaksi di backend dan unhandled error di Vue frontend dapat dikirimkan langsung ke dashboard Sentry melalui integrasi log channel yang telah terpasang.
        </p>
    </div>

    <!-- ==================== BAB 9: API & CHECKLIST RILIS ==================== -->
    <div id="bab-9" class="chapter-container">
        <div class="section-header">
            <div class="section-title">BAB 9: KATALOG REST API, OPENAPI SCRAMBLE, &amp; CHECKLIST RILIS</div>
            <div class="section-desc">Katalog Lengkap REST API, GUI Dokumentasi, Pengujian Otomatis, &amp; Standarisasi Rilis</div>
        </div>

        <h3>9.1 Dokumentasi Interaktif Scramble OpenAPI</h3>
        <p>
            Aplikasi menyediakan antarmuka dokumentasi API interaktif tanpa konfigurasi anotasi rumit. Anda dapat menguji endpoint secara langsung melalui GUI di peramban:
            <br>
            &bull; <strong>Tautan Akses OpenAPI:</strong> <a href="http://127.0.0.1:8000/docs/api" target="_blank">http://127.0.0.1:8000/docs/api ↗</a>
        </p>

        <h3>9.2 Katalog Lengkap REST API Endpoints</h3>
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 12%;">Method</th>
                    <th style="width: 32%;">Endpoint URL</th>
                    <th style="width: 16%;">Autentikasi</th>
                    <th style="width: 40%;">Fungsi &amp; Output Response</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><span class="badge badge-get">GET</span></td>
                    <td><code class="inline">/api/v1/stats</code></td>
                    <td>Public</td>
                    <td>Mengambil status telemetri modul &amp; kesehatan servis.</td>
                </tr>
                <tr>
                    <td><span class="badge badge-get">GET</span></td>
                    <td><code class="inline">/api/v1/search?q={query}</code></td>
                    <td>Sanctum</td>
                    <td>Pencarian teks penuh cepat via Laravel Scout.</td>
                </tr>
                <tr>
                    <td><span class="badge badge-get">GET</span></td>
                    <td><code class="inline">/api/v1/users</code></td>
                    <td>Sanctum</td>
                    <td>Daftar pengguna terpaginasi via Spatie QueryBuilder.</td>
                </tr>
                <tr>
                    <td><span class="badge badge-post">POST</span></td>
                    <td><code class="inline">/api/v1/media/upload</code></td>
                    <td>Sanctum</td>
                    <td>Mengunggah berkas avatar via Spatie MediaLibrary.</td>
                </tr>
                <tr>
                    <td><span class="badge badge-get">GET</span></td>
                    <td><code class="inline">/api/v1/media</code></td>
                    <td>Sanctum</td>
                    <td>Mendapatkan koleksi berkas media milik pengguna.</td>
                </tr>
                <tr>
                    <td><span class="badge badge-delete">DELETE</span></td>
                    <td><code class="inline">/api/v1/media/{id}</code></td>
                    <td>Sanctum</td>
                    <td>Menghapus berkas lampiran media berdasarkan ID.</td>
                </tr>
                <tr>
                    <td><span class="badge badge-get">GET</span></td>
                    <td><code class="inline">/api/v1/profile</code></td>
                    <td>Sanctum</td>
                    <td>Melihat data profil akun dan peran aktif saat ini.</td>
                </tr>
                <tr>
                    <td><span class="badge badge-patch">PATCH</span></td>
                    <td><code class="inline">/api/v1/profile</code></td>
                    <td>Sanctum</td>
                    <td>Memperbarui nama dan email pengguna aktif.</td>
                </tr>
                <tr>
                    <td><span class="badge badge-get">GET</span></td>
                    <td><code class="inline">/api/v1/audit-logs</code></td>
                    <td>Sanctum</td>
                    <td>Melihat riwayat log jejak audit aktivitas sistem.</td>
                </tr>
            </tbody>
        </table>

        <h3>9.3 Pengujian Otomatis (56 Automated Tests Passed)</h3>
        <p>
            Pastikan integritas kode tetap terjaga dengan menjalankan rangkaian pengujian otomatis lengkap:
        </p>
        <pre># Menjalankan seluruh 56 pengujian Unit & Feature Test
php artisan test</pre>

        <h3>9.4 Checklist Lengkap Standarisasi Rilis Produksi</h3>
        <ol>
            <li><strong>Verifikasi Pengujian:</strong> Pastikan seluruh 56 tests lulus tanpa ada error atau kegagalan.</li>
            <li><strong>Konfigurasi Lingkungan:</strong> Setel <code class="inline">APP_ENV=production</code>, <code class="inline">APP_DEBUG=false</code>, dan sesuaikan <code class="inline">APP_URL=https://domainanda.com</code>.</li>
            <li><strong>Optimasi Cache Laravel:</strong> Jalankan <code class="inline">php artisan config:cache &amp;&amp; php artisan route:cache &amp;&amp; php artisan view:cache</code>.</li>
            <li><strong>Kompilasi Aset Frontend:</strong> Jalankan <code class="inline">npm run build</code> untuk memproduksi bundle JavaScript &amp; CSS terkompresi.</li>
            <li><strong>Supervisor Queue &amp; Reverb:</strong> Pasang konfigurasi proses supervisor untuk menjaga servis worker antrean dan Reverb tetap berjalan di background.</li>
            <li><strong>Penjadwalan Otomatis:</strong> Pasang entri cron job Laravel di server (<code class="inline">* * * * * php /path-to-app/artisan schedule:run >> /dev/null 2>&1</code>) untuk menjalankan backup berkala.</li>
        </ol>

        <div style="margin-top: 25px; text-align: center; color: #94a3b8; font-size: 8.5px; border-top: 1px solid #e2e8f0; padding-top: 10px;">
            Dokumen Manual Book ini diterbitkan secara otomatis oleh <strong>Paradigma Monolith Ecosystem</strong> &bull; Dilisensikan di bawah Lisensi MIT.
        </div>
    </div>

    <!-- ==================== BAB 10: DEPLOYMENT SERVER ==================== -->
    <div id="bab-10" class="chapter-container">
        <div class="section-header">
            <div class="section-title">BAB 10: PANDUAN DEPLOYMENT PRODUKSI &amp; KONFIGURASI SERVER</div>
            <div class="section-desc">Instalasi Linux LEMP, Reverse Proxy Nginx WSS, Supervisor Daemon, &amp; Otomasi Rilis</div>
        </div>

        <h3>10.1 Arsitektur Server Linux Produksi (Ubuntu 22.04 / 24.04 LTS)</h3>
        <p>
            Aplikasi menggunakan tumpukan <strong>LEMP Stack Terpadu</strong> yang dilengkapi dengan server WebSocket mandiri (Laravel Reverb), cache performa tinggi (Redis), dan antrean asinkron (Supervisor):
        </p>

        <div class="diagram-container">
[CLIENT BROWSER] ──HTTPS (443) / WSS (443/app)──> [NGINX REVERSE PROXY]
                                                        │
                      ┌─────────────────────────────────┴─────────────────────────────────┐
                      │ Pass /index.php                                                   │ Pass /app/* (WSS)
                      ▼                                                                   ▼
       [PHP-FPM / LARAVEL 12]                                              [LARAVEL REVERB DAEMON]
       - Inertia.js Bridge &amp; Traditional SPA                               - WebSockets Engine (Port 8080)
       - Sanctum Cookie SSO Auth                                           - Managed by Supervisor
                      │                                                                   │
                      └─────────────────────────────────┬─────────────────────────────────┘
                                                        ▼
                                       [INFRASTRUCTURE &amp; DATA LAYER]
                                       - MySQL 8.0: Database transaksi utama
                                       - Redis 7.x: Session Driver, Cache, &amp; Queue
                                       - Supervisor: Queue Worker (2 proses latar belakang)
                                       - Crontab: Laravel Scheduler &amp; Spatie Backup
        </div>

        <h3>10.2 Konfigurasi Web Server Nginx &amp; Reverse Proxy WebSockets</h3>
        <p>
            Berkas konfigurasi resmi telah disediakan di berkas <code class="inline">deploy/nginx/app.conf</code>. Bagian krusial untuk koneksi WebSocket WSS aman melalui port 443 adalah blok direktif berikut:
        </p>
        <pre># Reverse Proxy WebSocket Laravel Reverb (WSS Port 443)
location /app/ {
    proxy_pass http://127.0.0.1:8080;
    proxy_http_version 1.1;
    proxy_set_header Upgrade $http_upgrade;
    proxy_set_header Connection "Upgrade";
    proxy_set_header Host $host;
    proxy_set_header X-Real-IP $remote_addr;
    proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
    proxy_set_header X-Forwarded-Proto $scheme;
    proxy_read_timeout 600s;
    proxy_send_timeout 600s;
}

# FastCGI Handler PHP 8.2+
location ~ \.php$ {
    fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
    fastcgi_index index.php;
    fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
    include fastcgi_params;
}</pre>

        <h3>10.3 Manajemen Daemon Latar Belakang (Supervisor)</h3>
        <p>
            Supervisor memastikan server WebSocket Reverb dan background queue worker selalu hidup 24/7 di latar belakang. Konfigurasi telah tersedia di <code class="inline">deploy/supervisor/</code>:
        </p>
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 28%;">Nama Berkas Supervisor</th>
                    <th style="width: 42%;">Perintah Daemon yang Dijalankan</th>
                    <th style="width: 30%;">Tanggung Jawab Operasional</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><code class="inline">starterkit-reverb.conf</code></td>
                    <td><code class="inline">php artisan reverb:start --port=8080</code></td>
                    <td>Menjaga server WebSocket aktif terus-menerus.</td>
                </tr>
                <tr>
                    <td><code class="inline">starterkit-worker.conf</code></td>
                    <td><code class="inline">php artisan queue:work --sleep=3</code></td>
                    <td>Memproses background jobs, email, dan ekspor data.</td>
                </tr>
                <tr>
                    <td><code class="inline">starterkit-octane.conf</code></td>
                    <td><code class="inline">php artisan octane:start --server=roadrunner</code></td>
                    <td>Worker super cepat (opsional pengganti PHP-FPM).</td>
                </tr>
            </tbody>
        </table>

        <h3>10.4 Penjadwalan Otomatis (Crontab) &amp; Script Otomasi Rilis</h3>
        <p>
            Tambahkan penjadwalan Laravel pada crontab pengguna web server (<code class="inline">sudo crontab -u www-data -e</code>):
        </p>
        <pre>* * * * * cd /var/www/starterkit_paradigma &amp;&amp; php artisan schedule:run &gt;&gt; /dev/null 2&gt;&amp;1</pre>

        <p>
            Untuk merilis pembaruan versi baru dari GitHub ke server produksi, jalankan script otomasi satu baris bawaan di <code class="inline">deploy/deploy.sh</code>:
        </p>
        <pre># Eksekusi deployment zero-downtime otomatis:
sudo /var/www/starterkit_paradigma/deploy/deploy.sh</pre>

        <div class="callout callout-info">
            <div class="callout-title">Panduan Lengkap Terintegrasi</div>
            Dokumentasi instalasi server dari awal (Ubuntu 22.04 LTS, MySQL, Redis, firewall UFW, dan instalasi sertifikat SSL gratis via Certbot Let's Encrypt) dapat Anda pelajari secara komprehensif pada dokumen repositori <a href="https://github.com/paradigma-monolith/sadaya/blob/main/DEPLOYMENT.md" target="_blank">DEPLOYMENT.md ↗</a>.
        </div>

        <div style="margin-top: 25px; text-align: center; color: #94a3b8; font-size: 8.5px; border-top: 1px solid #e2e8f0; padding-top: 10px;">
            Dokumen Manual Book ini diterbitkan secara otomatis oleh <strong>Paradigma Monolith Ecosystem</strong> &bull; Dilisensikan di bawah Lisensi MIT.
        </div>
    </div>

</body>
</html>
