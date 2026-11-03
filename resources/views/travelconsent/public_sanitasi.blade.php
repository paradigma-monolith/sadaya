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

        /* Geotagging & Photo Documentation Styles (Matches Aircraft Inspection Form) */
        .geotag-section {
            background: #ffffff;
            border: 1.5px solid #cbd5e1;
            border-radius: 18px;
            padding: 24px;
            margin-top: 15px;
            margin-bottom: 20px;
            box-shadow: 0 4px 16px -2px rgba(15, 23, 42, 0.05);
        }
        .geotag-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
            padding-bottom: 14px;
            border-bottom: 1.5px solid #f1f5f9;
            margin-bottom: 20px;
        }
        .geotag-title {
            font-size: 15px;
            font-weight: 800;
            color: #0f172a;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .geo-status-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 14px;
            border-radius: 9999px;
            font-size: 12px;
            font-weight: 700;
            background: #f1f5f9;
            color: #475569;
            border: 1px solid #e2e8f0;
            transition: all 0.3s;
        }
        .geo-status-badge.active {
            background: #ecfdf5;
            color: #047857;
            border-color: #86efac;
        }
        .geo-pulse {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background-color: #10b981;
            box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
            animation: pulse-green 1.8s infinite;
        }
        @keyframes pulse-green {
            0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
            70% { transform: scale(1); box-shadow: 0 0 0 8px rgba(16, 185, 129, 0); }
            100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
        }
        .geo-info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-bottom: 20px;
        }
        @media (max-width: 768px) {
            .geo-info-grid {
                grid-template-columns: 1fr;
            }
        }
        @media (max-width: 640px) {
            .geotag-section {
                padding: 14px 10px;
                border-radius: 14px;
            }
            .upload-buttons-bar {
                flex-direction: column !important;
                gap: 8px !important;
            }
            .btn-camera-action, .btn-gallery-action {
                width: 100% !important;
                justify-content: center !important;
            }
            .geo-coords-display {
                font-size: 11px;
                padding: 6px 8px;
            }
            .photo-gallery-grid {
                grid-template-columns: repeat(2, 1fr) !important;
                gap: 8px !important;
            }
            .signature-canvas {
                height: 130px !important;
            }
        }
        .geo-info-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 16px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .geo-coords-display {
            font-family: 'JetBrains Mono', monospace;
            font-size: 13px;
            font-weight: 700;
            color: #047857;
            background: #ecfdf5;
            border: 1px solid #a7f3d0;
            padding: 8px 12px;
            border-radius: 8px;
            margin: 8px 0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 8px;
        }
        .geo-map-container {
            height: 190px;
            border-radius: 12px;
            overflow: hidden;
            border: 1.5px solid #cbd5e1;
            position: relative;
            background: #e2e8f0;
        }
        .btn-camera-action {
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            color: #ffffff;
            border: none;
            padding: 11px 20px;
            border-radius: 12px;
            font-size: 13px;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(5, 150, 105, 0.35);
            transition: all 0.2s;
        }
        .btn-camera-action:hover {
            background: linear-gradient(135deg, #047857 0%, #065f46 100%);
            transform: translateY(-1px);
        }
        .btn-gallery-action {
            background: #ffffff;
            color: #1e293b;
            border: 1.5px solid #cbd5e1;
            padding: 11px 20px;
            border-radius: 12px;
            font-size: 13px;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            transition: all 0.2s;
        }
        .btn-gallery-action:hover {
            background: #f1f5f9;
            border-color: #94a3b8;
        }
        .btn-gps-refresh {
            background: #ecfdf5;
            color: #047857;
            border: 1.5px solid #a7f3d0;
            padding: 7px 14px;
            border-radius: 10px;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s;
        }
        .btn-gps-refresh:hover {
            background: #d1fae5;
        }
        .preset-badge {
            background: #ffffff;
            border: 1px solid #cbd5e1;
            color: #475569;
            padding: 4px 10px;
            border-radius: 8px;
            font-size: 11px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.15s;
        }
        .preset-badge:hover {
            background: #d1fae5;
            color: #047857;
            border-color: #6ee7b7;
        }
        .photo-dropzone {
            border: 2px dashed #94a3b8;
            border-radius: 16px;
            padding: 24px 16px;
            text-align: center;
            background: #f8fafc;
            transition: all 0.2s;
            cursor: pointer;
        }
        .photo-dropzone:hover, .photo-dropzone.dragover {
            border-color: #059669;
            background: #ecfdf5;
        }
        .photo-gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
            gap: 16px;
            margin-top: 20px;
        }
        .photo-card-item {
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 14px;
            overflow: hidden;
            box-shadow: 0 4px 10px rgba(0,0,0,0.04);
            position: relative;
            display: flex;
            flex-direction: column;
            transition: all 0.2s;
        }
        .photo-card-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 18px rgba(0,0,0,0.08);
        }
        .photo-preview-wrap {
            position: relative;
            width: 100%;
            height: 180px;
            background: #0f172a;
            overflow: hidden;
        }
        .photo-preview-wrap img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }
        .geotag-stamp-badge {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(to top, rgba(15, 23, 42, 0.96) 0%, rgba(15, 23, 42, 0.82) 85%, transparent 100%);
            padding: 8px 10px 5px 10px;
            color: #ffffff;
            font-family: 'JetBrains Mono', monospace;
            font-size: 9.5px;
            line-height: 1.35;
            pointer-events: none;
        }
        .photo-details-body {
            padding: 12px;
            display: flex;
            flex-direction: column;
            gap: 8px;
            flex-grow: 1;
            background: #ffffff;
        }
        .photo-category-input {
            width: 100%;
            font-size: 12px;
            padding: 6px 8px;
            border-radius: 8px;
            border: 1px solid #cbd5e1;
            background: #f8fafc;
            color: #0f172a;
            font-weight: 600;
        }
        .btn-delete-card {
            position: absolute;
            top: 8px;
            right: 8px;
            background: rgba(225, 29, 72, 0.9);
            color: #ffffff;
            border: none;
            width: 26px;
            height: 26px;
            border-radius: 50%;
            font-size: 13px;
            font-weight: bold;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 2px 6px rgba(0,0,0,0.3);
            transition: all 0.2s;
            z-index: 10;
        }
        .btn-delete-card:hover {
            background: #be123c;
            transform: scale(1.1);
        }

        /* Live Camera WebRTC Modal Styles */
        .camera-modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.88);
            backdrop-filter: blur(8px);
            z-index: 9999;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 16px;
        }
        .camera-modal-card {
            background: #0f172a;
            border: 1.5px solid #334155;
            border-radius: 20px;
            width: 100%;
            max-width: 620px;
            overflow: hidden;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            display: flex;
            flex-direction: column;
        }
        .camera-modal-header {
            padding: 14px 18px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            color: #ffffff;
            border-bottom: 1px solid #1e293b;
        }
        .camera-viewfinder-wrapper {
            position: relative;
            background: #000000;
            width: 100%;
            height: 380px;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .camera-viewfinder-wrapper video {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .camera-live-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(to top, rgba(0,0,0,0.85) 0%, rgba(0,0,0,0.4) 70%, transparent 100%);
            padding: 10px 14px 8px 14px;
            color: #ffffff;
            font-family: 'JetBrains Mono', monospace;
            font-size: 11px;
            line-height: 1.4;
            pointer-events: none;
        }
        .camera-controls-bar {
            padding: 16px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #0f172a;
            gap: 12px;
        }
        .btn-shutter {
            width: 66px;
            height: 66px;
            border-radius: 50%;
            background: #ffffff;
            border: 4.5px solid #059669;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 0 24px rgba(5, 150, 105, 0.65);
            transition: transform 0.15s, background 0.15s;
        }
        .btn-shutter:hover {
            transform: scale(1.08);
            background: #ecfdf5;
        }
        .btn-shutter:active {
            transform: scale(0.95);
        }
        .btn-cam-switch, .btn-cam-close {
            background: #1e293b;
            color: #ffffff;
            border: 1px solid #334155;
            padding: 10px 16px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s;
        }
        .btn-cam-switch:hover, .btn-cam-close:hover {
            background: #334155;
        }
    </style>
    <!-- Leaflet CSS for Map Preview -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.css" />
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
                    <a href="{{ route('travelconsent.home') }}" class="text-xs font-semibold text-slate-500 hover:text-slate-900 px-3 py-1 rounded-lg border border-slate-200 hover:bg-slate-50 transition-colors">
                        ← Portal Layanan
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

                    <!-- Pelabuhan / Wilayah Kerja Laut -->
                    <div>
                        <label for="pelabuhan" class="block font-semibold text-slate-700 mb-1.5 flex items-center justify-between">
                            <span>Nama Pelabuhan / Wilker <span class="text-rose-500">*</span></span>
                            <span class="text-[10px] text-emerald-600 bg-emerald-50 px-1.5 py-0.5 rounded border border-emerald-200">Wilker Laut & Induk</span>
                        </label>
                        <select id="pelabuhan" name="pelabuhan" required onchange="onSanitasiOfficeChange(this)" class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-slate-200 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100 font-semibold text-slate-800 transition-all outline-none">
                            <option value="">-- Pilih Pelabuhan / Wilker Laut --</option>
                            @if(isset($offices) && count($offices) > 0)
                                @foreach($offices as $office)
                                    @php
                                        $oNama = is_array($office) ? $office['nama'] : $office->nama;
                                        $oAlamat = is_array($office) ? $office['alamat'] : $office->alamat;
                                        $oLat = is_array($office) ? ($office['latitude'] ?? $office['lat'] ?? null) : ($office->latitude ?? null);
                                        $oLng = is_array($office) ? ($office['longitude'] ?? $office['lng'] ?? null) : ($office->longitude ?? null);
                                    @endphp
                                    <option value="{{ $oNama }}" 
                                        data-alamat="{{ $oAlamat }}" 
                                        data-lat="{{ $oLat }}" 
                                        data-lng="{{ $oLng }}" 
                                        {{ old('pelabuhan', 'Wilayah Kerja Patimban') == $oNama ? 'selected' : '' }}>
                                        {{ $oNama }}
                                    </option>
                                @endforeach
                            @else
                                @foreach($ports as $port)
                                    <option value="{{ $port }}" {{ old('pelabuhan') == $port ? 'selected' : '' }}>{{ $port }}</option>
                                @endforeach
                            @endif
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

                    <!-- Alamat Pemeriksa (Otomatis sesuai Wilker tanpa menampilkan kolom alamat di form) -->
                    <input type="hidden" id="alamat_pemeriksa" name="alamat_pemeriksa" value="{{ old('alamat_pemeriksa', 'Pelabuhan Patimban, Pusakanagara, Subang, Jawa Barat 41255') }}">
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

                <!-- Mobile Swipe Hint -->
                <p class="text-[11px] text-slate-500 md:hidden flex items-center justify-between bg-slate-50 border border-slate-200 px-3 py-1.5 rounded-lg">
                    <span>👉 Geser tabel ke samping untuk mengisi Bukti &amp; Tindakan</span>
                    <span class="font-bold text-emerald-700">32 Area</span>
                </p>

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
                                    <input type="hidden" name="areas[{{ $area['no'] }}][no]" value="{{ $area['no'] }}">
                                    <input type="hidden" name="areas[{{ $area['no'] }}][nama]" value="{{ $area['nama'] }}">
                                    <input type="hidden" name="areas[{{ $area['no'] }}][kategori]" value="{{ $area['kategori'] }}">
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
                    <!-- Pejabat Penerbit (otomatis / tersembunyi, penandatanganan didasarkan pada Petugas Pemeriksa BKK) -->
                    <input type="hidden" id="pejabat_penerbit" name="pejabat_penerbit" value="Balai Kekarantinaan Kesehatan Kelas I Bandung">

                    <!-- Keterangan -->
                    <div>
                        <label for="keterangan" class="block font-semibold text-slate-700 mb-1.5">
                            Keterangan &amp; Catatan Rekomendasi Sanitasi <span class="text-rose-500">*</span>
                        </label>
                        <textarea id="keterangan" name="keterangan" rows="3" required placeholder="Tuliskan catatan kondisi sanitasi kapal, temuan penting, atau instruksi pemeliharaan kesehatan kapal..." class="w-full px-3.5 py-2.5 rounded-xl bg-white border border-slate-200 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100 font-medium text-slate-800 transition-all outline-none">Kapal telah diperiksa sesuai pedoman IHR (2005) dan memenuhi standar kelaikan sanitasi kapal laut.</textarea>
                    </div>

                    <!-- Geotagging & Documentation Photo Upload Section -->
                    <div class="geotag-section mt-6" id="geotagSection">
                        <div class="geotag-header">
                            <div>
                                <div class="geotag-title">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14.5 4h-5L7 7H4a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-3l-2.5-3z"/><circle cx="12" cy="13" r="3"/></svg>
                                    <span>Dokumentasi Foto Lapangan &amp; Geotagging GPS</span>
                                </div>
                                <div style="font-size: 12px; color: #64748b; margin-top: 2px;">
                                    Bukti foto kelaikan sanitasi kapal (palka, dapur/galley, pentry, kamar mesin, air bersih). Koordinat GPS terekam otomatis.
                                </div>
                            </div>

                            <div class="geo-status-badge" id="geoStatusBadge">
                                <span class="geo-pulse" id="geoPulseDot"></span>
                                <span id="geoStatusText">Mendeteksi Lokasi GPS...</span>
                            </div>
                        </div>

                        <!-- Sensor GPS & Interactive Location Overview -->
                        <div class="geo-info-grid">
                            <!-- Box 1: Coordinates & Location Information -->
                            <div class="geo-info-box">
                                <div>
                                    <div style="display: flex; justify-content: space-between; align-items: center;">
                                        <span style="font-weight: 700; font-size: 12.5px; color: #1e293b;">Koordinat Geotagging (WGS84)</span>
                                        <button type="button" class="btn-gps-refresh" id="btnRefreshGps" title="Klik untuk refresh lokasi perangkat">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="6"/><circle cx="12" cy="12" r="2"/></svg>
                                            <span>Perbarui GPS</span>
                                        </button>
                                    </div>

                                    <div class="geo-coords-display" id="geoCoordsDisplay">
                                        <span>Lat: <strong id="lblLatitude">-6.719823</strong> &bull; Lng: <strong id="lblLongitude">108.572145</strong></span>
                                        <span style="font-size: 11px; color: #047857; background: #ecfdf5; padding: 2px 6px; border-radius: 4px;" id="lblAccuracy">Akurasi: &plusmn; 10m</span>
                                    </div>

                                    <div id="displayFullAddress" style="font-size: 11px; color: #475569; margin: 4px 0 8px 0; background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 6px; padding: 6px 10px; display: flex; align-items: flex-start; gap: 6px;">
                                        <span style="font-size: 13px; line-height: 1;">🏠</span>
                                        <span id="lblFullAddress" style="word-break: break-word; line-height: 1.3;">Memuat alamat dermaga pelabuhan...</span>
                                    </div>

                                    <div style="margin-top: 8px;">
                                        <label style="font-size: 11.5px; font-weight: 600; color: #475569; display: block; margin-bottom: 3px;">Keterangan / Lokasi Dermaga:</label>
                                        <input type="text" name="lokasi_geotag" id="input_lokasi_geotag" class="w-full text-xs rounded-lg border border-slate-300 p-2 focus:ring-2 focus:ring-emerald-500" placeholder="Contoh: Dermaga Muara Jati Pelabuhan Cirebon / Dermaga Patimban" value="{{ old('lokasi_geotag', 'Dermaga Muara Jati / Pelabuhan Cirebon, Kec. Lemahwungkuk, Kota Cirebon, Jawa Barat 45111') }}">
                                    </div>
                                </div>

                                <div style="margin-top: 10px; font-size: 11px; color: #64748b; display: flex; align-items: center; gap: 4px;">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                                    <span>Setiap foto yang diunggah akan otomatis tersemat stempel geotagging koordinat ini.</span>
                                </div>
                            </div>

                            <!-- Box 2: Interactive Location Mini-Map -->
                            <div class="geo-map-container" id="geoMiniMap">
                                <!-- Leaflet map container -->
                            </div>
                        </div>

                        <!-- Hidden Inputs for Form Submission -->
                        <input type="hidden" name="latitude" id="input_latitude" value="{{ old('latitude', '-6.719823') }}">
                        <input type="hidden" name="longitude" id="input_longitude" value="{{ old('longitude', '108.572145') }}">
                        
                        <!-- Real Multiple File Input (controlled via DataTransfer) -->
                        <input type="file" name="foto_dokumentasi[]" id="realPhotoInput" multiple accept="image/*" style="display: none;">

                        <!-- Hidden dedicated trigger inputs -->
                        <input type="file" id="cameraInput" accept="image/*" capture="environment" style="display: none;">
                        <input type="file" id="galleryInput" accept="image/*" multiple style="display: none;">

                        <!-- Upload Action Toolbar & Dropzone -->
                        <div class="upload-buttons-bar">
                            <button type="button" class="btn-camera-action" id="btnTriggerCamera">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>
                                <span>Ambil Foto via Kamera (Live)</span>
                            </button>

                            <button type="button" class="btn-gallery-action" id="btnTriggerGallery">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                                <span>Pilih Berkas Foto (Galeri)</span>
                            </button>
                        </div>

                        <!-- Drag & Drop Zone -->
                        <div class="photo-dropzone" id="photoDropzone">
                            <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="margin: 0 auto 8px auto; display: block;"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                            <div style="font-weight: 700; color: #1e293b; font-size: 13px;">Seret &amp; letakkan foto dokumentasi sanitasi kapal di sini</div>
                            <div style="font-size: 11.5px; color: #64748b; margin-top: 4px;">Mendukung format JPG, PNG, WEBP (Maksimal 12MB per berkas foto).</div>
                        </div>

                        <!-- Uploaded Photos Gallery Preview -->
                        <div class="photo-gallery-grid" id="photoGalleryGrid">
                            <!-- Populated dynamically via JavaScript -->
                        </div>

                        <div id="emptyPhotoNotice" style="text-align: center; padding: 25px 15px; color: #94a3b8; font-size: 12.5px; background: #fafafa; border: 1px dashed #e2e8f0; border-radius: 12px; margin-top: 15px;">
                            Belum ada foto dokumentasi yang dilampirkan. Klik tombol <strong>Ambil Foto Kamera</strong> atau <strong>Pilih Berkas Foto</strong> di atas untuk menambahkan bukti dokumentasi inspeksi kelaikan kapal.
                        </div>
                    </div>

                    <!-- Tanda Tangan Digital Petugas Pemeriksa -->
                    <div class="pt-4">
                        <div class="p-4 rounded-xl border border-slate-200 bg-slate-50/50 space-y-2.5">
                            <div class="flex items-center justify-between">
                                <label class="font-bold text-slate-900 flex items-center gap-1.5">
                                    <span>✍️ Tanda Tangan Digital Petugas Pemeriksa</span>
                                    <span class="text-rose-500">*</span>
                                </label>
                                <button type="button" id="btnClearSignature" class="px-2.5 py-1 rounded-md text-[11px] font-semibold text-rose-600 hover:bg-rose-50 border border-rose-200 transition-colors">
                                    Hapus TTD
                                </button>
                            </div>

                            <div class="relative bg-white rounded-xl border border-slate-300 overflow-hidden shadow-inner max-w-xl mx-auto">
                                <canvas id="signatureCanvas" class="signature-canvas w-full h-44"></canvas>
                                <span class="absolute bottom-2 right-2 text-[10px] text-slate-400 font-mono pointer-events-none">Goreskan TTD disini</span>
                            </div>
                            <input type="hidden" name="tanda_tangan" id="tanda_tangan" required>
                            <p class="text-[11px] text-slate-500 text-center leading-tight">Gunakan sentuhan jari (smartphone/tablet) atau mouse untuk menandatangani dokumen.</p>
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

    <!-- Live Camera WebRTC Viewfinder Modal -->
    <div class="camera-modal-overlay" id="liveCameraModal">
        <div class="camera-modal-card">
            <div class="camera-modal-header">
                <div style="font-weight: 700; font-size: 14px; display: flex; align-items: center; gap: 8px;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#34d399" stroke-width="2.5"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>
                    <span>Kamera Live Inspeksi Kapal &bull; Geotagging GPS</span>
                </div>
                <button type="button" onclick="closeLiveCamera()" style="background: #334155; color: white; border: none; padding: 5px 12px; border-radius: 8px; cursor: pointer;">&times; Tutup</button>
            </div>

            <div class="camera-viewfinder-wrapper">
                <video id="cameraVideo" autoplay playsinline muted></video>
                <div class="camera-live-overlay">
                    <div>🚢 BKK BANDUNG &bull; SANITASI KAPAL (SSCC / SSCEC)</div>
                    <div id="camLiveCoords">📍 Koordinat: -6.719823, 108.572145</div>
                    <div id="camLiveTime">🕒 Memuat waktu...</div>
                    <div id="camLiveAddress" style="font-size: 9.5px; color: #e2e8f0; margin-top: 2px; line-height: 1.25; opacity: 0.95; word-break: break-word;">
                        🏠 <span id="camLiveAddressText">Memuat alamat dermaga pelabuhan...</span>
                    </div>
                </div>
            </div>

            <div class="camera-controls-bar">
                <button type="button" class="btn-cam-switch" id="btnSwitchCamera" title="Ganti Kamera Depan / Belakang">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21.5 2v6h-6M21.34 15.57a10 10 0 1 1-.57-8.38l5.67-5.67"/></svg>
                    <span>Putar Kamera</span>
                </button>

                <button type="button" class="btn-shutter" id="btnShutter" title="Jepret Foto Sekarang">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="3" fill="#059669"/></svg>
                </button>

                <button type="button" class="btn-cam-close" onclick="closeLiveCamera()">
                    <span>Selesai</span>
                </button>
            </div>
        </div>
    </div>
    <canvas id="cameraCanvas" style="display:none;"></canvas>

    <!-- Leaflet Map Library -->
    <script src="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.js"></script>

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

        /* =========================================================
         * 1. GPS Geolocation & Interactive Map
         * ========================================================= */
        var currentLatitude = parseFloat(document.getElementById('input_latitude').value) || -6.719823;
        var currentLongitude = parseFloat(document.getElementById('input_longitude').value) || 108.572145;
        var currentAccuracy = 10;
        var currentFullAddress = document.getElementById('input_lokasi_geotag')?.value || 'Dermaga Muara Jati / Pelabuhan Cirebon, Kec. Lemahwungkuk, Kota Cirebon, Jawa Barat 45111';
        var mapInstance = null;
        var markerInstance = null;
        var reverseGeocodeCache = {};
        var reverseGeocodeTimeout = null;

        function escapeHtml(str) {
            if (!str) return '';
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function applyFullAddress(addr) {
            if (!addr) return;
            currentFullAddress = addr;

            var camLiveAddrText = document.getElementById('camLiveAddressText');
            if (camLiveAddrText) camLiveAddrText.textContent = addr;

            var lblFullAddr = document.getElementById('lblFullAddress');
            if (lblFullAddr) lblFullAddr.textContent = addr;

            var inputLokasi = document.getElementById('input_lokasi_geotag');
            if (inputLokasi && (!inputLokasi.dataset.userEdited || inputLokasi.value.trim() === '')) {
                inputLokasi.value = addr.length > 250 ? addr.substring(0, 247) + '...' : addr;
            }

            if (markerInstance) {
                markerInstance.bindPopup('<b>Lokasi Inspeksi Sanitasi Kapal</b><br><span style="font-size: 11px; color: #475569;">' + escapeHtml(addr) + '</span>');
            }
        }

        function fetchReverseGeocode(lat, lng) {
            var key = lat.toFixed(4) + ',' + lng.toFixed(4);
            if (reverseGeocodeCache[key]) {
                applyFullAddress(reverseGeocodeCache[key]);
                return;
            }

            if (reverseGeocodeTimeout) clearTimeout(reverseGeocodeTimeout);
            reverseGeocodeTimeout = setTimeout(function() {
                var url = 'https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=' + lat + '&lon=' + lng + '&zoom=18&addressdetails=1';
                fetch(url, { headers: { 'Accept-Language': 'id,en;q=0.8' } })
                    .then(function(res) { return res.json(); })
                    .then(function(data) {
                        if (data && data.display_name) {
                            reverseGeocodeCache[key] = data.display_name;
                            applyFullAddress(data.display_name);
                        }
                    })
                    .catch(function(err) {
                        console.debug('Reverse geocoding not reachable:', err);
                    });
            }, 500);
        }

        function initMiniMap(lat, lng) {
            var mapContainer = document.getElementById('geoMiniMap');
            if (!mapContainer || typeof L === 'undefined') return;

            try {
                if (!mapInstance) {
                    mapInstance = L.map('geoMiniMap', { zoomControl: false, attributionControl: false }).setView([lat, lng], 15);
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 19
                    }).addTo(mapInstance);

                    markerInstance = L.marker([lat, lng]).addTo(mapInstance);
                    markerInstance.bindPopup('<b>Lokasi Inspeksi Sanitasi Kapal</b><br><span style="font-size: 11px; color: #475569;">' + escapeHtml(currentFullAddress) + '</span>').openPopup();
                    L.control.zoom({ position: 'bottomright' }).addTo(mapInstance);
                } else {
                    mapInstance.setView([lat, lng], 15);
                    if (markerInstance) {
                        markerInstance.setLatLng([lat, lng]);
                        markerInstance.bindPopup('<b>Lokasi Inspeksi Sanitasi Kapal</b><br><span style="font-size: 11px; color: #475569;">' + escapeHtml(currentFullAddress) + '</span>');
                    }
                }
            } catch (err) {
                console.warn('Map initialization failed:', err);
            }
        }

        function updateGpsUI(lat, lng, acc, skipReverseGeocode) {
            currentLatitude = parseFloat(lat);
            currentLongitude = parseFloat(lng);
            if (acc) currentAccuracy = Math.round(acc);

            document.getElementById('input_latitude').value = currentLatitude.toFixed(7);
            document.getElementById('input_longitude').value = currentLongitude.toFixed(7);
            document.getElementById('lblLatitude').textContent = currentLatitude.toFixed(6);
            document.getElementById('lblLongitude').textContent = currentLongitude.toFixed(6);
            document.getElementById('lblAccuracy').textContent = 'Akurasi: ± ' + currentAccuracy + 'm';

            var camCoords = document.getElementById('camLiveCoords');
            if (camCoords) {
                camCoords.textContent = '📍 Lat: ' + currentLatitude.toFixed(6) + ', Lng: ' + currentLongitude.toFixed(6) + ' (±' + currentAccuracy + 'm)';
            }

            var badge = document.getElementById('geoStatusBadge');
            var badgeText = document.getElementById('geoStatusText');
            var dot = document.getElementById('geoPulseDot');

            if (badge && badgeText) {
                badge.classList.add('active');
                badgeText.textContent = 'GPS Aktif • Terkunci (±' + currentAccuracy + 'm)';
                if (dot) dot.style.display = 'inline-block';
            }

            initMiniMap(currentLatitude, currentLongitude);

            if (!skipReverseGeocode) {
                fetchReverseGeocode(currentLatitude, currentLongitude);
            }
        }

        function detectGeolocation(showNotice) {
            var badgeText = document.getElementById('geoStatusText');
            if (badgeText) badgeText.textContent = 'Mencari sinyal GPS satelit...';

            if ("geolocation" in navigator) {
                navigator.geolocation.getCurrentPosition(
                    function(position) {
                        updateGpsUI(
                            position.coords.latitude,
                            position.coords.longitude,
                            position.coords.accuracy
                        );
                    },
                    function(error) {
                        console.warn('Geolocation warning:', error.message);
                        var badge = document.getElementById('geoStatusBadge');
                        var badgeText = document.getElementById('geoStatusText');
                        if (badge && badgeText) {
                            badge.classList.remove('active');
                            badgeText.textContent = 'GPS Manual / Preset Dermaga';
                        }
                        updateGpsUI(currentLatitude, currentLongitude, 25);

                        if (showNotice) {
                            alert('Izin akses lokasi perangkat belum aktif atau sinyal GPS lemah. Koordinat default dermaga digunakan.');
                        }
                    },
                    { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
                );
            } else {
                updateGpsUI(currentLatitude, currentLongitude, 50);
            }
        }

        function onSanitasiOfficeChange(selectEl) {
            if (!selectEl) return;
            var opt = selectEl.options[selectEl.selectedIndex];
            if (!opt || !opt.value) return;

            var alamat = opt.getAttribute('data-alamat');
            var lat = parseFloat(opt.getAttribute('data-lat'));
            var lng = parseFloat(opt.getAttribute('data-lng'));

            if (alamat) {
                var addrField = document.getElementById('alamat_pemeriksa');
                if (addrField) {
                    addrField.value = alamat;
                    addrField.classList.add('bg-emerald-50', 'border-emerald-500');
                    setTimeout(function() {
                        addrField.classList.remove('bg-emerald-50', 'border-emerald-500');
                    }, 1200);
                }
            }

            if (!isNaN(lat) && !isNaN(lng)) {
                setPresetLocation(lat, lng, opt.value, opt.value + ' - ' + (alamat || ''));
            }
        }

        function selectOfficeOption(nama, alamat, lat, lng) {
            var sel = document.getElementById('pelabuhan');
            if (sel) {
                sel.value = nama;
            }
            var addrField = document.getElementById('alamat_pemeriksa');
            if (addrField && alamat) {
                addrField.value = alamat;
                addrField.classList.add('bg-emerald-50', 'border-emerald-500');
                setTimeout(function() {
                    addrField.classList.remove('bg-emerald-50', 'border-emerald-500');
                }, 1200);
            }
            if (!isNaN(lat) && !isNaN(lng)) {
                setPresetLocation(lat, lng, nama, nama + ' - ' + (alamat || ''));
            }
        }

        function setPresetLocation(lat, lng, label, fullAddress) {
            var full = fullAddress || label;
            var inputLokasi = document.getElementById('input_lokasi_geotag');
            if (inputLokasi) {
                inputLokasi.value = full.length > 250 ? full.substring(0, 247) + '...' : full;
                delete inputLokasi.dataset.userEdited;
            }
            applyFullAddress(full);
            updateGpsUI(lat, lng, 5, true);
        }

        var inputLokasiEl = document.getElementById('input_lokasi_geotag');
        if (inputLokasiEl) {
            inputLokasiEl.addEventListener('input', function() {
                this.dataset.userEdited = 'true';
                currentFullAddress = this.value;
                var camLiveAddrText = document.getElementById('camLiveAddressText');
                if (camLiveAddrText) camLiveAddrText.textContent = this.value || 'Dermaga Pelabuhan';
                var lblFullAddr = document.getElementById('lblFullAddress');
                if (lblFullAddr) lblFullAddr.textContent = this.value || '-';
            });
        }

        document.getElementById('btnRefreshGps').addEventListener('click', function() {
            detectGeolocation(true);
        });

        /* =========================================================
         * 2. Pure JS EXIF GPS Parser
         * ========================================================= */
        function extractExifGps(buffer) {
            try {
                var dv = new DataView(buffer);
                if (dv.getUint16(0, false) !== 0xFFD8) return null;

                var length = dv.byteLength;
                var offset = 2;

                while (offset < length) {
                    var marker = dv.getUint16(offset, false);
                    offset += 2;

                    if (marker === 0xFFE1) {
                        var app1Length = dv.getUint16(offset, false);
                        offset += 2;
                        var exifHeader = dv.getUint32(offset, false);
                        if (exifHeader !== 0x45786966) return null;

                        var tiffStart = offset + 6;
                        var littleEndian = dv.getUint16(tiffStart, false) === 0x4949;
                        var firstIfdOffset = dv.getUint32(tiffStart + 4, littleEndian);

                        var ifdStart = tiffStart + firstIfdOffset;
                        var numEntries = dv.getUint16(ifdStart, littleEndian);
                        var gpsOffset = null;

                        for (var i = 0; i < numEntries; i++) {
                            var entryOffset = ifdStart + 2 + (i * 12);
                            var tag = dv.getUint16(entryOffset, littleEndian);
                            if (tag === 0x8825) {
                                gpsOffset = dv.getUint32(entryOffset + 8, littleEndian);
                                break;
                            }
                        }

                        if (!gpsOffset) return null;

                        var gpsIfdStart = tiffStart + gpsOffset;
                        var numGpsEntries = dv.getUint16(gpsIfdStart, littleEndian);
                        var latRef = null, lngRef = null, latVal = null, lngVal = null;

                        var readRational = function(valOffset) {
                            var num = dv.getUint32(tiffStart + valOffset, littleEndian);
                            var den = dv.getUint32(tiffStart + valOffset + 4, littleEndian);
                            return den !== 0 ? num / den : 0;
                        };

                        for (var j = 0; j < numGpsEntries; j++) {
                            var gEntry = gpsIfdStart + 2 + (j * 12);
                            var gTag = dv.getUint16(gEntry, littleEndian);
                            var gOffset = dv.getUint32(gEntry + 8, littleEndian);

                            if (gTag === 1) latRef = String.fromCharCode(dv.getUint8(gEntry + 8));
                            if (gTag === 2) {
                                var d = readRational(gOffset);
                                var m = readRational(gOffset + 8);
                                var s = readRational(gOffset + 16);
                                latVal = d + (m / 60) + (s / 3600);
                            }
                            if (gTag === 3) lngRef = String.fromCharCode(dv.getUint8(gEntry + 8));
                            if (gTag === 4) {
                                var d2 = readRational(gOffset);
                                var m2 = readRational(gOffset + 8);
                                var s2 = readRational(gOffset + 16);
                                lngVal = d2 + (m2 / 60) + (s2 / 3600);
                            }
                        }

                        if (latVal !== null && lngVal !== null && latRef && lngRef) {
                            if (latRef.toUpperCase() === 'S') latVal = -latVal;
                            if (lngRef.toUpperCase() === 'W') lngVal = -lngVal;
                            return { latitude: latVal, longitude: lngVal };
                        }
                        return null;
                    } else if ((marker & 0xFF00) === 0xFF00) {
                        offset += dv.getUint16(offset, false);
                    } else {
                        break;
                    }
                }
            } catch (ex) {
                console.debug('EXIF read ignored:', ex);
            }
            return null;
        }

        /* =========================================================
         * 3. WebRTC Live Camera Controller
         * ========================================================= */
        var cameraStream = null;
        var currentFacingMode = 'environment';
        var liveClockInterval = null;

        function openLiveCamera() {
            var modal = document.getElementById('liveCameraModal');
            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                if (location.protocol !== 'https:' && location.hostname !== 'localhost' && location.hostname !== '127.0.0.1') {
                    if (confirm('Akses live kamera WebRTC mewajibkan koneksi aman (HTTPS).\n\nBeralih ke koneksi aman (HTTPS) sekarang agar live kamera dapat aktif?')) {
                        location.href = 'https://' + location.host + location.pathname + location.search;
                        return;
                    }
                }
                alert('Browser Anda tidak mendukung akses live kamera WebRTC atau izin kamera belum aktif. Membuka pemilih berkas...');
                document.getElementById('cameraInput').click();
                return;
            }

            modal.style.display = 'flex';
            updateLiveCameraOverlayClock();
            var camLiveAddrText = document.getElementById('camLiveAddressText');
            if (camLiveAddrText) {
                camLiveAddrText.textContent = currentFullAddress || document.getElementById('input_lokasi_geotag')?.value || 'Dermaga Pelabuhan';
            }
            if (liveClockInterval) clearInterval(liveClockInterval);
            liveClockInterval = setInterval(updateLiveCameraOverlayClock, 1000);

            startCameraStream();
        }

        function updateLiveCameraOverlayClock() {
            var el = document.getElementById('camLiveTime');
            if (el) {
                var now = new Date();
                el.textContent = '🕒 ' + now.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }) + ' ' +
                                 now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
            }
        }

        function startCameraStream() {
            var video = document.getElementById('cameraVideo');
            if (cameraStream) {
                cameraStream.getTracks().forEach(function(track) { track.stop(); });
                cameraStream = null;
            }

            var constraints = {
                video: {
                    facingMode: { ideal: currentFacingMode },
                    width: { ideal: 1920 },
                    height: { ideal: 1080 }
                },
                audio: false
            };

            navigator.mediaDevices.getUserMedia(constraints)
                .then(function(stream) {
                    cameraStream = stream;
                    video.srcObject = stream;
                    video.play();
                })
                .catch(function(err) {
                    console.warn('getUserMedia ideal constraint failed, retrying generic video...', err);
                    navigator.mediaDevices.getUserMedia({ video: true, audio: false })
                        .then(function(stream) {
                            cameraStream = stream;
                            video.srcObject = stream;
                            video.play();
                        })
                        .catch(function(err2) {
                            alert('Tidak dapat mengaktifkan kamera: ' + (err2.name || err2.message) + '.\nPastikan izin webcam/kamera diaktifkan atau pilih file foto dari perangkat.');
                            closeLiveCamera();
                            document.getElementById('cameraInput').click();
                        });
                });
        }

        function closeLiveCamera() {
            var modal = document.getElementById('liveCameraModal');
            if (modal) modal.style.display = 'none';
            if (cameraStream) {
                cameraStream.getTracks().forEach(function(track) { track.stop(); });
                cameraStream = null;
            }
            if (liveClockInterval) {
                clearInterval(liveClockInterval);
                liveClockInterval = null;
            }
        }

        function switchCamera() {
            currentFacingMode = (currentFacingMode === 'environment') ? 'user' : 'environment';
            startCameraStream();
        }

        function snapPhotoFromLiveCamera() {
            var video = document.getElementById('cameraVideo');
            var canvas = document.getElementById('cameraCanvas');
            if (!video || !video.videoWidth) {
                alert('Kamera belum siap, mohon tunggu beberapa detik...');
                return;
            }

            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            var ctx = canvas.getContext('2d');

            ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

            var fontSize = Math.max(14, Math.round(canvas.height * 0.024));
            var addressFontSize = Math.max(10, Math.round(fontSize * 0.65));
            var padding = Math.max(16, Math.round(canvas.width * 0.02));
            var bannerHeight = Math.max(92, Math.round(canvas.height * 0.165));

            ctx.fillStyle = 'rgba(15, 23, 42, 0.88)';
            ctx.fillRect(0, canvas.height - bannerHeight, canvas.width, bannerHeight);

            // 1. Header Line
            ctx.font = 'bold ' + fontSize + 'px sans-serif';
            ctx.fillStyle = '#ffffff';
            var line1 = '🚢 BKK BANDUNG • SHIP SANITATION INSPECTION (SSCC)';
            ctx.fillText(line1, padding, canvas.height - bannerHeight + (fontSize * 1.25));

            // 2. Coordinates Line
            ctx.font = 'bold ' + (fontSize * 0.82) + 'px monospace';
            ctx.fillStyle = '#34d399';
            var line2 = '📍 Lat: ' + currentLatitude.toFixed(6) + ', Lng: ' + currentLongitude.toFixed(6) + ' (±' + currentAccuracy + 'm)';
            ctx.fillText(line2, padding, canvas.height - bannerHeight + (fontSize * 2.35));

            // 3. Time & Ship Name
            ctx.font = (fontSize * 0.78) + 'px monospace';
            ctx.fillStyle = '#cbd5e1';
            var shipName = document.querySelector('input[name="nama_kapal"]')?.value || 'KAPAL LAUT';
            var line3 = '🕒 ' + new Date().toLocaleString('id-ID') + ' • Kapal: ' + shipName;
            ctx.fillText(line3, padding, canvas.height - bannerHeight + (fontSize * 3.4));

            // 4. Address Line
            ctx.font = addressFontSize + 'px sans-serif';
            ctx.fillStyle = '#f1f5f9';
            var addrRaw = currentFullAddress || document.getElementById('input_lokasi_geotag')?.value || 'Dermaga Pelabuhan';
            var addressText = '🏠 ' + addrRaw;

            var maxAddrWidth = canvas.width - (padding * 2);
            var words = addressText.split(' ');
            var currentLine = '';
            var startY = canvas.height - bannerHeight + (fontSize * 4.35);
            var lineHeight = addressFontSize * 1.35;
            var maxLines = 2;
            var renderedLines = 0;

            for (var n = 0; n < words.length; n++) {
                var testLine = currentLine + words[n] + ' ';
                var metrics = ctx.measureText(testLine);
                if (metrics.width > maxAddrWidth && n > 0) {
                    ctx.fillText(currentLine.trim(), padding, startY);
                    currentLine = words[n] + ' ';
                    startY += lineHeight;
                    renderedLines++;
                    if (renderedLines >= maxLines - 1) {
                        var remaining = words.slice(n).join(' ');
                        while (ctx.measureText(remaining + '...').width > maxAddrWidth && remaining.length > 0) {
                            remaining = remaining.substring(0, remaining.length - 1);
                        }
                        currentLine = remaining + '... ';
                        break;
                    }
                } else {
                    currentLine = testLine;
                }
            }
            if (currentLine.trim().length > 0) {
                ctx.fillText(currentLine.trim(), padding, startY);
            }

            canvas.toBlob(function(blob) {
                if (!blob) return;
                var fileName = 'ship_snap_' + Date.now() + '.jpg';
                var file = new File([blob], fileName, { type: 'image/jpeg' });

                handleSelectedFiles([file]);

                var shutter = document.getElementById('btnShutter');
                if (shutter) {
                    shutter.style.transform = 'scale(1.25)';
                    setTimeout(function() { shutter.style.transform = 'scale(1)'; }, 180);
                }

                closeLiveCamera();
            }, 'image/jpeg', 0.92);
        }

        /* =========================================================
         * 4. Multi-Photo Geotagging & Preview Management
         * ========================================================= */
        var photoTransfer = new DataTransfer();
        var photoGalleryGrid = document.getElementById('photoGalleryGrid');
        var emptyNotice = document.getElementById('emptyPhotoNotice');
        var realPhotoInput = document.getElementById('realPhotoInput');

        var cameraInput = document.getElementById('cameraInput');
        var galleryInput = document.getElementById('galleryInput');
        var photoDropzone = document.getElementById('photoDropzone');

        document.getElementById('btnTriggerCamera').addEventListener('click', function() {
            openLiveCamera();
        });

        document.getElementById('btnSwitchCamera').addEventListener('click', switchCamera);
        document.getElementById('btnShutter').addEventListener('click', snapPhotoFromLiveCamera);

        document.getElementById('btnTriggerGallery').addEventListener('click', function() {
            galleryInput.click();
        });

        photoDropzone.addEventListener('click', function() {
            galleryInput.click();
        });

        photoDropzone.addEventListener('dragover', function(e) {
            e.preventDefault();
            photoDropzone.classList.add('dragover');
        });

        photoDropzone.addEventListener('dragleave', function() {
            photoDropzone.classList.remove('dragover');
        });

        photoDropzone.addEventListener('drop', function(e) {
            e.preventDefault();
            photoDropzone.classList.remove('dragover');
            if (e.dataTransfer.files && e.dataTransfer.files.length > 0) {
                handleSelectedFiles(e.dataTransfer.files);
            }
        });

        cameraInput.addEventListener('change', function() {
            if (this.files && this.files.length > 0) {
                handleSelectedFiles(this.files);
                this.value = '';
            }
        });

        galleryInput.addEventListener('change', function() {
            if (this.files && this.files.length > 0) {
                handleSelectedFiles(this.files);
                this.value = '';
            }
        });

        function updateEmptyNotice() {
            if (photoTransfer.items.length === 0) {
                emptyNotice.style.display = 'block';
            } else {
                emptyNotice.style.display = 'none';
            }
            realPhotoInput.files = photoTransfer.files;
        }

        function handleSelectedFiles(files) {
            var defaultCategories = [
                'Area Palka / Ruang Kargo',
                'Dapur / Galley & Ruang Makan',
                'Penyimpanan Pangan & Perbekalan',
                'Air Minum & Pasokan Bersih',
                'Kamar Mesin & Limbah Bilga',
                'Kamar Tidur Awak & Fasilitas Sanitasi',
                'Dokumen Buku Kesehatan Kapal (MDH)',
                'Dokumentasi Lapangan Umum'
            ];

            Array.from(files).forEach(function(file) {
                if (!file.type.match(/^image\//)) {
                    alert('File ' + file.name + ' bukan gambar yang didukung.');
                    return;
                }

                var currentIndex = photoTransfer.items.length;
                photoTransfer.items.add(file);

                var readerBuf = new FileReader();
                readerBuf.onload = function(eBuf) {
                    var exifGps = extractExifGps(eBuf.target.result);
                    var photoLat = exifGps ? exifGps.latitude : currentLatitude;
                    var photoLng = exifGps ? exifGps.longitude : currentLongitude;
                    var photoAcc = exifGps ? 5 : currentAccuracy;

                    var readerData = new FileReader();
                    readerData.onload = function(eData) {
                        renderPhotoCard(file, eData.target.result, currentIndex, photoLat, photoLng, photoAcc, defaultCategories);
                        updateEmptyNotice();
                    };
                    readerData.readAsDataURL(file);
                };
                readerBuf.readAsArrayBuffer(file);
            });
        }

        function renderPhotoCard(file, dataUrl, index, lat, lng, acc, categories) {
            var now = new Date();
            var timeStr = now.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }) + ' ' +
                          now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });

            var defaultCat = categories[index % categories.length];

            var card = document.createElement('div');
            card.className = 'photo-card-item';
            card.dataset.index = index;

            var displayAddress = currentFullAddress || document.getElementById('input_lokasi_geotag')?.value || 'Dermaga Pelabuhan';

            card.innerHTML = `
                <button type="button" class="btn-delete-card" title="Hapus foto ini">&times;</button>
                <div class="photo-preview-wrap">
                    <img src="${dataUrl}" alt="${file.name}">
                    <div class="geotag-stamp-badge">
                        <div style="font-weight: 700;">🚢 BKK BANDUNG &bull; SANITASI KAPAL</div>
                        <div>📍 ${lat.toFixed(6)}, ${lng.toFixed(6)} (&plusmn;${acc}m)</div>
                        <div>🕒 ${timeStr}</div>
                        <div style="font-size: 8.5px; opacity: 0.92; color: #f1f5f9; margin-top: 2px; line-height: 1.25; word-break: break-word; max-height: 24px; overflow: hidden;">
                            🏠 ${escapeHtml(displayAddress)}
                        </div>
                    </div>
                </div>
                <div class="photo-details-body">
                    <div style="font-weight: 700; font-size: 11.5px; color: #1e293b; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                        ${file.name}
                    </div>
                    <div>
                        <label style="font-size: 10.5px; color: #64748b; font-weight: 600; display: block; margin-bottom: 2px;">Kategori Area:</label>
                        <select name="foto_kategori[]" class="photo-category-input">
                            <option value="Area Palka / Ruang Kargo" ${defaultCat === 'Area Palka / Ruang Kargo' ? 'selected' : ''}>Area Palka / Ruang Kargo</option>
                            <option value="Dapur / Galley & Ruang Makan" ${defaultCat === 'Dapur / Galley & Ruang Makan' ? 'selected' : ''}>Dapur / Galley & Ruang Makan</option>
                            <option value="Penyimpanan Pangan & Perbekalan" ${defaultCat === 'Penyimpanan Pangan & Perbekalan' ? 'selected' : ''}>Penyimpanan Pangan & Perbekalan</option>
                            <option value="Air Minum & Pasokan Bersih" ${defaultCat === 'Air Minum & Pasokan Bersih' ? 'selected' : ''}>Air Minum & Pasokan Bersih</option>
                            <option value="Kamar Mesin & Limbah Bilga" ${defaultCat === 'Kamar Mesin & Limbah Bilga' ? 'selected' : ''}>Kamar Mesin & Limbah Bilga</option>
                            <option value="Kamar Tidur Awak & Fasilitas Sanitasi" ${defaultCat === 'Kamar Tidur Awak & Fasilitas Sanitasi' ? 'selected' : ''}>Kamar Tidur Awak & Fasilitas Sanitasi</option>
                            <option value="Dokumen Buku Kesehatan Kapal (MDH)" ${defaultCat === 'Dokumen Buku Kesehatan Kapal (MDH)' ? 'selected' : ''}>Dokumen Buku Kesehatan Kapal (MDH)</option>
                            <option value="Dokumentasi Lapangan Umum" ${defaultCat === 'Dokumentasi Lapangan Umum' ? 'selected' : ''}>Dokumentasi Lapangan Umum</option>
                        </select>
                    </div>
                    <input type="hidden" name="foto_latitudes[]" value="${lat.toFixed(7)}">
                    <input type="hidden" name="foto_longitudes[]" value="${lng.toFixed(7)}">
                    <input type="hidden" name="foto_accuracies[]" value="${acc}">
                </div>
            `;

            card.querySelector('.btn-delete-card').addEventListener('click', function() {
                var targetIdx = parseInt(card.dataset.index);
                removePhotoAtIndex(targetIdx);
            });

            photoGalleryGrid.appendChild(card);
        }

        function removePhotoAtIndex(removeIndex) {
            var newTransfer = new DataTransfer();
            for (var i = 0; i < photoTransfer.files.length; i++) {
                if (i !== removeIndex) {
                    newTransfer.items.add(photoTransfer.files[i]);
                }
            }
            photoTransfer = newTransfer;

            photoGalleryGrid.innerHTML = '';
            var remainingFiles = Array.from(photoTransfer.files);
            photoTransfer = new DataTransfer();
            handleSelectedFiles(remainingFiles);
            updateEmptyNotice();
        }

        // Signature Canvas & Page Initialization
        document.addEventListener('DOMContentLoaded', () => {
            // Synchronize selected office address if needed
            var pelabuhanEl = document.getElementById('pelabuhan');
            var addrEl = document.getElementById('alamat_pemeriksa');
            if (pelabuhanEl && addrEl && (!addrEl.value || addrEl.value.includes('Jl. Cikapayang No.5, Bandung, Jawa Barat'))) {
                var curOpt = pelabuhanEl.options[pelabuhanEl.selectedIndex];
                if (curOpt && curOpt.getAttribute('data-alamat')) {
                    addrEl.value = curOpt.getAttribute('data-alamat');
                }
            }

            // Apply initial styles to selects
            document.querySelectorAll('.area-status-select').forEach(sel => styleSelect(sel));
            updateProgress();

            // Geotagging init
            applyFullAddress(currentFullAddress);
            detectGeolocation(false);
            initMiniMap(currentLatitude, currentLongitude);

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
            document.getElementById('formSanitasiKapal').addEventListener('submit', async function(e) {
                e.preventDefault();
                const form = this;
                const selects = document.querySelectorAll('.area-status-select');
                for (const sel of selects) {
                    if (!sel.value) {
                        alert('Mohon lengkapi seluruh Status Pemeriksaan pada 32 Area Fasilitas terlebih dahulu.');
                        sel.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        sel.focus();
                        return false;
                    }
                }

                if (!inputSignature.value) {
                    alert('Mohon bubuhkan Tanda Tangan Petugas Pemeriksa pada kotak tanda tangan digital.');
                    canvas.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    return false;
                }

                realPhotoInput.files = photoTransfer.files;

                const submitBtn = form.querySelector('button[type="submit"]');
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<span>Menyimpan Formulir...</span>';
                }

                try {
                    const formData = new FormData(form);
                    const response = await fetch(form.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    });

                    if (response.ok) {
                        const result = await response.json();
                        if (result.success) {
                            showPublicModal({
                                regNo: result.registration_no,
                                publicUrl: result.receipt_url,
                                title: 'Formulir Berhasil Disubmit!'
                            });
                            if (submitBtn) {
                                submitBtn.disabled = false;
                                submitBtn.innerHTML = '<span>✓ Berhasil Terkirim</span>';
                            }
                            return;
                        }
                    }
                    form.submit();
                } catch (err) {
                    console.error('AJAX submit fallback:', err);
                    form.submit();
                }
            });
        });
    </script>

    @include('travelconsent.modal_public_link')

</body>
</html>
