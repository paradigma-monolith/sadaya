<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sertifikat Pengendalian Sanitasi Kapal &bull; BKK Kelas I Bandung</title>

    <!-- Google Fonts & Tailwind CDN -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'system-ui', '-apple-system', 'sans-serif'],
                        mono: ['"JetBrains Mono"', 'monospace'],
                    },
                    colors: {
                        primary: {
                            50: '#ecfdf5',
                            100: '#d1fae5',
                            500: '#10b981',
                            600: '#059669',
                            700: '#047857',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        /* Custom custom styling for smooth feel */
        .glass-card {
            background: #ffffff;
            border: 1px solid rgba(226, 232, 240, 0.9);
            box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.04), 0 8px 10px -6px rgba(15, 23, 42, 0.02);
        }
        .step-active {
            border-color: #059669;
            color: #059669;
            background-color: #ecfdf5;
        }
        .area-row:hover {
            background-color: #f8fafc;
        }
        .signature-canvas {
            touch-action: none;
            cursor: crosshair;
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 antialiased min-h-screen py-6 sm:py-10 px-3 sm:px-6 lg:px-8">

    <div class="max-w-5xl mx-auto space-y-6">

        <!-- Header / Kop Dokumen Resmi -->
        <header class="glass-card rounded-2xl p-6 sm:p-8 text-center relative overflow-hidden">
            <div class="absolute top-0 left-0 right-0 h-1.5 bg-gradient-to-r from-emerald-500 via-teal-500 to-indigo-600"></div>
            
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4 border-b border-slate-100 pb-5">
                <div class="flex items-center gap-3.5">
                    <div class="w-12 h-12 rounded-xl bg-slate-900 text-white flex items-center justify-center font-black text-sm tracking-widest shadow-md">
                        BKK
                    </div>
                    <div class="text-left">
                        <span class="text-[11px] font-bold tracking-wider uppercase text-emerald-600 block">KEMENTERIAN KESEHATAN RI</span>
                        <h1 class="text-base sm:text-lg font-extrabold text-slate-900 leading-tight">Balai Kekarantinaan Kesehatan Kelas I Bandung</h1>
                        <span class="text-xs text-slate-500 block">Wilayah Kerja: Cirebon, Patimban, Indramayu, Pelabuharatu &amp; Jawa Barat</span>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 mr-1.5 animate-pulse"></span>
                        IHR (2005) Compliant
                    </span>
                    <a href="{{ route('home') }}" class="text-xs font-semibold text-slate-500 hover:text-slate-900 px-3 py-1 rounded-lg border border-slate-200 hover:bg-slate-50 transition-colors">
                        ← Dashboard
                    </a>
                </div>
            </div>

            <!-- Title & Subtitle -->
            <div class="pt-5 pb-1">
                <h2 class="text-xl sm:text-2xl font-black text-slate-900 uppercase tracking-tight">
                    Sertifikat Pengendalian Sanitasi Kapal
                </h2>
                <p class="text-xs sm:text-sm text-slate-500 font-medium italic mt-0.5">
                    Ship Sanitation Control Exemption Certificate (SSCEC) / Ship Sanitation Control Certificate (SSCC)
                </p>
                <div class="inline-block mt-3 px-3 py-1 rounded-lg bg-slate-100 text-[11px] font-mono text-slate-600 font-medium">
                    Lampiran 3 Peraturan Kesehatan Internasional (WHO - International Health Regulations 2005)
                </div>
            </div>
        </header>

        <!-- Stepper Navigation Bar -->
        <nav class="glass-card rounded-xl p-2 sm:p-2.5 flex items-center justify-between gap-1 sm:gap-3 text-xs font-semibold">
            <button type="button" onclick="scrollToSection('section-part1')" class="flex-1 py-2 px-3 rounded-lg flex items-center justify-center gap-2 transition-all hover:bg-slate-100 text-slate-600">
                <span class="w-5 h-5 rounded-full bg-slate-900 text-white flex items-center justify-center text-[10px] font-bold">1</span>
                <span class="hidden sm:inline">Bagian 1:</span>
                <span>Informasi Umum</span>
            </button>
            <div class="w-4 h-[1px] bg-slate-200 hidden sm:block"></div>
            <button type="button" onclick="scrollToSection('section-part2')" class="flex-1 py-2 px-3 rounded-lg flex items-center justify-center gap-2 transition-all hover:bg-slate-100 text-slate-600">
                <span class="w-5 h-5 rounded-full bg-slate-900 text-white flex items-center justify-center text-[10px] font-bold">2</span>
                <span class="hidden sm:inline">Bagian 2:</span>
                <span>32 Area Fasilitas</span>
            </button>
            <div class="w-4 h-[1px] bg-slate-200 hidden sm:block"></div>
            <button type="button" onclick="scrollToSection('section-part3')" class="flex-1 py-2 px-3 rounded-lg flex items-center justify-center gap-2 transition-all hover:bg-slate-100 text-slate-600">
                <span class="w-5 h-5 rounded-full bg-slate-900 text-white flex items-center justify-center text-[10px] font-bold">3</span>
                <span class="hidden sm:inline">Bagian 3:</span>
                <span>Pengesahan &amp; TTD</span>
            </button>
        </nav>

        <!-- Form Element -->
        <form id="formSanitasiKapal" action="{{ route('travelconsent.submit_sanitasi') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <!-- ================================================================= -->
            <!-- BAGIAN 1: INFORMASI UMUM & IDENTITAS KAPAL                       -->
            <!-- ================================================================= -->
            <section id="section-part1" class="glass-card rounded-2xl p-6 sm:p-8 space-y-6">
                <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                    <div>
                        <h3 class="text-base sm:text-lg font-bold text-slate-900 flex items-center gap-2">
                            <span class="w-6 h-6 rounded-lg bg-emerald-100 text-emerald-800 text-xs font-black flex items-center justify-center">1</span>
                            Bagian 1: Informasi Umum &amp; Data Kapal
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">Identitas kapal, pelabuhan pemeriksaan, tonase, dan data petugas pemeriksa</p>
                    </div>
                    <span class="text-[11px] font-mono text-slate-400">Step 1 of 3</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 text-xs">
                    <!-- Balai Karkes -->
                    <div>
                        <label class="block font-semibold text-slate-700 mb-1.5">Balai Karkes Penerbit</label>
                        <input type="text" value="BKK Kelas I Bandung" readonly class="w-full px-3.5 py-2.5 rounded-xl bg-slate-100 border border-slate-200 text-slate-600 font-medium cursor-not-allowed">
                    </div>

                    <!-- Pelabuhan -->
                    <div>
                        <label for="pelabuhan" class="block font-semibold text-slate-700 mb-1.5">
                            Nama Pelabuhan / Wilker <span class="text-rose-500">*</span>
                        </label>
                        <select id="pelabuhan" name="pelabuhan" required class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-slate-200 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100 font-medium text-slate-800 transition-all outline-none">
                            <option value="">-- Pilih Pelabuhan / Wilker --</option>
                            @foreach($ports as $port)
                                <option value="{{ $port }}">{{ $port }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Tanggal -->
                    <div>
                        <label for="tanggal" class="block font-semibold text-slate-700 mb-1.5">
                            Tanggal Pemeriksaan <span class="text-rose-500">*</span>
                        </label>
                        <input type="date" id="tanggal" name="tanggal" value="{{ $today }}" required class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-slate-200 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100 font-medium text-slate-800 transition-all outline-none">
                    </div>

                    <!-- Sertifikat ini mencatat pemeriksaan dan -->
                    <div class="sm:col-span-2 lg:col-span-3 bg-slate-50/80 p-4 rounded-xl border border-slate-200/80">
                        <label class="block font-semibold text-slate-800 mb-2">
                            Sertifikat ini mencatat pemeriksaan dan: <span class="text-rose-500">*</span>
                        </label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <label class="flex items-center gap-3 p-3 rounded-xl border border-slate-200 bg-white cursor-pointer hover:border-emerald-500 transition-all">
                                <input type="radio" name="sertifikat_mencatat" value="Pembebasan" checked class="w-4 h-4 text-emerald-600 focus:ring-0">
                                <div>
                                    <span class="block font-bold text-slate-900 text-xs">Pembebasan (SSCEC)</span>
                                    <span class="block text-[11px] text-slate-500">Ship Sanitation Control Exemption Certificate</span>
                                </div>
                            </label>
                            <label class="flex items-center gap-3 p-3 rounded-xl border border-slate-200 bg-white cursor-pointer hover:border-emerald-500 transition-all">
                                <input type="radio" name="sertifikat_mencatat" value="Pengendalian" class="w-4 h-4 text-emerald-600 focus:ring-0">
                                <div>
                                    <span class="block font-bold text-slate-900 text-xs">Pengendalian (SSCC)</span>
                                    <span class="block text-[11px] text-slate-500">Ship Sanitation Control Certificate</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- IMO / Registrasi -->
                    <div>
                        <label for="imo_no" class="block font-semibold text-slate-700 mb-1.5">
                            Nomor Registrasi / IMO No <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" id="imo_no" name="imo_no" placeholder="Contoh: IMO 9123456" required class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-slate-200 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100 font-mono font-bold text-slate-900 transition-all outline-none">
                    </div>

                    <!-- Jenis Kapal -->
                    <div>
                        <label for="jenis_kapal" class="block font-semibold text-slate-700 mb-1.5">
                            Jenis Kapal <span class="text-rose-500">*</span>
                        </label>
                        <select id="jenis_kapal" name="jenis_kapal" required class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-slate-200 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100 font-medium text-slate-800 transition-all outline-none">
                            <option value="">-- Pilih Jenis --</option>
                            <option value="Penumpang">Penumpang</option>
                            <option value="Kargo">Kargo</option>
                            <option value="Tanker">Tangki / Tanker</option>
                            <option value="Kontainer">Kontainer</option>
                            <option value="Lainnya">Lainnya</option>
                        </select>
                    </div>

                    <!-- Detail Jenis Kapal -->
                    <div>
                        <label for="detail_jenis_kapal" class="block font-semibold text-slate-700 mb-1.5">
                            Detail Trayek Kapal <span class="text-rose-500">*</span>
                        </label>
                        <select id="detail_jenis_kapal" name="detail_jenis_kapal" required class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-slate-200 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100 font-medium text-slate-800 transition-all outline-none">
                            <option value="">-- Pilih Trayek --</option>
                            <option value="Domestik">Domestik (Antar Pulau)</option>
                            <option value="Internasional">Internasional</option>
                        </select>
                    </div>

                    <!-- Nama Kapal -->
                    <div>
                        <label for="nama_kapal" class="block font-semibold text-slate-700 mb-1.5">
                            Nama Kapal <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" id="nama_kapal" name="nama_kapal" placeholder="Nama lengkap kapal..." required class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-slate-200 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100 font-bold text-slate-900 transition-all outline-none">
                    </div>

                    <!-- Bendera -->
                    <div>
                        <label for="bendera" class="block font-semibold text-slate-700 mb-1.5">
                            Bendera Negara Kapal
                        </label>
                        <select id="bendera" name="bendera" class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-slate-200 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100 font-medium text-slate-800 transition-all outline-none">
                            @foreach($countries as $c)
                                <option value="{{ $c }}" {{ $c === 'Indonesia' ? 'selected' : '' }}>{{ $c }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Berat GT -->
                    <div>
                        <label for="berat_gt" class="block font-semibold text-slate-700 mb-1.5">
                            Tonase Kotor / Berat (GT) <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="number" id="berat_gt" name="berat_gt" placeholder="Contoh: 4500" min="0" required class="w-full pl-3.5 pr-12 py-2.5 rounded-xl bg-white border border-slate-200 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100 font-mono font-bold text-slate-900 transition-all outline-none">
                            <span class="absolute right-3.5 top-1/2 -translate-y-1/2 text-xs font-mono font-bold text-slate-400">GT</span>
                        </div>
                    </div>

                    <!-- Kondisi Palka -->
                    <div>
                        <label for="kondisi_palka" class="block font-semibold text-slate-700 mb-1.5">
                            Pada Waktu Diperiksa, Palka-palka: <span class="text-rose-500">*</span>
                        </label>
                        <select id="kondisi_palka" name="kondisi_palka" required class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-slate-200 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100 font-medium text-slate-800 transition-all outline-none">
                            <option value="">-- Pilih Kondisi --</option>
                            <option value="Kosong" selected>Kosong (Empty)</option>
                            <option value="Isi">Berisi / Isi Muatan (Loaded)</option>
                        </select>
                    </div>

                    <!-- Nama Pemeriksa -->
                    <div>
                        <label for="nama_pemeriksa" class="block font-semibold text-slate-700 mb-1.5">
                            Nama Petugas Pemeriksa BKK <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" id="nama_pemeriksa" name="nama_pemeriksa" placeholder="Nama lengkap petugas sanitasi..." required class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-slate-200 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100 font-semibold text-slate-900 transition-all outline-none">
                    </div>

                    <!-- Alamat Pemeriksa -->
                    <div class="sm:col-span-2 lg:col-span-3">
                        <label for="alamat_pemeriksa" class="block font-semibold text-slate-700 mb-1.5">
                            Alamat Kantor Pemeriksa BKK <span class="text-rose-500">*</span>
                        </label>
                        <textarea id="alamat_pemeriksa" name="alamat_pemeriksa" rows="2" required class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-slate-200 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100 font-medium text-slate-800 transition-all outline-none">Balai Kekarantinaan Kesehatan Kelas I Bandung, Jl. Cikapayang No.5, Bandung, Jawa Barat</textarea>
                    </div>
                </div>
            </section>

            <!-- ================================================================= -->
            <!-- BAGIAN 2: 32 AREA / FASILITAS PEMERIKSAAN STANDAR WHO IHR (2005) -->
            <!-- ================================================================= -->
            <section id="section-part2" class="glass-card rounded-2xl p-6 sm:p-8 space-y-5">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 border-b border-slate-100 pb-4">
                    <div>
                        <h3 class="text-base sm:text-lg font-bold text-slate-900 flex items-center gap-2">
                            <span class="w-6 h-6 rounded-lg bg-emerald-100 text-emerald-800 text-xs font-black flex items-center justify-center">2</span>
                            Bagian 2: Area / Fasilitas yang Diperiksa (32 Area)
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">Standar pemeriksaan sanitasi kapal Lampiran 3 International Health Regulations (IHR 2005)</p>
                    </div>

                    <!-- Live Progress Indicator -->
                    <div id="progressBadge" class="px-3.5 py-1.5 rounded-xl bg-slate-100 border border-slate-200 text-xs font-semibold text-slate-700 flex items-center gap-2">
                        <span id="progressBarRing" class="w-2.5 h-2.5 rounded-full bg-amber-500 animate-pulse"></span>
                        <span id="progressText">0 / 32 Area Terisi</span>
                    </div>
                </div>

                <!-- Batch Action Pill Buttons -->
                <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200/90 flex flex-wrap items-center justify-between gap-3 text-xs">
                    <span class="font-semibold text-slate-700">Aksi Cepat Status Semua Area:</span>
                    <div class="flex flex-wrap items-center gap-1.5">
                        <button type="button" onclick="setAllAreaStatus('Tidak Ditemukan Faktor Risiko')" class="px-3 py-1.5 rounded-lg font-bold bg-emerald-600 hover:bg-emerald-700 text-white shadow-xs transition-all cursor-pointer">
                            ✓ Tidak Ditemukan Faktor Risiko
                        </button>
                        <button type="button" onclick="setAllAreaStatus('Ditemukan Faktor Risiko')" class="px-3 py-1.5 rounded-lg font-bold bg-amber-500 hover:bg-amber-600 text-slate-950 shadow-xs transition-all cursor-pointer">
                            ⚠️ Ditemukan Faktor Risiko
                        </button>
                        <button type="button" onclick="setAllAreaStatus('Tidak Ada')" class="px-3 py-1.5 rounded-lg font-semibold bg-slate-200 hover:bg-slate-300 text-slate-800 transition-all cursor-pointer">
                            — Tidak Ada
                        </button>
                        <button type="button" onclick="setAllAreaStatus('')" class="px-3 py-1.5 rounded-lg font-semibold text-slate-500 hover:text-slate-900 border border-slate-300 hover:bg-white transition-all cursor-pointer">
                            Reset
                        </button>
                    </div>
                </div>

                <!-- 32 Area Interactive Matrix -->
                <div class="overflow-x-auto rounded-xl border border-slate-200">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-slate-100/80 text-slate-800 font-bold border-b border-slate-200 text-[11px] uppercase tracking-wider">
                                <th class="py-3 px-3 w-10 text-center">No</th>
                                <th class="py-3 px-4 min-w-[200px]">Area / Fasilitas</th>
                                <th class="py-3 px-3 min-w-[210px]">Status Pemeriksaan <span class="text-rose-500">*</span></th>
                                <th class="py-3 px-3 min-w-[150px]">Bukti Ditemukan</th>
                                <th class="py-3 px-3 min-w-[130px]">Hasil Sampel</th>
                                <th class="py-3 px-3 min-w-[140px]">Dokumen</th>
                                <th class="py-3 px-3 min-w-[150px]">Tindakan Pengendalian</th>
                                <th class="py-3 px-3 min-w-[130px]">Tgl Ulang</th>
                                <th class="py-3 px-3 min-w-[140px]">Komentar</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @foreach($standardAreas as $area)
                            <tr class="area-row transition-colors" id="area-row-{{ $area['no'] }}">
                                <td class="py-3 px-3 text-center font-mono font-bold text-slate-400">
                                    {{ $area['no'] }}
                                </td>
                                <td class="py-3 px-4">
                                    <div class="font-bold text-slate-900 leading-tight">{{ $area['nama'] }}</div>
                                    <span class="text-[10px] text-slate-400 font-medium">{{ $area['kategori'] }}</span>
                                    <input type="hidden" name="areas[{{ $area['no'] }}][nama]" value="{{ $area['nama'] }}">
                                </td>
                                <td class="py-2.5 px-3">
                                    <select name="areas[{{ $area['no'] }}][status]" required onchange="handleAreaChange(this)" class="area-status-select w-full px-2.5 py-1.5 rounded-lg border border-slate-200 text-xs font-semibold transition-all outline-none">
                                        <option value="">-- Pilih Status --</option>
                                        <option value="Tidak Ditemukan Faktor Risiko" selected>Tidak Ditemukan Faktor Risiko</option>
                                        <option value="Ditemukan Faktor Risiko">Ditemukan Faktor Risiko</option>
                                        <option value="Tidak Ada">Tidak Ada</option>
                                    </select>
                                </td>
                                <td class="py-2 px-3">
                                    <input type="text" name="areas[{{ $area['no'] }}][bukti]" placeholder="Bukti..." class="w-full px-2.5 py-1.5 rounded-lg border border-slate-200 text-xs text-slate-700 focus:border-emerald-500 outline-none">
                                </td>
                                <td class="py-2 px-3">
                                    <input type="text" name="areas[{{ $area['no'] }}][sampel]" placeholder="Sampel..." class="w-full px-2.5 py-1.5 rounded-lg border border-slate-200 text-xs text-slate-700 focus:border-emerald-500 outline-none">
                                </td>
                                <td class="py-2 px-3">
                                    <input type="text" name="areas[{{ $area['no'] }}][dokumen]" placeholder="Dokumen..." class="w-full px-2.5 py-1.5 rounded-lg border border-slate-200 text-xs text-slate-700 focus:border-emerald-500 outline-none">
                                </td>
                                <td class="py-2 px-3">
                                    <input type="text" name="areas[{{ $area['no'] }}][tindakan]" placeholder="Tindakan..." class="w-full px-2.5 py-1.5 rounded-lg border border-slate-200 text-xs text-slate-700 focus:border-emerald-500 outline-none">
                                </td>
                                <td class="py-2 px-3">
                                    <input type="date" name="areas[{{ $area['no'] }}][tanggal_ulang]" class="w-full px-2 py-1.5 rounded-lg border border-slate-200 text-xs text-slate-700 focus:border-emerald-500 outline-none">
                                </td>
                                <td class="py-2 px-3">
                                    <input type="text" name="areas[{{ $area['no'] }}][komentar]" placeholder="Komentar..." class="w-full px-2.5 py-1.5 rounded-lg border border-slate-200 text-xs text-slate-700 focus:border-emerald-500 outline-none">
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- ================================================================= -->
            <!-- BAGIAN 3: KETERANGAN, GEOTAGGING & TANDA TANGAN DIGITAL          -->
            <!-- ================================================================= -->
            <section id="section-part3" class="glass-card rounded-2xl p-6 sm:p-8 space-y-6">
                <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                    <div>
                        <h3 class="text-base sm:text-lg font-bold text-slate-900 flex items-center gap-2">
                            <span class="w-6 h-6 rounded-lg bg-emerald-100 text-emerald-800 text-xs font-black flex items-center justify-center">3</span>
                            Bagian 3: Keterangan, Pengesahan &amp; Geotagging
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">Catatan rekomendasi sanitasi, stempel lokasi GPS, dan tanda tangan digital</p>
                    </div>
                    <span class="text-[11px] font-mono text-slate-400">Step 3 of 3</span>
                </div>

                <div class="space-y-5 text-xs">
                    <!-- Pejabat Penerbit -->
                    <div>
                        <label for="pejabat_penerbit" class="block font-semibold text-slate-700 mb-1.5">
                            Nama Pejabat dan Instansi yang Menerbitkan
                        </label>
                        <input type="text" id="pejabat_penerbit" name="pejabat_penerbit" value="Kepala Balai Kekarantinaan Kesehatan Kelas I Bandung" class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-slate-200 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100 font-semibold text-slate-900 transition-all outline-none">
                    </div>

                    <!-- Keterangan -->
                    <div>
                        <label for="keterangan" class="block font-semibold text-slate-700 mb-1.5">
                            Keterangan &amp; Catatan Rekomendasi Sanitasi <span class="text-rose-500">*</span>
                        </label>
                        <textarea id="keterangan" name="keterangan" rows="3" required placeholder="Tuliskan catatan kondisi sanitasi kapal, temuan penting, atau instruksi pemeliharaan kesehatan kapal..." class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-slate-200 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100 font-medium text-slate-800 transition-all outline-none">Kapal telah diperiksa sesuai pedoman IHR (2005) dan memenuhi standar kelaikan sanitasi kapal laut.</textarea>
                    </div>

                    <!-- Grid: Tanda Tangan Digital & Foto Geotagging -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-2">
                        <!-- Tanda Tangan Canvas -->
                        <div class="p-4 rounded-xl border border-slate-200 bg-slate-50/50 space-y-2.5">
                            <div class="flex items-center justify-between">
                                <label class="font-bold text-slate-900 flex items-center gap-1.5">
                                    <span>✍️ Tanda Tangan Petugas Pemeriksa</span>
                                    <span class="text-rose-500">*</span>
                                </label>
                                <button type="button" id="btnClearSignature" class="px-2.5 py-1 rounded-md text-[11px] font-semibold text-rose-600 hover:bg-rose-50 border border-rose-200 transition-colors">
                                    Hapus TTD
                                </button>
                            </div>

                            <div class="relative bg-white rounded-xl border border-slate-300 overflow-hidden shadow-inner">
                                <canvas id="signatureCanvas" class="signature-canvas w-full h-44"></canvas>
                                <span class="absolute bottom-2 right-2 text-[10px] text-slate-400 font-mono pointer-events-none">Goreskan TTD disini</span>
                            </div>
                            <input type="hidden" name="tanda_tangan" id="tanda_tangan" required>
                            <p class="text-[11px] text-slate-500 leading-tight">Gunakan sentuhan jari (smartphone/tablet) atau mouse untuk menandatangani dokumen.</p>
                        </div>

                        <!-- Foto Geotagging & GPS Auto-Detect -->
                        <div class="p-4 rounded-xl border border-slate-200 bg-slate-50/50 space-y-2.5">
                            <div class="flex items-center justify-between">
                                <label class="font-bold text-slate-900 flex items-center gap-1.5">
                                    <span>📸 Foto Geotagging Dokumentasi</span>
                                </label>
                                <button type="button" onclick="detectGPS()" class="px-2.5 py-1 rounded-md text-[11px] font-semibold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 transition-colors flex items-center gap-1">
                                    <span>📍 Deteksi GPS</span>
                                </button>
                            </div>

                            <div class="relative border-2 border-dashed border-slate-300 hover:border-emerald-500 rounded-xl p-4 bg-white text-center transition-all">
                                <input type="file" name="foto_geotagging" id="foto_geotagging" accept="image/*" onchange="previewGeotag(this)" class="absolute inset-0 opacity-0 w-full h-full cursor-pointer z-10">
                                <div id="uploadPrompt" class="space-y-1">
                                    <div class="text-2xl text-slate-400">📷</div>
                                    <div class="font-semibold text-slate-800 text-xs">Klik atau seret foto bukti inspeksi</div>
                                    <div class="text-[10px] text-slate-400">JPG, PNG (Maks 10MB)</div>
                                </div>
                                <div id="previewWrapper" class="hidden mt-2">
                                    <img id="imgPreview" src="" alt="Preview Geotag" class="max-h-32 mx-auto rounded-lg shadow-sm border border-slate-200">
                                    <span id="fileNameLabel" class="block text-[10px] font-mono text-slate-500 mt-1 truncate"></span>
                                </div>
                            </div>

                            <!-- GPS Hidden & Display Fields -->
                            <input type="hidden" name="latitude" id="latitude">
                            <input type="hidden" name="longitude" id="longitude">
                            <input type="hidden" name="lokasi_geotag" id="lokasi_geotag">
                            <div id="gpsStatusBadge" class="hidden p-2 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 text-[11px] font-mono flex items-center justify-between">
                                <span id="gpsCoordsLabel">📍 Lat: -, Lng: -</span>
                                <span class="text-[9px] bg-emerald-600 text-white px-1.5 py-0.5 rounded font-bold">AKURAT</span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Submit Action Footer -->
            <div class="glass-card rounded-2xl p-6 sm:p-8 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div>
                    <h4 class="font-bold text-slate-900 text-sm">Verifikasi Akhir Sebelum Kirim</h4>
                    <p class="text-xs text-slate-500">Pastikan seluruh 32 area pemeriksaan telah dievaluasi dan tanda tangan petugas telah dibubuhkan.</p>
                </div>

                <div class="flex items-center gap-3 w-full sm:w-auto">
                    <button type="submit" id="btnSubmitSanitasi" class="w-full sm:w-auto px-7 py-3 rounded-xl font-bold text-sm text-white bg-emerald-600 hover:bg-emerald-500 active:scale-[0.99] shadow-md shadow-emerald-600/20 transition-all flex items-center justify-center gap-2 cursor-pointer">
                        <span>Simpan &amp; Terbitkan Sertifikat ↗</span>
                    </button>
                </div>
            </div>
        </form>

    </div>

    <!-- Scripts -->
    <script>
        function scrollToSection(id) {
            const el = document.getElementById(id);
            if (el) {
                el.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }

        // Batch Action Status
        function setAllAreaStatus(val) {
            document.querySelectorAll('.area-status-select').forEach(sel => {
                sel.value = val;
                styleSelect(sel);
            });
            updateProgress();
        }

        function handleAreaChange(sel) {
            styleSelect(sel);
            updateProgress();
        }

        function styleSelect(sel) {
            sel.classList.remove('bg-emerald-50', 'text-emerald-800', 'border-emerald-300', 'bg-amber-50', 'text-amber-800', 'border-amber-300', 'bg-slate-50', 'text-slate-600');
            if (sel.value === 'Tidak Ditemukan Faktor Risiko') {
                sel.classList.add('bg-emerald-50', 'text-emerald-800', 'border-emerald-300');
            } else if (sel.value === 'Ditemukan Faktor Risiko') {
                sel.classList.add('bg-amber-50', 'text-amber-800', 'border-amber-300');
            } else if (sel.value === 'Tidak Ada') {
                sel.classList.add('bg-slate-50', 'text-slate-600');
            }
        }

        function updateProgress() {
            const selects = document.querySelectorAll('.area-status-select');
            const total = selects.length;
            let filled = 0;
            selects.forEach(sel => {
                if (sel.value !== '') filled++;
            });

            const textEl = document.getElementById('progressText');
            const ringEl = document.getElementById('progressBarRing');
            if (textEl) {
                textEl.textContent = `${filled} / ${total} Area Terisi`;
            }
            if (ringEl) {
                if (filled === total && total > 0) {
                    ringEl.className = 'w-2.5 h-2.5 rounded-full bg-emerald-500';
                } else {
                    ringEl.className = 'w-2.5 h-2.5 rounded-full bg-amber-500 animate-pulse';
                }
            }
        }

        // Preview Geotag Photo
        function previewGeotag(input) {
            if (input.files && input.files[0]) {
                const file = input.files[0];
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('imgPreview').src = e.target.result;
                    document.getElementById('fileNameLabel').textContent = file.name;
                    document.getElementById('uploadPrompt').classList.add('hidden');
                    document.getElementById('previewWrapper').classList.remove('hidden');
                };
                reader.readAsDataURL(file);
            }
        }

        // Automatic GPS Geolocation
        function detectGPS() {
            if ('geolocation' in navigator) {
                navigator.geolocation.getCurrentPosition(
                    pos => {
                        const lat = pos.coords.latitude.toFixed(6);
                        const lng = pos.coords.longitude.toFixed(6);
                        document.getElementById('latitude').value = lat;
                        document.getElementById('longitude').value = lng;
                        document.getElementById('lokasi_geotag').value = `GPS (${lat}, ${lng})`;
                        const badge = document.getElementById('gpsStatusBadge');
                        badge.classList.remove('hidden');
                        document.getElementById('gpsCoordsLabel').textContent = `📍 Lat: ${lat}, Lng: ${lng}`;
                    },
                    err => {
                        alert('Tidak dapat mendeteksi lokasi GPS secara otomatis. Silakan izinkan akses lokasi browser.');
                    },
                    { enableHighAccuracy: true, timeout: 10000 }
                );
            } else {
                alert('Fitur Geolocation tidak didukung di perangkat ini.');
            }
        }

        // Signature Canvas
        document.addEventListener('DOMContentLoaded', () => {
            // Apply initial styles to selects
            document.querySelectorAll('.area-status-select').forEach(sel => styleSelect(sel));
            updateProgress();

            const canvas = document.getElementById('signatureCanvas');
            const ctx = canvas.getContext('2d');
            const inputSignature = document.getElementById('tanda_tangan');
            const btnClear = document.getElementById('btnClearSignature');
            let isDrawing = false;

            function resizeCanvas() {
                const ratio = Math.max(window.devicePixelRatio || 1, 1);
                canvas.width = canvas.offsetWidth * ratio;
                canvas.height = canvas.offsetHeight * ratio;
                ctx.scale(ratio, ratio);
                ctx.lineWidth = 2.2;
                ctx.lineCap = 'round';
                ctx.lineJoin = 'round';
                ctx.strokeStyle = '#0f172a';
            }
            window.addEventListener('resize', resizeCanvas);
            resizeCanvas();

            function getPos(e) {
                const rect = canvas.getBoundingClientRect();
                const clientX = (e.touches && e.touches.length > 0) ? e.touches[0].clientX : e.clientX;
                const clientY = (e.touches && e.touches.length > 0) ? e.touches[0].clientY : e.clientY;
                return { x: clientX - rect.left, y: clientY - rect.top };
            }

            function start(e) {
                isDrawing = true;
                const pos = getPos(e);
                ctx.beginPath();
                ctx.moveTo(pos.x, pos.y);
                if (e.type.startsWith('touch')) e.preventDefault();
            }

            function draw(e) {
                if (!isDrawing) return;
                const pos = getPos(e);
                ctx.lineTo(pos.x, pos.y);
                ctx.stroke();
                if (e.type.startsWith('touch')) e.preventDefault();
            }

            function stop() {
                if (isDrawing) {
                    isDrawing = false;
                    inputSignature.value = canvas.toDataURL('image/png');
                }
            }

            canvas.addEventListener('mousedown', start);
            canvas.addEventListener('mousemove', draw);
            canvas.addEventListener('mouseup', stop);
            canvas.addEventListener('mouseleave', stop);

            canvas.addEventListener('touchstart', start, { passive: false });
            canvas.addEventListener('touchmove', draw, { passive: false });
            canvas.addEventListener('touchend', stop);

            btnClear.addEventListener('click', () => {
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                inputSignature.value = '';
            });

            // Form Submit Check
            document.getElementById('formSanitasiKapal').addEventListener('submit', (e) => {
                const selects = document.querySelectorAll('.area-status-select');
                for (const sel of selects) {
                    if (!sel.value) {
                        e.preventDefault();
                        alert('Mohon lengkapi seluruh Status Pemeriksaan pada 32 Area Fasilitas terlebih dahulu.');
                        sel.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        sel.focus();
                        return false;
                    }
                }

                if (!inputSignature.value) {
                    e.preventDefault();
                    alert('Mohon bubuhkan Tanda Tangan Petugas Pemeriksa pada kotak tanda tangan digital.');
                    canvas.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    return false;
                }
            });
        });
    </script>
</body>
</html>
