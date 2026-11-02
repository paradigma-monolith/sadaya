<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Daftar Pemakaian Alat Kesehatan P3K & Emergensi &bull; BKK Kelas I Bandung</title>

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
                            50: '#eff6ff',
                            100: '#dbeafe',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        .glass-card {
            background: #ffffff;
            border: 1px solid rgba(226, 232, 240, 0.9);
            box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.04), 0 8px 10px -6px rgba(15, 23, 42, 0.02);
        }
        .signature-canvas {
            touch-action: none;
            cursor: crosshair;
        }

        /* Geotagging & Mini Map Styles */
        .geotag-section {
            background: #ffffff;
            border: 1.5px solid #cbd5e1;
            border-radius: 20px;
            padding: 24px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);
            margin-bottom: 24px;
        }
        .geotag-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 18px;
            flex-wrap: wrap;
            gap: 12px;
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
            gap: 6px;
            padding: 6px 14px;
            border-radius: 9999px;
            font-size: 11.5px;
            font-weight: 700;
            background: #f1f5f9;
            color: #64748b;
            border: 1px solid #cbd5e1;
        }
        .geo-status-badge.active {
            background: #eff6ff;
            color: #1d4ed8;
            border-color: #93c5fd;
        }
        .geo-pulse {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #2563eb;
            display: inline-block;
            box-shadow: 0 0 0 0 rgba(37, 99, 235, 0.7);
            animation: pulse-dot 1.8s infinite;
        }
        @keyframes pulse-dot {
            0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(37, 99, 235, 0.7); }
            70% { transform: scale(1); box-shadow: 0 0 0 8px rgba(37, 99, 235, 0); }
            100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(37, 99, 235, 0); }
        }
        .geo-info-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 16px;
            margin-bottom: 18px;
        }
        @media (min-width: 768px) {
            .geo-info-grid {
                grid-template-columns: 1.25fr 1fr;
            }
        }
        .geo-info-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 14px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .geo-coords-display {
            font-family: 'JetBrains Mono', monospace;
            font-size: 12px;
            color: #0f172a;
            font-weight: 600;
            margin: 6px 0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 8px;
        }
        .btn-gps-refresh {
            background: #ffffff;
            border: 1px solid #cbd5e1;
            padding: 4px 10px;
            border-radius: 8px;
            font-size: 11px;
            font-weight: 700;
            color: #0f172a;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            transition: all 0.2s;
        }
        .btn-gps-refresh:hover {
            background: #eff6ff;
            border-color: #3b82f6;
            color: #1d4ed8;
        }
        .geo-map-container {
            width: 100%;
            height: 190px;
            border-radius: 14px;
            border: 1px solid #cbd5e1;
            overflow: hidden;
            background: #e2e8f0;
            z-index: 1;
        }
        .upload-buttons-bar {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-bottom: 14px;
        }
        .btn-camera-action {
            flex: 1;
            min-width: 170px;
            background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 100%);
            color: #ffffff;
            font-weight: 700;
            font-size: 13px;
            padding: 12px 18px;
            border-radius: 12px;
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.25);
            transition: all 0.2s;
        }
        .btn-camera-action:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(37, 99, 235, 0.35);
        }
        .btn-gallery-action {
            flex: 1;
            min-width: 170px;
            background: #f8fafc;
            color: #1e293b;
            font-weight: 700;
            font-size: 13px;
            padding: 12px 18px;
            border-radius: 12px;
            border: 1.5px solid #cbd5e1;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.2s;
        }
        .btn-gallery-action:hover {
            background: #eff6ff;
            border-color: #3b82f6;
            color: #1d4ed8;
        }
        .preset-badge {
            background: #f1f5f9;
            color: #334155;
            border: 1px solid #cbd5e1;
            padding: 3px 8px;
            border-radius: 6px;
            font-size: 10.5px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.15s;
        }
        .preset-badge:hover {
            background: #dbeafe;
            color: #1d4ed8;
            border-color: #93c5fd;
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
            border-color: #2563eb;
            background: #eff6ff;
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
            border: 4.5px solid #2563eb;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 0 24px rgba(37, 99, 235, 0.65);
            transition: transform 0.15s, background 0.15s;
        }
        .btn-shutter:hover {
            transform: scale(1.08);
            background: #eff6ff;
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

        <!-- Header Resmi BKK Kelas I Bandung -->
        <div class="glass-card rounded-3xl p-6 sm:p-8 flex flex-col md:flex-row items-center justify-between gap-6 border-b-4 border-b-blue-600">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-blue-600 text-white flex items-center justify-center font-black text-xl shadow-md">
                    BKK
                </div>
                <div>
                    <div class="text-[11px] font-mono tracking-widest text-blue-700 font-bold uppercase">
                        Kementerian Kesehatan Republik Indonesia
                    </div>
                    <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">
                        Balai Kekarantinaan Kesehatan Kelas I Bandung
                    </h1>
                    <p class="text-xs text-slate-500 font-medium">
                        Wilayah Kerja Bandara Husein Sastranegara, Kertajati (BIJB), &amp; Pelabuhan Jawa Barat
                    </p>
                </div>
            </div>

            <div class="text-right flex md:flex-col items-center md:items-end gap-2 text-xs">
                <div class="flex items-center gap-2">
                    <span class="px-3 py-1 rounded-full font-mono font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                        FORMULIR ALAT KESEHATAN
                    </span>
                    <a href="{{ route('travelconsent.home') }}" class="px-3 py-1 rounded-lg text-slate-500 hover:text-slate-900 border border-slate-200 hover:bg-slate-50 transition-colors font-semibold">
                        ← Portal Layanan
                    </a>
                </div>
                <span class="text-slate-400 font-mono text-[11px]">
                    Standar ICAO / Kemenkes RI
                </span>
            </div>
        </div>

        <!-- Form Container -->
        <form action="{{ route('travelconsent.submit_alat') }}" method="POST" enctype="multipart/form-data" id="mainForm" class="space-y-6">
            @csrf

            <!-- SECTION 1: Informasi Penerbangan & Kloter -->
            <div class="glass-card rounded-3xl p-6 sm:p-8 space-y-5">
                <div class="flex items-center gap-2.5 pb-3 border-b border-slate-200">
                    <span class="w-7 h-7 rounded-lg bg-blue-100 text-blue-700 font-bold flex items-center justify-center text-xs">1</span>
                    <h2 class="text-base font-extrabold text-slate-900">
                        Informasi Penerbangan &amp; Kloter Haji
                    </h2>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Tahun Haji -->
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Penerbangan Haji (H / M) <span class="text-rose-500">*</span>
                        </label>
                        <div class="flex items-center gap-2">
                            <input
                                type="text"
                                name="penerbangan_haji_h"
                                value="{{ old('penerbangan_haji_h', $currentH) }}"
                                placeholder="1447"
                                class="w-1/2 px-3 py-2 text-xs rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500 font-mono font-bold text-center"
                                required
                            />
                            <span class="text-slate-400 font-bold">H /</span>
                            <input
                                type="text"
                                name="penerbangan_haji_m"
                                value="{{ old('penerbangan_haji_m', $currentM) }}"
                                placeholder="2026"
                                class="w-1/2 px-3 py-2 text-xs rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500 font-mono font-bold text-center"
                                required
                            />
                            <span class="text-slate-400 font-bold">M</span>
                        </div>
                    </div>

                    <!-- Embarkasi -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Embarkasi <span class="text-rose-500">*</span>
                        </label>
                        <input
                            type="text"
                            name="embarkasi"
                            value="{{ old('embarkasi', 'Kertajati (KJT)') }}"
                            placeholder="Contoh: Kertajati (KJT) / JKS"
                            class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500 font-medium"
                            required
                        />
                    </div>

                    <!-- Nomor Kloter -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Nomor Kloter <span class="text-rose-500">*</span>
                        </label>
                        <input
                            type="text"
                            name="nomor_kloter"
                            value="{{ old('nomor_kloter') }}"
                            placeholder="Contoh: Kloter 01 / SOC 02"
                            class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500 font-medium"
                            required
                        />
                    </div>

                    <!-- Nomor Kontak -->
                    <div class="sm:col-span-2 lg:col-span-4">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Nomor Kontak PIC / Dokter Kloter (TKHI)
                        </label>
                        <input
                            type="text"
                            name="no_kontak"
                            value="{{ old('no_kontak') }}"
                            placeholder="Contoh: 0812-3456-7890 (WhatsApp)"
                            class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-blue-500 font-medium"
                        />
                    </div>
                </div>
            </div>

            <!-- SECTION 2: Pemakaian Kotak P3K (17 Items) -->
            <div class="glass-card rounded-3xl p-6 sm:p-8 space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-200">
                    <div class="flex items-center gap-2.5">
                        <span class="w-7 h-7 rounded-lg bg-blue-100 text-blue-700 font-bold flex items-center justify-center text-xs">2</span>
                        <div>
                            <h2 class="text-base font-extrabold text-slate-900">
                                Daftar Pemakaian Alat Kesehatan Kotak P3K (17 Item)
                            </h2>
                            <p class="text-[11px] text-slate-400">First Aid Kit standar kelaikan kesehatan penerbangan</p>
                        </div>
                    </div>

                    <button
                        type="button"
                        onclick="setAllP3kZero()"
                        class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-slate-100 hover:bg-slate-200 text-slate-700 transition-colors cursor-pointer self-start sm:self-auto"
                    >
                        Set Semua Terpakai: 0
                    </button>
                </div>

                <div class="border border-slate-200 rounded-2xl overflow-hidden overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 font-bold uppercase tracking-wider text-[11px]">
                                <th class="px-4 py-3 w-12 text-center">No</th>
                                <th class="px-4 py-3">Nama Alat Kesehatan</th>
                                <th class="px-4 py-3 w-24 text-center">Satuan</th>
                                <th class="px-4 py-3 w-24 text-center">Isi Standar</th>
                                <th class="px-4 py-3 w-28 text-center">Terpakai</th>
                                <th class="px-4 py-3">Keterangan / Kondisi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($p3kItems as $idx => $item)
                            <tr class="hover:bg-blue-50/40 transition-colors">
                                <td class="px-4 py-2.5 text-center font-mono text-slate-400">{{ $idx + 1 }}</td>
                                <td class="px-4 py-2.5 font-semibold text-slate-800">
                                    {{ $item['nama'] }}
                                    <input type="hidden" name="items_p3k[{{ $idx }}][nama]" value="{{ $item['nama'] }}" />
                                </td>
                                <td class="px-4 py-2.5 text-center">
                                    <span class="px-2 py-0.5 rounded text-[10.5px] font-mono bg-slate-100 text-slate-600">
                                        {{ $item['satuan'] }}
                                    </span>
                                    <input type="hidden" name="items_p3k[{{ $idx }}][satuan]" value="{{ $item['satuan'] }}" />
                                </td>
                                <td class="px-4 py-2.5 text-center">
                                    <input
                                        type="text"
                                        name="items_p3k[{{ $idx }}][isi]"
                                        value="{{ $item['isi'] }}"
                                        class="w-16 px-2 py-1 text-center font-mono text-xs rounded-lg border border-slate-200 bg-slate-50 focus:bg-white"
                                    />
                                </td>
                                <td class="px-4 py-2.5 text-center">
                                    <input
                                        type="number"
                                        min="0"
                                        name="items_p3k[{{ $idx }}][terpakai]"
                                        value="0"
                                        class="p3k-terpakai w-16 px-2 py-1 text-center font-mono font-bold text-xs rounded-lg border border-blue-200 bg-blue-50/50 focus:bg-white focus:ring-2 focus:ring-blue-500"
                                    />
                                </td>
                                <td class="px-4 py-2.5">
                                    <input
                                        type="text"
                                        name="items_p3k[{{ $idx }}][keterangan]"
                                        placeholder="Kondisi / pemakaian..."
                                        class="w-full px-2.5 py-1 text-xs rounded-lg border border-slate-200 bg-slate-50 focus:bg-white"
                                    />
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- SECTION 3: Pemakaian Kotak Emergensi (21 Items) -->
            <div class="glass-card rounded-3xl p-6 sm:p-8 space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-200">
                    <div class="flex items-center gap-2.5">
                        <span class="w-7 h-7 rounded-lg bg-blue-100 text-blue-700 font-bold flex items-center justify-center text-xs">3</span>
                        <div>
                            <h2 class="text-base font-extrabold text-slate-900">
                                Daftar Pemakaian Alat Kesehatan Emergensi (21 Item)
                            </h2>
                            <p class="text-[11px] text-slate-400">Emergency Medical Kit (EMK) kabin pesawat udara</p>
                        </div>
                    </div>

                    <button
                        type="button"
                        onclick="setAllEmZero()"
                        class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-slate-100 hover:bg-slate-200 text-slate-700 transition-colors cursor-pointer self-start sm:self-auto"
                    >
                        Set Semua Terpakai: 0
                    </button>
                </div>

                <div class="border border-slate-200 rounded-2xl overflow-hidden overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 font-bold uppercase tracking-wider text-[11px]">
                                <th class="px-4 py-3 w-12 text-center">No</th>
                                <th class="px-4 py-3">Nama Alat Emergensi</th>
                                <th class="px-4 py-3 w-24 text-center">Satuan</th>
                                <th class="px-4 py-3 w-24 text-center">Isi Standar</th>
                                <th class="px-4 py-3 w-28 text-center">Terpakai</th>
                                <th class="px-4 py-3">Keterangan / Kondisi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($emergensiItems as $idx => $item)
                            <tr class="hover:bg-blue-50/40 transition-colors">
                                <td class="px-4 py-2.5 text-center font-mono text-slate-400">{{ $idx + 1 }}</td>
                                <td class="px-4 py-2.5 font-semibold text-slate-800">
                                    {{ $item['nama'] }}
                                    <input type="hidden" name="items_emergensi[{{ $idx }}][nama]" value="{{ $item['nama'] }}" />
                                </td>
                                <td class="px-4 py-2.5 text-center">
                                    <span class="px-2 py-0.5 rounded text-[10.5px] font-mono bg-slate-100 text-slate-600">
                                        {{ $item['satuan'] }}
                                    </span>
                                    <input type="hidden" name="items_emergensi[{{ $idx }}][satuan]" value="{{ $item['satuan'] }}" />
                                </td>
                                <td class="px-4 py-2.5 text-center">
                                    <input
                                        type="text"
                                        name="items_emergensi[{{ $idx }}][isi]"
                                        value="{{ $item['isi'] }}"
                                        class="w-16 px-2 py-1 text-center font-mono text-xs rounded-lg border border-slate-200 bg-slate-50 focus:bg-white"
                                    />
                                </td>
                                <td class="px-4 py-2.5 text-center">
                                    <input
                                        type="number"
                                        min="0"
                                        name="items_emergensi[{{ $idx }}][terpakai]"
                                        value="0"
                                        class="em-terpakai w-16 px-2 py-1 text-center font-mono font-bold text-xs rounded-lg border border-blue-200 bg-blue-50/50 focus:bg-white focus:ring-2 focus:ring-blue-500"
                                    />
                                </td>
                                <td class="px-4 py-2.5">
                                    <input
                                        type="text"
                                        name="items_emergensi[{{ $idx }}][keterangan]"
                                        placeholder="Kondisi / kegawatdaruratan..."
                                        class="w-full px-2.5 py-1 text-xs rounded-lg border border-slate-200 bg-slate-50 focus:bg-white"
                                    />
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- SECTION 4: Dokumentasi & Geotagging GPS -->
            <div class="geotag-section" id="geotagSection">
                <div class="geotag-header">
                    <div>
                        <div class="geotag-title">
                            <span class="w-7 h-7 rounded-lg bg-blue-100 text-blue-700 font-bold flex items-center justify-center text-xs">4</span>
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14.5 4h-5L7 7H4a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-3l-2.5-3z"/><circle cx="12" cy="13" r="3"/></svg>
                            <span>Dokumentasi Foto Lapangan &amp; Geotagging GPS</span>
                        </div>
                        <div style="font-size: 12px; color: #64748b; margin-top: 2px;">
                            Bukti foto pemeriksaan kotak P3K &amp; kit medis darurat pesawat. Koordinat GPS &amp; cap waktu terekam otomatis.
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
                                <span>Lat: <strong id="lblLatitude">-6.657500</strong> &bull; Lng: <strong id="lblLongitude">108.196670</strong></span>
                                <span style="font-size: 11px; color: #1d4ed8; background: #dbeafe; padding: 2px 6px; border-radius: 4px;" id="lblAccuracy">Akurasi: &plusmn; 10m</span>
                            </div>

                            <div id="displayFullAddress" style="font-size: 11px; color: #475569; margin: 4px 0 8px 0; background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 6px; padding: 6px 10px; display: flex; align-items: flex-start; gap: 6px;">
                                <span style="font-size: 13px; line-height: 1;">🏠</span>
                                <span id="lblFullAddress" style="word-break: break-word; line-height: 1.3;">Memuat alamat lokasi bandara...</span>
                            </div>

                            <div style="margin-top: 8px;">
                                <label style="font-size: 11.5px; font-weight: 600; color: #475569; display: block; margin-bottom: 3px;">Keterangan / Lokasi Bandara:</label>
                                <input type="text" name="lokasi_geotag" id="input_lokasi_geotag" class="w-full text-xs rounded-lg border border-slate-300 p-2 focus:ring-2 focus:ring-blue-500" placeholder="Contoh: Apron Bandara Internasional Kertajati (BIJB)" value="{{ old('lokasi_geotag', 'Bandara Internasional Kertajati (BIJB), Jl. Raya Kertajati - Majalengka, Kab. Majalengka 45457') }}">
                            </div>

                            <div style="margin-top: 10px; display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
                                <span style="font-size: 11px; color: #64748b;">Pilihan Cepat:</span>
                                <button type="button" class="preset-badge" onclick="setPresetLocation(-6.65750, 108.19667, 'Bandara Internasional Kertajati (BIJB) - Majalengka', 'Bandara Internasional Kertajati (BIJB), Jl. Raya Kertajati - Majalengka, Kertajati, Kab. Majalengka, Jawa Barat 45457')">Bandara KJT (BIJB)</button>
                                <button type="button" class="preset-badge" onclick="setPresetLocation(-6.90389, 107.61861, 'Bandara Husein Sastranegara (BDO) - Bandung', 'Bandara Husein Sastranegara, Jl. Pajajaran No. 156, Husen Sastranegara, Kec. Cicendo, Kota Bandung, Jawa Barat 40174')">Bandara BDO (Bandung)</button>
                                <button type="button" class="preset-badge" onclick="setPresetLocation(-6.24150, 107.00120, 'Asrama Haji Embarkasi Jakarta-Bekasi (JKS)', 'UPT Asrama Haji Embarkasi Jakarta-Bekasi, Jl. Kemakmuran No. 72, Marga Jaya, Kec. Bekasi Selatan, Kota Bekasi 17141')">Asrama Haji Bekasi</button>
                            </div>
                        </div>

                        <div style="margin-top: 10px; font-size: 11px; color: #64748b; display: flex; align-items: center; gap: 4px;">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                            <span>Setiap foto yang diunggah akan otomatis tersemat stempel geotagging koordinat ini.</span>
                        </div>
                    </div>

                    <!-- Box 2: Interactive Location Mini-Map -->
                    <div class="geo-map-container" id="geoMiniMap">
                        <!-- Leaflet map container -->
                    </div>
                </div>

                <!-- Hidden Inputs for Form Submission -->
                <input type="hidden" name="latitude" id="input_latitude" value="{{ old('latitude', '-6.657500') }}">
                <input type="hidden" name="longitude" id="input_longitude" value="{{ old('longitude', '108.196670') }}">
                
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
                    <div style="font-weight: 700; color: #1e293b; font-size: 13px;">Seret &amp; letakkan foto dokumentasi kotak P3K di sini</div>
                    <div style="font-size: 11.5px; color: #64748b; margin-top: 4px;">Mendukung format JPG, PNG, WEBP (Maksimal 12MB per berkas foto).</div>
                </div>

                <!-- Uploaded Photos Gallery Preview -->
                <div class="photo-gallery-grid" id="photoGalleryGrid">
                    <!-- Populated dynamically via JavaScript -->
                </div>

                <div id="emptyPhotoNotice" style="text-align: center; padding: 25px 15px; color: #94a3b8; font-size: 12.5px; background: #fafafa; border: 1px dashed #e2e8f0; border-radius: 12px; margin-top: 15px;">
                    Belum ada foto dokumentasi yang dilampirkan. Klik tombol <strong>Ambil Foto Kamera</strong> atau <strong>Pilih Berkas Foto</strong> di atas untuk menambahkan bukti dokumentasi kotak P3K &amp; emergensi.
                </div>
            </div>

            <!-- SECTION 5: Tanda Tangan Digital Ganda -->
            <div class="glass-card rounded-3xl p-6 sm:p-8 space-y-4">
                <div class="flex items-center gap-2.5 pb-3 border-b border-slate-200">
                    <span class="w-7 h-7 rounded-lg bg-blue-100 text-blue-700 font-bold flex items-center justify-center text-xs">5</span>
                    <h2 class="text-base font-extrabold text-slate-900">
                        Pengesahan &amp; Tanda Tangan Digital Ganda
                    </h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Awak Kabin / Crew -->
                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 space-y-3">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                            Awak Kabin / Kru Pesawat <span class="text-rose-500">*</span>
                        </label>
                        <input
                            type="text"
                            name="crew_nama"
                            placeholder="Nama Lengkap Awak Kabin"
                            class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 bg-white font-medium"
                            required
                        />
                        <div class="space-y-1">
                            <div class="flex justify-between items-center text-[11px] text-slate-400">
                                <span>Tanda Tangan Digital Awak Kabin:</span>
                                <button type="button" onclick="clearCrewSign()" class="text-rose-500 hover:underline">Hapus</button>
                            </div>
                            <div class="border border-slate-300 rounded-xl bg-white overflow-hidden">
                                <canvas id="crewCanvas" width="400" height="150" class="signature-canvas w-full h-32"></canvas>
                            </div>
                            <input type="hidden" name="crew_ttd" id="crewSignInput" />
                        </div>
                    </div>

                    <!-- Petugas Karantina / Pelapor -->
                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 space-y-3">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                            Petugas Karantina BKK / Pelapor <span class="text-rose-500">*</span>
                        </label>
                        <input
                            type="text"
                            name="pelapor_nama"
                            placeholder="Nama Petugas Karantina BKK / TKHI"
                            class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 bg-white font-medium"
                            required
                        />
                        <div class="space-y-1">
                            <div class="flex justify-between items-center text-[11px] text-slate-400">
                                <span>Tanda Tangan Digital Petugas / Pelapor:</span>
                                <button type="button" onclick="clearPelaporSign()" class="text-rose-500 hover:underline">Hapus</button>
                            </div>
                            <div class="border border-slate-300 rounded-xl bg-white overflow-hidden">
                                <canvas id="pelaporCanvas" width="400" height="150" class="signature-canvas w-full h-32"></canvas>
                            </div>
                            <input type="hidden" name="pelapor_ttd" id="pelaporSignInput" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit Button Bar -->
            <div class="glass-card rounded-3xl p-6 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="text-xs text-slate-500">
                    Pastikan seluruh data pemakaian alat kesehatan terisi dengan benar sebelum mengirim.
                </div>
                <button
                    type="submit"
                    class="w-full sm:w-auto px-8 py-3.5 rounded-2xl font-bold text-sm text-white bg-blue-600 hover:bg-blue-500 transition-all shadow-md cursor-pointer flex items-center justify-center gap-2"
                >
                    <span>Kirim Formulir Pemakaian Alkes</span>
                    <span>→</span>
                </button>
            </div>

        </form>

        <!-- Footer -->
        <div class="text-center text-xs text-slate-400 py-4">
            &copy; 2026 Balai Kekarantinaan Kesehatan Kelas I Bandung &bull; Sistem Informasi Karantina Terpadu
        </div>

    </div>

    <!-- Live Camera WebRTC Viewfinder Modal -->
    <div class="camera-modal-overlay" id="liveCameraModal">
        <div class="camera-modal-card">
            <div class="camera-modal-header">
                <div style="font-weight: 700; font-size: 14px; display: flex; align-items: center; gap: 8px;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#60a5fa" stroke-width="2.5"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>
                    <span>Kamera Live Inspeksi Alkes P3K &bull; Geotagging GPS</span>
                </div>
                <button type="button" onclick="closeLiveCamera()" style="background: #334155; color: white; border: none; padding: 5px 12px; border-radius: 8px; cursor: pointer;">&times; Tutup</button>
            </div>

            <div class="camera-viewfinder-wrapper">
                <video id="cameraVideo" autoplay playsinline muted></video>
                <div class="camera-live-overlay">
                    <div>✈️ BKK BANDUNG &bull; PEMAKAIAN ALAT KESEHATAN P3K</div>
                    <div id="camLiveCoords">📍 Koordinat: -6.657500, 108.196670</div>
                    <div id="camLiveTime">🕒 Memuat waktu...</div>
                    <div id="camLiveAddress" style="font-size: 9.5px; color: #e2e8f0; margin-top: 2px; line-height: 1.25; opacity: 0.95; word-break: break-word;">
                        🏠 <span id="camLiveAddressText">Memuat alamat lokasi bandara...</span>
                    </div>
                </div>
            </div>

            <div class="camera-controls-bar">
                <button type="button" class="btn-cam-switch" id="btnSwitchCamera" title="Ganti Kamera Depan / Belakang">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21.5 2v6h-6M21.34 15.57a10 10 0 1 1-.57-8.38l5.67-5.67"/></svg>
                    <span>Putar Kamera</span>
                </button>

                <button type="button" class="btn-shutter" id="btnShutter" title="Jepret Foto Sekarang">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="3" fill="#2563eb"/></svg>
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

    <!-- Script Signature & GPS -->
    <script>
        function setAllP3kZero() {
            document.querySelectorAll('.p3k-terpakai').forEach(el => el.value = '0');
        }

        function setAllEmZero() {
            document.querySelectorAll('.em-terpakai').forEach(el => el.value = '0');
        }

        /* =========================================================
         * 1. GPS Geolocation & Interactive Map
         * ========================================================= */
        var currentLatitude = parseFloat(document.getElementById('input_latitude').value) || -6.657500;
        var currentLongitude = parseFloat(document.getElementById('input_longitude').value) || 108.196670;
        var currentAccuracy = 10;
        var currentFullAddress = document.getElementById('input_lokasi_geotag')?.value || 'Bandara Internasional Kertajati (BIJB), Jl. Raya Kertajati - Majalengka, Kertajati, Kab. Majalengka, Jawa Barat 45457';
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
                markerInstance.bindPopup('<b>Lokasi Pemeriksaan Alkes P3K</b><br><span style="font-size: 11px; color: #475569;">' + escapeHtml(addr) + '</span>');
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
                    markerInstance.bindPopup('<b>Lokasi Pemeriksaan Alkes P3K</b><br><span style="font-size: 11px; color: #475569;">' + escapeHtml(currentFullAddress) + '</span>').openPopup();
                    L.control.zoom({ position: 'bottomright' }).addTo(mapInstance);
                } else {
                    mapInstance.setView([lat, lng], 15);
                    if (markerInstance) {
                        markerInstance.setLatLng([lat, lng]);
                        markerInstance.bindPopup('<b>Lokasi Pemeriksaan Alkes P3K</b><br><span style="font-size: 11px; color: #475569;">' + escapeHtml(currentFullAddress) + '</span>');
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
                            badgeText.textContent = 'GPS Manual / Preset Bandara';
                        }
                        updateGpsUI(currentLatitude, currentLongitude, 25);

                        if (showNotice) {
                            alert('Izin akses lokasi perangkat belum aktif atau sinyal GPS lemah. Koordinat default bandara digunakan.');
                        }
                    },
                    { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
                );
            } else {
                updateGpsUI(currentLatitude, currentLongitude, 50);
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
                if (camLiveAddrText) camLiveAddrText.textContent = this.value || 'Bandara';
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
                alert('Browser Anda tidak mendukung akses live kamera WebRTC. Membuka pemilih berkas...');
                document.getElementById('cameraInput').click();
                return;
            }

            modal.style.display = 'flex';
            updateLiveCameraOverlayClock();
            var camLiveAddrText = document.getElementById('camLiveAddressText');
            if (camLiveAddrText) {
                camLiveAddrText.textContent = currentFullAddress || document.getElementById('input_lokasi_geotag')?.value || 'Bandara';
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
            var line1 = '✈️ BKK BANDUNG • PEMAKAIAN ALAT KESEHATAN P3K';
            ctx.fillText(line1, padding, canvas.height - bannerHeight + (fontSize * 1.25));

            // 2. Coordinates Line
            ctx.font = 'bold ' + (fontSize * 0.82) + 'px monospace';
            ctx.fillStyle = '#60a5fa';
            var line2 = '📍 Lat: ' + currentLatitude.toFixed(6) + ', Lng: ' + currentLongitude.toFixed(6) + ' (±' + currentAccuracy + 'm)';
            ctx.fillText(line2, padding, canvas.height - bannerHeight + (fontSize * 2.35));

            // 3. Time & Flight / Kloter Line
            ctx.font = (fontSize * 0.78) + 'px monospace';
            ctx.fillStyle = '#cbd5e1';
            var kloter = document.querySelector('input[name="nomor_kloter"]')?.value || '-';
            var embarkasi = document.querySelector('input[name="embarkasi"]')?.value || 'KJT';
            var line3 = '🕒 ' + new Date().toLocaleString('id-ID') + ' • Kloter: ' + kloter + ' (' + embarkasi + ')';
            ctx.fillText(line3, padding, canvas.height - bannerHeight + (fontSize * 3.4));

            // 4. Address Line
            ctx.font = addressFontSize + 'px sans-serif';
            ctx.fillStyle = '#f1f5f9';
            var addrRaw = currentFullAddress || document.getElementById('input_lokasi_geotag')?.value || 'Bandara';
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
                var fileName = 'alkes_snap_' + Date.now() + '.jpg';
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
                'Kotak P3K Pesawat (First Aid Kit)',
                'Kotak Kit Medis Darurat (Emergency Medical Kit)',
                'Segel & Kunci Kotak Medis',
                'Resep / Bukti Pemakaian Alkes',
                'Pemeriksaan Fisik & Sterilitas',
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

            var displayAddress = currentFullAddress || document.getElementById('input_lokasi_geotag')?.value || 'Bandara';

            card.innerHTML = `
                <button type="button" class="btn-delete-card" title="Hapus foto ini">&times;</button>
                <div class="photo-preview-wrap">
                    <img src="${dataUrl}" alt="${file.name}">
                    <div class="geotag-stamp-badge">
                        <div style="font-weight: 700;">✈️ BKK BANDUNG &bull; ALKES P3K</div>
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
                        <label style="font-size: 10.5px; color: #64748b; font-weight: 600; display: block; margin-bottom: 2px;">Kategori Area / Komponen:</label>
                        <select name="foto_kategori[]" class="photo-category-input">
                            <option value="Kotak P3K Pesawat (First Aid Kit)" ${defaultCat.includes('P3K') ? 'selected' : ''}>Kotak P3K Pesawat (First Aid Kit)</option>
                            <option value="Kotak Kit Medis Darurat (Emergency Medical Kit)" ${defaultCat.includes('Darurat') ? 'selected' : ''}>Kotak Kit Medis Darurat (Emergency Medical Kit)</option>
                            <option value="Segel & Kunci Kotak Medis" ${defaultCat.includes('Segel') ? 'selected' : ''}>Segel & Kunci Kotak Medis</option>
                            <option value="Resep / Bukti Pemakaian Alkes" ${defaultCat.includes('Resep') ? 'selected' : ''}>Resep / Bukti Pemakaian Alkes</option>
                            <option value="Pemeriksaan Fisik & Sterilitas" ${defaultCat.includes('Fisik') ? 'selected' : ''}>Pemeriksaan Fisik & Sterilitas</option>
                            <option value="Dokumentasi Lapangan Umum" ${defaultCat.includes('Umum') ? 'selected' : ''}>Dokumentasi Lapangan Umum</option>
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

        // Setup Signature Pad Helper
        function initSignature(canvasId, inputId) {
            const canvas = document.getElementById(canvasId);
            const input = document.getElementById(inputId);
            if (!canvas) return { clear: () => {} };

            const ctx = canvas.getContext('2d');
            let isDrawing = false;
            let hasDrawn = false;

            function resizeCanvas() {
                const rect = canvas.getBoundingClientRect();
                canvas.width = rect.width;
                canvas.height = rect.height;
                ctx.lineWidth = 2.5;
                ctx.lineCap = 'round';
                ctx.lineJoin = 'round';
                ctx.strokeStyle = '#0f172a';
            }
            window.addEventListener('resize', resizeCanvas);
            setTimeout(resizeCanvas, 100);

            function getPos(e) {
                const rect = canvas.getBoundingClientRect();
                const clientX = e.touches ? e.touches[0].clientX : e.clientX;
                const clientY = e.touches ? e.touches[0].clientY : e.clientY;
                return {
                    x: clientX - rect.left,
                    y: clientY - rect.top
                };
            }

            function startDraw(e) {
                isDrawing = true;
                const pos = getPos(e);
                ctx.beginPath();
                ctx.moveTo(pos.x, pos.y);
                if (e.cancelable) e.preventDefault();
            }

            function draw(e) {
                if (!isDrawing) return;
                const pos = getPos(e);
                ctx.lineTo(pos.x, pos.y);
                ctx.stroke();
                hasDrawn = true;
                if (e.cancelable) e.preventDefault();
            }

            function endDraw() {
                if (isDrawing && hasDrawn) {
                    input.value = canvas.toDataURL('image/png');
                }
                isDrawing = false;
            }

            canvas.addEventListener('mousedown', startDraw);
            canvas.addEventListener('mousemove', draw);
            window.addEventListener('mouseup', endDraw);

            canvas.addEventListener('touchstart', startDraw, { passive: false });
            canvas.addEventListener('touchmove', draw, { passive: false });
            window.addEventListener('touchend', endDraw);

            return {
                clear: function() {
                    ctx.clearRect(0, 0, canvas.width, canvas.height);
                    input.value = '';
                    hasDrawn = false;
                }
            };
        }

        let crewSignPad, pelaporSignPad;
        document.addEventListener('DOMContentLoaded', function() {
            // Geotagging init
            applyFullAddress(currentFullAddress);
            detectGeolocation(false);
            initMiniMap(currentLatitude, currentLongitude);

            crewSignPad = initSignature('crewCanvas', 'crewSignInput');
            pelaporSignPad = initSignature('pelaporCanvas', 'pelaporSignInput');
        });

        function clearCrewSign() {
            if (crewSignPad) crewSignPad.clear();
        }

        function clearPelaporSign() {
            if (pelaporSignPad) pelaporSignPad.clear();
        }

        // Form Submit Hook
        document.querySelector('form').addEventListener('submit', async function(e) {
            e.preventDefault();
            const form = this;
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
    </script>

    @include('travelconsent.modal_public_link')

</body>
</html>

