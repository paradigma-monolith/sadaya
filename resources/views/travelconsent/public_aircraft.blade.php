<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AIRCRAFT SANITATION INSPECTION CHECK LIST &bull; BKK Bandung</title>
    <!-- Modern Tailwind & Bootstrap style utilities -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
    <style>
        *, ::before, ::after { box-sizing: border-box; border-width: 0; border-style: solid; border-color: #e2e8f0; }
        body {
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
            font-size: 13.5px;
            line-height: 1.5;
            color: #0f172a;
            background-color: #f8fafc;
            margin: 0;
            padding: 20px 10px 60px 10px;
        }
        .container {
            max-width: 1040px;
            margin-left: auto;
            margin-right: auto;
            background: #ffffff;
            padding: 30px 40px;
            border-radius: 20px;
            box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.06), 0 4px 6px -2px rgba(0, 0, 0, 0.04);
            border: 1px solid #e2e8f0;
        }
        .kop-surat {
            width: 100%;
            text-align: center;
            margin-bottom: 24px;
        }
        .kop-surat img {
            width: 100%;
            max-width: 100%;
            height: auto;
            display: block;
            margin: 0 auto;
        }
        .doc-title {
            text-align: center;
            font-size: 17px;
            font-weight: 800;
            text-decoration: underline;
            letter-spacing: 0.5px;
            color: #0f172a;
            margin-bottom: 4px;
        }
        .doc-subtitle {
            text-align: center;
            font-size: 12px;
            font-style: italic;
            color: #64748b;
            margin-bottom: 24px;
        }
        .italic-eng {
            font-style: italic;
            font-size: 0.88em;
            color: #64748b;
        }
        .table-meta {
            width: 100%;
            margin-bottom: 20px;
            border-collapse: collapse;
        }
        .table-meta td {
            padding: 6px 4px;
            vertical-align: middle;
        }
        .underline-input {
            width: 100%;
            border: none;
            border-bottom: 1.5px dashed #475569;
            background: transparent;
            padding: 4px 6px;
            font-size: 13.5px;
            font-weight: 600;
            color: #0f172a;
            outline: none;
            transition: all 0.2s;
        }
        .underline-input:focus {
            border-bottom: 2px solid #2563eb;
            background: #f0f7ff;
            border-radius: 4px 4px 0 0;
        }
        .check-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .check-table th, .check-table td {
            border: 1px solid #cbd5e1;
            padding: 8px 10px;
            vertical-align: middle;
        }
        .check-table thead th {
            background-color: #f1f5f9;
            font-weight: 700;
            text-align: center;
            color: #1e293b;
        }
        .radio-custom {
            width: 20px;
            height: 20px;
            accent-color: #2563eb;
            cursor: pointer;
            vertical-align: middle;
        }
        .checkbox-custom {
            width: 18px;
            height: 18px;
            accent-color: #2563eb;
            cursor: pointer;
            vertical-align: middle;
        }
        .section-header-row {
            background-color: #f8fafc;
            font-weight: 700;
        }
        .signature-wrapper {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
            margin-top: 35px;
            text-align: center;
        }
        @media (max-width: 640px) {
            .signature-wrapper {
                grid-template-columns: 1fr;
            }
            .container {
                padding: 16px;
            }
        }
        .sig-card {
            background: #fafaf9;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 16px;
        }
        .sig-canvas {
            border: 1.5px dashed #94a3b8;
            border-radius: 10px;
            background: #ffffff;
            cursor: crosshair;
            touch-action: none;
            width: 100%;
            max-width: 340px;
            height: 140px;
        }
        .btn-clear {
            background: #f1f5f9;
            border: 1px solid #cbd5e1;
            padding: 5px 12px;
            font-size: 11.5px;
            font-weight: 600;
            border-radius: 8px;
            color: #475569;
            cursor: pointer;
            margin-top: 8px;
            transition: all 0.2s;
        }
        .btn-clear:hover {
            background: #e2e8f0;
            color: #0f172a;
        }
        .btn-submit {
            background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
            color: white;
            font-weight: 700;
            font-size: 15px;
            padding: 14px 40px;
            border-radius: 14px;
            border: none;
            cursor: pointer;
            box-shadow: 0 4px 14px -2px rgba(2, 132, 199, 0.4);
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .btn-submit:hover {
            background: linear-gradient(135deg, #0369a1 0%, #075985 100%);
            box-shadow: 0 6px 20px -2px rgba(2, 132, 199, 0.6);
            transform: translateY(-1px);
        }
        .alert-error {
            background-color: #ffe4e6;
            border: 1px solid #fecdd3;
            color: #9f1239;
            padding: 12px 16px;
            border-radius: 12px;
            margin-bottom: 20px;
        }
        .badge-verified {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #ecfdf5;
            color: #047857;
            border: 1px solid #a7f3d0;
            padding: 4px 12px;
            border-radius: 9999px;
            font-size: 11.5px;
            font-weight: 600;
        }

        /* Geotagging & Photo Documentation Styles */
        .geotag-section {
            background: #ffffff;
            border: 1.5px solid #cbd5e1;
            border-radius: 18px;
            padding: 24px;
            margin-top: 30px;
            margin-bottom: 30px;
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
            color: #0284c7;
            background: #f0f9ff;
            border: 1px solid #bae6fd;
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
            background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
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
            box-shadow: 0 4px 12px rgba(2, 132, 199, 0.35);
            transition: all 0.2s;
        }
        .btn-camera-action:hover {
            background: linear-gradient(135deg, #0369a1 0%, #075985 100%);
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
            background: #e0f2fe;
            color: #0369a1;
            border-color: #7dd3fc;
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
            border-color: #0284c7;
            background: #f0f9ff;
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
            height: 160px;
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
            background: linear-gradient(to top, rgba(15, 23, 42, 0.95) 0%, rgba(15, 23, 42, 0.75) 80%, transparent 100%);
            padding: 6px 10px 4px 10px;
            color: #ffffff;
            font-family: 'JetBrains Mono', monospace;
            font-size: 10px;
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
            border: 4.5px solid #0284c7;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 0 24px rgba(2, 132, 199, 0.65);
            transition: transform 0.15s, background 0.15s;
        }
        .btn-shutter:hover {
            transform: scale(1.08);
            background: #f0f9ff;
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
<body>

<div class="container">
    <!-- Kop Surat Resmi Balai Kekarantinaan Kesehatan Bandung -->
    <div class="kop-surat">
        <img src="{{ asset('storage/images/Kop_BKK_bandung.png') }}" alt="Kop Surat Balai Kekarantinaan Kesehatan Bandung" onerror="this.onerror=null;this.src='{{ asset('images/Kop_BKK_bandung.png') }}';">
    </div>

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; flex-wrap: wrap; gap: 10px;">
        <span class="badge-verified">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-4"/></svg>
            Sistem Informasi Pengawasan Kekarantinaan Kesehatan Udara
        </span>
        <span style="font-family: 'JetBrains Mono', monospace; font-size: 11px; color: #64748b;">
            Form Standard IHR 2005 &bull; Permenkes No. 7 Tahun 2024
        </span>
    </div>

    <div class="doc-title">AIRCRAFT SANITATION INSPECTION CHECK LIST</div>
    <div class="doc-subtitle">DAFTAR PEMERIKSAAN KESEHATAN DAN SANITASI PESAWAT UDARA</div>

    @if ($errors->any())
        <div class="alert-error">
            <strong>Terdapat kesalahan pengisian formulir:</strong>
            <ul style="margin: 6px 0 0 20px; padding: 0;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('travelconsent.submit_aircraft') }}" method="POST" id="aircraftForm" enctype="multipart/form-data">
        @csrf

        <!-- Metadata Header Table -->
        <table class="table-meta">
            <tr>
                <td width="28%"><strong><u>Pemilik atau Penyelenggara</u></strong><br><span class="italic-eng">Owner or Operator</span></td>
                <td width="2%" align="center">:</td>
                <td width="38%">
                    <input type="text" name="pemilik" class="underline-input" placeholder="Contoh: PT Garuda Indonesia / Lion Air / Citilink" required value="{{ old('pemilik') }}">
                </td>
                <td width="12%"><strong><u>Tanggal</u></strong><br><span class="italic-eng">Date</span></td>
                <td width="2%" align="center">:</td>
                <td width="18%">
                    <input type="date" name="tanggal" class="underline-input" required value="{{ old('tanggal', $today) }}">
                </td>
            </tr>
            <tr>
                <td><strong><u>Tanda Kebangsaan dan Pendaftaran</u></strong><br><span class="italic-eng">Marks of Nationality and Registration</span></td>
                <td align="center">:</td>
                <td>
                    <input type="text" name="tanda_kebangsaan" class="underline-input" placeholder="Contoh: PK-GIA / PK-LJQ" required value="{{ old('tanda_kebangsaan') }}">
                </td>
                <td><strong><u>Pukul</u></strong><br><span class="italic-eng">Time</span></td>
                <td align="center">:</td>
                <td>
                    <input type="time" name="waktu" class="underline-input" required value="{{ old('waktu', $nowTime) }}">
                </td>
            </tr>
        </table>

        <!-- Main Checklist Table -->
        <table class="check-table">
            <thead>
                <tr>
                    <th rowspan="2" width="5%">No</th>
                    <th rowspan="2" colspan="2" width="65%">
                        <u>Y a n g &nbsp; D i p e r i k s a :</u><br>
                        <span class="italic-eng">I t e m ' s &nbsp; I n s p e c t e d</span>
                    </th>
                    <th colspan="2" width="30%">Keadaan / <span class="italic-eng">Condition</span></th>
                </tr>
                <tr>
                    <th width="15%" style="background-color: #ecfdf5; color: #047857;">Baik<br><span class="italic-eng">(Good)</span></th>
                    <th width="15%" style="background-color: #fff1f2; color: #be123c;">Tidak Baik<br><span class="italic-eng">(Poor)</span></th>
                </tr>
            </thead>
            <tbody>
                <!-- 1. Kabin Penumpang -->
                <tr class="section-header-row">
                    <td align="center" rowspan="7"><strong>1</strong></td>
                    <td colspan="2"><u><strong>Kabin Penumpang</strong></u><br><span class="italic-eng">Passenger Cabins</span></td>
                    <td colspan="2" align="center" style="font-size: 11px; color: #64748b;">(Pilih salah satu)</td>
                </tr>
                <tr>
                    <td width="4%" align="right" style="border-right: none;">a.</td>
                    <td style="border-left: none;">Lantai dan dinding <span class="italic-eng">(Floor &amp; Wall)</span> ..........................</td>
                    <td align="center">
                        <input type="radio" class="radio-custom" name="kabin_penumpang[a]" value="baik" required {{ old('kabin_penumpang.a', 'baik') === 'baik' ? 'checked' : '' }}>
                    </td>
                    <td align="center">
                        <input type="radio" class="radio-custom" name="kabin_penumpang[a]" value="tidak_baik" {{ old('kabin_penumpang.a') === 'tidak_baik' ? 'checked' : '' }}>
                    </td>
                </tr>
                <tr>
                    <td align="right" style="border-right: none;">b.</td>
                    <td style="border-left: none;">Kursi <span class="italic-eng">(Seats)</span> ..........................</td>
                    <td align="center">
                        <input type="radio" class="radio-custom" name="kabin_penumpang[b]" value="baik" required {{ old('kabin_penumpang.b', 'baik') === 'baik' ? 'checked' : '' }}>
                    </td>
                    <td align="center">
                        <input type="radio" class="radio-custom" name="kabin_penumpang[b]" value="tidak_baik" {{ old('kabin_penumpang.b') === 'tidak_baik' ? 'checked' : '' }}>
                    </td>
                </tr>
                <tr>
                    <td align="right" style="border-right: none;">c.</td>
                    <td style="border-left: none;">Meja <span class="italic-eng">(Tables)</span> ..........................</td>
                    <td align="center">
                        <input type="radio" class="radio-custom" name="kabin_penumpang[c]" value="baik" required {{ old('kabin_penumpang.c', 'baik') === 'baik' ? 'checked' : '' }}>
                    </td>
                    <td align="center">
                        <input type="radio" class="radio-custom" name="kabin_penumpang[c]" value="tidak_baik" {{ old('kabin_penumpang.c') === 'tidak_baik' ? 'checked' : '' }}>
                    </td>
                </tr>
                <tr>
                    <td align="right" style="border-right: none;">d.</td>
                    <td style="border-left: none;">Bagasi Kabin <span class="italic-eng">(Cabin Baggage)</span> ..........................</td>
                    <td align="center">
                        <input type="radio" class="radio-custom" name="kabin_penumpang[d]" value="baik" required {{ old('kabin_penumpang.d', 'baik') === 'baik' ? 'checked' : '' }}>
                    </td>
                    <td align="center">
                        <input type="radio" class="radio-custom" name="kabin_penumpang[d]" value="tidak_baik" {{ old('kabin_penumpang.d') === 'tidak_baik' ? 'checked' : '' }}>
                    </td>
                </tr>
                <tr>
                    <td align="right" style="border-right: none;">e.</td>
                    <td style="border-left: none;">Kantong belakang kursi <span class="italic-eng">(Seat-back pocket)</span> ..........................</td>
                    <td align="center">
                        <input type="radio" class="radio-custom" name="kabin_penumpang[e]" value="baik" required {{ old('kabin_penumpang.e', 'baik') === 'baik' ? 'checked' : '' }}>
                    </td>
                    <td align="center">
                        <input type="radio" class="radio-custom" name="kabin_penumpang[e]" value="tidak_baik" {{ old('kabin_penumpang.e') === 'tidak_baik' ? 'checked' : '' }}>
                    </td>
                </tr>
                <tr>
                    <td align="right" style="border-right: none;">f.</td>
                    <td style="border-left: none;">Langit-langit kabin <span class="italic-eng">(Ceiling)</span> ..........................</td>
                    <td align="center">
                        <input type="radio" class="radio-custom" name="kabin_penumpang[f]" value="baik" required {{ old('kabin_penumpang.f', 'baik') === 'baik' ? 'checked' : '' }}>
                    </td>
                    <td align="center">
                        <input type="radio" class="radio-custom" name="kabin_penumpang[f]" value="tidak_baik" {{ old('kabin_penumpang.f') === 'tidak_baik' ? 'checked' : '' }}>
                    </td>
                </tr>

                <!-- 2. Toilet Compartment -->
                <tr class="section-header-row">
                    <td align="center" rowspan="6"><strong>2</strong></td>
                    <td colspan="2"><u><strong>Kompartemen Toilet</strong></u><br><span class="italic-eng">Toilet Compartment</span></td>
                    <td colspan="2"></td>
                </tr>
                <tr>
                    <td align="right" style="border-right: none;">a.</td>
                    <td style="border-left: none;">Lantai <span class="italic-eng">(Floor)</span> ..........................</td>
                    <td align="center">
                        <input type="radio" class="radio-custom" name="kompartemen_toilet[a]" value="baik" required {{ old('kompartemen_toilet.a', 'baik') === 'baik' ? 'checked' : '' }}>
                    </td>
                    <td align="center">
                        <input type="radio" class="radio-custom" name="kompartemen_toilet[a]" value="tidak_baik" {{ old('kompartemen_toilet.a') === 'tidak_baik' ? 'checked' : '' }}>
                    </td>
                </tr>
                <tr>
                    <td align="right" style="border-right: none;">b.</td>
                    <td style="border-left: none;">Alas duduk &amp; penutup <span class="italic-eng">(Seat &amp; Cover)</span> ..........................</td>
                    <td align="center">
                        <input type="radio" class="radio-custom" name="kompartemen_toilet[b]" value="baik" required {{ old('kompartemen_toilet.b', 'baik') === 'baik' ? 'checked' : '' }}>
                    </td>
                    <td align="center">
                        <input type="radio" class="radio-custom" name="kompartemen_toilet[b]" value="tidak_baik" {{ old('kompartemen_toilet.b') === 'tidak_baik' ? 'checked' : '' }}>
                    </td>
                </tr>
                <tr>
                    <td align="right" style="border-right: none;">c.</td>
                    <td style="border-left: none;">Tempat sampah <span class="italic-eng">(Waste Bins)</span> ..........................</td>
                    <td align="center">
                        <input type="radio" class="radio-custom" name="kompartemen_toilet[c]" value="baik" required {{ old('kompartemen_toilet.c', 'baik') === 'baik' ? 'checked' : '' }}>
                    </td>
                    <td align="center">
                        <input type="radio" class="radio-custom" name="kompartemen_toilet[c]" value="tidak_baik" {{ old('kompartemen_toilet.c') === 'tidak_baik' ? 'checked' : '' }}>
                    </td>
                </tr>
                <tr>
                    <td align="right" style="border-right: none;">d.</td>
                    <td style="border-left: none;">Tempat cuci tangan <span class="italic-eng">(Wash hand basin)</span> ..........................</td>
                    <td align="center">
                        <input type="radio" class="radio-custom" name="kompartemen_toilet[d]" value="baik" required {{ old('kompartemen_toilet.d', 'baik') === 'baik' ? 'checked' : '' }}>
                    </td>
                    <td align="center">
                        <input type="radio" class="radio-custom" name="kompartemen_toilet[d]" value="tidak_baik" {{ old('kompartemen_toilet.d') === 'tidak_baik' ? 'checked' : '' }}>
                    </td>
                </tr>
                <tr>
                    <td align="right" style="border-right: none;">e.</td>
                    <td style="border-left: none;">Sabun &amp; tissue <span class="italic-eng">(Soap &amp; tissue)</span> ..........................</td>
                    <td align="center">
                        <input type="radio" class="radio-custom" name="kompartemen_toilet[e]" value="baik" required {{ old('kompartemen_toilet.e', 'baik') === 'baik' ? 'checked' : '' }}>
                    </td>
                    <td align="center">
                        <input type="radio" class="radio-custom" name="kompartemen_toilet[e]" value="tidak_baik" {{ old('kompartemen_toilet.e') === 'tidak_baik' ? 'checked' : '' }}>
                    </td>
                </tr>

                <!-- 3. Galley -->
                <tr class="section-header-row">
                    <td align="center" rowspan="6"><strong>3</strong></td>
                    <td colspan="2"><u><strong>Tempat Penyimpanan Makanan</strong></u><br><span class="italic-eng">Galley</span></td>
                    <td colspan="2"></td>
                </tr>
                <tr>
                    <td align="right" style="border-right: none;">a.</td>
                    <td style="border-left: none;">Lantai <span class="italic-eng">(Floor)</span> ..........................</td>
                    <td align="center">
                        <input type="radio" class="radio-custom" name="galley[a]" value="baik" required {{ old('galley.a', 'baik') === 'baik' ? 'checked' : '' }}>
                    </td>
                    <td align="center">
                        <input type="radio" class="radio-custom" name="galley[a]" value="tidak_baik" {{ old('galley.a') === 'tidak_baik' ? 'checked' : '' }}>
                    </td>
                </tr>
                <tr>
                    <td align="right" style="border-right: none;">b.</td>
                    <td style="border-left: none;">Alas tempat kerja <span class="italic-eng">(Worktop)</span> ..........................</td>
                    <td align="center">
                        <input type="radio" class="radio-custom" name="galley[b]" value="baik" required {{ old('galley.b', 'baik') === 'baik' ? 'checked' : '' }}>
                    </td>
                    <td align="center">
                        <input type="radio" class="radio-custom" name="galley[b]" value="tidak_baik" {{ old('galley.b') === 'tidak_baik' ? 'checked' : '' }}>
                    </td>
                </tr>
                <tr>
                    <td align="right" style="border-right: none;">c.</td>
                    <td style="border-left: none;">Tempat cuci tangan <span class="italic-eng">(Wash hand basin)</span> ..........................</td>
                    <td align="center">
                        <input type="radio" class="radio-custom" name="galley[c]" value="baik" required {{ old('galley.c', 'baik') === 'baik' ? 'checked' : '' }}>
                    </td>
                    <td align="center">
                        <input type="radio" class="radio-custom" name="galley[c]" value="tidak_baik" {{ old('galley.c') === 'tidak_baik' ? 'checked' : '' }}>
                    </td>
                </tr>
                <tr>
                    <td align="right" style="border-right: none;">d.</td>
                    <td style="border-left: none;">Tempat sampah <span class="italic-eng">(Waste bins)</span> ..........................</td>
                    <td align="center">
                        <input type="radio" class="radio-custom" name="galley[d]" value="baik" required {{ old('galley.b', 'baik') === 'baik' ? 'checked' : '' }}>
                    </td>
                    <td align="center">
                        <input type="radio" class="radio-custom" name="galley[d]" value="tidak_baik" {{ old('galley.d') === 'tidak_baik' ? 'checked' : '' }}>
                    </td>
                </tr>
                <tr>
                    <td align="right" style="border-right: none;">e.</td>
                    <td style="border-left: none;">Ruang tempat trolley <span class="italic-eng">(Trolley storage)</span> ..........................</td>
                    <td align="center">
                        <input type="radio" class="radio-custom" name="galley[e]" value="baik" required {{ old('galley.e', 'baik') === 'baik' ? 'checked' : '' }}>
                    </td>
                    <td align="center">
                        <input type="radio" class="radio-custom" name="galley[e]" value="tidak_baik" {{ old('galley.e') === 'tidak_baik' ? 'checked' : '' }}>
                    </td>
                </tr>

                <!-- 4. Dokumen Kesehatan -->
                <tr class="section-header-row">
                    <td align="center" rowspan="5"><strong>4</strong></td>
                    <td colspan="2"><u><strong>Dokumen Kesehatan Pesawat</strong></u><br><span class="italic-eng">Health Document's</span></td>
                    <td colspan="2"></td>
                </tr>
                <tr>
                    <td align="right" style="border-right: none;">a.</td>
                    <td style="border-left: none;">Health Part of the Air Craft General Declaration</td>
                    <td align="center">
                        <input type="radio" class="radio-custom" name="dokumen_kesehatan[a]" value="baik" required {{ old('dokumen_kesehatan.a', 'baik') === 'baik' ? 'checked' : '' }}>
                    </td>
                    <td align="center">
                        <input type="radio" class="radio-custom" name="dokumen_kesehatan[a]" value="tidak_baik" {{ old('dokumen_kesehatan.a') === 'tidak_baik' ? 'checked' : '' }}>
                    </td>
                </tr>
                <tr>
                    <td align="right" style="border-right: none;">b.</td>
                    <td style="border-left: none;">Surat keterangan hapus serangga yang terakhir</td>
                    <td align="center">
                        <input type="radio" class="radio-custom" name="dokumen_kesehatan[b]" value="baik" required {{ old('dokumen_kesehatan.b', 'baik') === 'baik' ? 'checked' : '' }}>
                    </td>
                    <td align="center">
                        <input type="radio" class="radio-custom" name="dokumen_kesehatan[b]" value="tidak_baik" {{ old('dokumen_kesehatan.b') === 'tidak_baik' ? 'checked' : '' }}>
                    </td>
                </tr>
                <tr>
                    <td align="right" style="border-right: none;">c.</td>
                    <td style="border-left: none;">Surat keterangan hapus hama, jika ada diadakan hapus hama</td>
                    <td align="center">
                        <input type="radio" class="radio-custom" name="dokumen_kesehatan[c]" value="baik" required {{ old('dokumen_kesehatan.c', 'baik') === 'baik' ? 'checked' : '' }}>
                    </td>
                    <td align="center">
                        <input type="radio" class="radio-custom" name="dokumen_kesehatan[c]" value="tidak_baik" {{ old('dokumen_kesehatan.c') === 'tidak_baik' ? 'checked' : '' }}>
                    </td>
                </tr>
                <tr>
                    <td align="right" style="border-right: none;">d.</td>
                    <td style="border-left: none;">Buku kesehatan pesawat udara <span class="italic-eng">(hanya pesawat udara yang mengadakan perjalanan dalam negeri)</span></td>
                    <td align="center">
                        <input type="radio" class="radio-custom" name="dokumen_kesehatan[d]" value="baik" required {{ old('dokumen_kesehatan.d', 'baik') === 'baik' ? 'checked' : '' }}>
                    </td>
                    <td align="center">
                        <input type="radio" class="radio-custom" name="dokumen_kesehatan[d]" value="tidak_baik" {{ old('dokumen_kesehatan.d') === 'tidak_baik' ? 'checked' : '' }}>
                    </td>
                </tr>

                <!-- 5. Pembuangan Kotoran Cair -->
                <tr>
                    <td align="center"><strong>5</strong></td>
                    <td colspan="2">
                        <u><strong>Pembuangan Kotoran Cair</strong></u><br><span class="italic-eng">Excreta Disposal</span><br>
                        : Ditangani petugas khusus <br><span class="italic-eng">(Handle by special personal)</span>...........................................
                    </td>
                    <td align="center">
                        <label style="cursor: pointer; display: block;">
                            <input type="radio" class="radio-custom" name="pembuangan_kotoran" value="ya" required {{ old('pembuangan_kotoran', 'ya') === 'ya' ? 'checked' : '' }}><br>
                            <strong>Ya <span class="italic-eng">(Yes)</span></strong>
                        </label>
                    </td>
                    <td align="center">
                        <label style="cursor: pointer; display: block;">
                            <input type="radio" class="radio-custom" name="pembuangan_kotoran" value="tidak" {{ old('pembuangan_kotoran') === 'tidak' ? 'checked' : '' }}><br>
                            <strong>Tidak <span class="italic-eng">(No)</span></strong>
                        </label>
                    </td>
                </tr>

                <!-- 6. Penanganan Air -->
                <tr>
                    <td align="center"><strong>6</strong></td>
                    <td colspan="2">
                        <u><strong>Penanganan Penyediaan Air</strong></u><br><span class="italic-eng">Aircraft Water Supply</span><br>
                        : a. Ditangani petugas khusus <span class="italic-eng">(Handle by special personal)</span>...........................................<br><br>
                        &nbsp;&nbsp;b. Kualitas Fisik <span class="italic-eng">(Physical Qualities)</span>:<br>
                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        <label style="margin-right: 15px; cursor: pointer;">
                            <input type="checkbox" class="checkbox-custom" name="penanganan_air[bau]" value="1" {{ old('penanganan_air.bau') ? 'checked' : '' }}> Bau <span class="italic-eng">(Smell)</span>
                        </label>
                        <label style="margin-right: 15px; cursor: pointer;">
                            <input type="checkbox" class="checkbox-custom" name="penanganan_air[warna]" value="1" {{ old('penanganan_air.warna') ? 'checked' : '' }}> Warna <span class="italic-eng">(Color)</span>
                        </label>
                        <label style="cursor: pointer;">
                            <input type="checkbox" class="checkbox-custom" name="penanganan_air[rasa]" value="1" {{ old('penanganan_air.rasa') ? 'checked' : '' }}> Rasa <span class="italic-eng">(Flavor)</span>
                        </label>
                    </td>
                    <td align="center">
                        <label style="cursor: pointer; display: block;">
                            <input type="radio" class="radio-custom" name="penanganan_air[petugas]" value="ya" {{ old('penanganan_air.petugas', 'ya') === 'ya' ? 'checked' : '' }}><br>
                            <strong>Ya <span class="italic-eng">(Yes)</span></strong>
                        </label>
                    </td>
                    <td align="center">
                        <label style="cursor: pointer; display: block;">
                            <input type="radio" class="radio-custom" name="penanganan_air[petugas]" value="tidak" {{ old('penanganan_air.petugas') === 'tidak' ? 'checked' : '' }}><br>
                            <strong>Tidak <span class="italic-eng">(No)</span></strong>
                        </label>
                    </td>
                </tr>

                <!-- 7. Makanan -->
                <tr>
                    <td align="center"><strong>7</strong></td>
                    <td colspan="4">
                        <u><strong>Keadaan makanan secara fisik*</strong></u><br><span class="italic-eng">Physical Appearance Of Food</span><br>
                        &nbsp;&nbsp;: a. <label style="cursor: pointer; margin-right: 20px;"><input type="radio" class="radio-custom" name="keadaan_makanan[kondisi]" value="baik" {{ old('keadaan_makanan.kondisi', 'baik') === 'baik' ? 'checked' : '' }}> <strong>Baik <span class="italic-eng">(Good)</span></strong></label>
                        &nbsp;&nbsp;b. <label style="cursor: pointer; margin-right: 15px;"><input type="radio" class="radio-custom" name="keadaan_makanan[kondisi]" value="tidak_baik" {{ old('keadaan_makanan.kondisi') === 'tidak_baik' ? 'checked' : '' }}> <strong>Tidak Baik <span class="italic-eng">(Poor)</span></strong></label>
                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        <label style="margin-right: 12px; cursor: pointer;"><input type="checkbox" class="checkbox-custom" name="keadaan_makanan[bau]" value="1" {{ old('keadaan_makanan.bau') ? 'checked' : '' }}> Bau <span class="italic-eng">(Smell)</span></label>
                        <label style="margin-right: 12px; cursor: pointer;"><input type="checkbox" class="checkbox-custom" name="keadaan_makanan[lendir]" value="1" {{ old('keadaan_makanan.lendir') ? 'checked' : '' }}> Lendir <span class="italic-eng">(Mucus)</span></label>
                        <label style="cursor: pointer;"><input type="checkbox" class="checkbox-custom" name="keadaan_makanan[jamur]" value="1" {{ old('keadaan_makanan.jamur') ? 'checked' : '' }}> Jamur <span class="italic-eng">(Fungi)</span></label>
                        <br><br>
                        &nbsp;&nbsp;c. Jumlah Jenis makanan: 
                        <input type="text" name="keadaan_makanan[jumlah]" class="underline-input" style="display: inline-block; width: 45%; max-width: 320px;" placeholder="Contoh: 120 Paket Hot Meal" value="{{ old('keadaan_makanan.jumlah') }}">
                        <br>
                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Sebutkan jenisnya : 
                        <input type="text" name="keadaan_makanan[jenis]" class="underline-input" style="display: inline-block; width: 45%; max-width: 320px;" placeholder="Contoh: Nasi Rendang, Snack Box, Roti" value="{{ old('keadaan_makanan.jenis') }}">
                    </td>
                </tr>

                <!-- 8. Binatang Pengganggu -->
                <tr>
                    <td align="center"><strong>8</strong></td>
                    <td colspan="4">
                        <u><strong>Binatang Pengganggu*</strong></u><br><span class="italic-eng">Pest Infestation</span><br>
                        &nbsp;&nbsp;: a. <label style="margin-right: 15px; cursor: pointer;"><input type="checkbox" class="checkbox-custom" name="binatang_pengganggu[nyamuk]" value="1" {{ old('binatang_pengganggu.nyamuk') ? 'checked' : '' }}> Nyamuk <span class="italic-eng">(Mosquito)</span></label>
                        &nbsp;&nbsp;b. <label style="margin-right: 15px; cursor: pointer;"><input type="checkbox" class="checkbox-custom" name="binatang_pengganggu[lalat]" value="1" {{ old('binatang_pengganggu.lalat') ? 'checked' : '' }}> Lalat <span class="italic-eng">(Flies)</span></label>
                        &nbsp;&nbsp;c. <label style="margin-right: 15px; cursor: pointer;"><input type="checkbox" class="checkbox-custom" name="binatang_pengganggu[kecoa]" value="1" {{ old('binatang_pengganggu.kecoa') ? 'checked' : '' }}> Kecoa <span class="italic-eng">(Cockroach)</span></label>
                        &nbsp;&nbsp;d. <label style="margin-right: 15px; cursor: pointer;"><input type="checkbox" class="checkbox-custom" name="binatang_pengganggu[tikus]" value="1" {{ old('binatang_pengganggu.tikus') ? 'checked' : '' }}> Tikus <span class="italic-eng">(Rodent)</span></label>
                        <br>
                        &nbsp;&nbsp;e. Lain-lain <span class="italic-eng">(Others)</span>: 
                        <input type="text" name="binatang_pengganggu[lain2]" class="underline-input" style="display: inline-block; width: 40%; max-width: 250px;" placeholder="Nihil / sebutkan jika ada" value="{{ old('binatang_pengganggu.lain2', 'Nihil') }}">
                    </td>
                </tr>

                <!-- 9. Surat Sanitasi -->
                <tr>
                    <td align="center"><strong>9</strong></td>
                    <td colspan="4">
                        <strong>Surat Keterangan Sanitasi Pesawat</strong><br><span class="italic-eng">Certificate Aircraft Sanitation</span>
                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        <label style="cursor: pointer; margin-right: 25px;">
                            <input type="radio" class="radio-custom" name="surat_sanitasi" value="ada" required {{ old('surat_sanitasi', 'ada') === 'ada' ? 'checked' : '' }}>
                            <strong>a. Ada / <span class="italic-eng">There are</span></strong>
                        </label>
                        <label style="cursor: pointer;">
                            <input type="radio" class="radio-custom" name="surat_sanitasi" value="tidak_ada" {{ old('surat_sanitasi') === 'tidak_ada' ? 'checked' : '' }}>
                            <strong>b. Tidak Ada / <span class="italic-eng">Not Found</span></strong>
                        </label>
                    </td>
                </tr>

                <!-- 10. Kesimpulan & Tindakan -->
                <tr>
                    <td align="center" style="background-color: #f8fafc;"><strong>10</strong></td>
                    <td colspan="4" style="background-color: #f8fafc;">
                        <div style="margin-bottom: 10px;">
                            <strong>Kesimpulan / <span class="italic-eng">Conclusion</span> :</strong> &nbsp;&nbsp;
                            <label style="cursor: pointer; margin-right: 20px;">
                                <input type="radio" class="radio-custom" name="kesimpulan" value="layak" required {{ old('kesimpulan', 'layak') === 'layak' ? 'checked' : '' }}>
                                <strong style="color: #047857;">Layak / Qualified</strong>
                            </label>
                            <label style="cursor: pointer;">
                                <input type="radio" class="radio-custom" name="kesimpulan" value="tidak_layak" {{ old('kesimpulan') === 'tidak_layak' ? 'checked' : '' }}>
                                <strong style="color: #be123c;">Tidak Layak / Unqualified</strong>
                            </label>
                            &nbsp; diterbitkan "Sertifikat Bebas Serangga"<br>
                            <span class="italic-eng" style="padding-left: 20px;">Qualified / Unqualified "Disinsection Exemption Certificate" issued.</span>
                        </div>

                        <hr style="border: 0; border-top: 1px dashed #cbd5e1; margin: 12px 0;">

                        <div>
                            <u><strong>Rekomendasi dan Tindakan* :</strong></u> &nbsp;&nbsp;&nbsp;
                            Perlu / tidak perlu tindakan penyehatan :<br>
                            <div style="margin-top: 6px; display: flex; flex-wrap: wrap; gap: 20px;">
                                <label style="cursor: pointer;"><input type="radio" class="radio-custom" name="tindakan" value="disinseksi" {{ old('tindakan') === 'disinseksi' ? 'checked' : '' }}> Disinseksi</label>
                                <label style="cursor: pointer;"><input type="radio" class="radio-custom" name="tindakan" value="fumigasi" {{ old('tindakan') === 'fumigasi' ? 'checked' : '' }}> Fumigasi</label>
                                <label style="cursor: pointer;"><input type="radio" class="radio-custom" name="tindakan" value="disinfeksi" {{ old('tindakan') === 'disinfeksi' ? 'checked' : '' }}> Disinfeksi</label>
                                <label style="cursor: pointer;"><input type="radio" class="radio-custom" name="tindakan" value="general_cleaning" {{ old('tindakan') === 'general_cleaning' ? 'checked' : '' }}> General Cleaning</label>
                            </div>
                        </div>

                        <div style="margin-top: 10px;">
                            <label style="font-size: 12px; font-weight: 600; color: #475569;">Catatan / Rekomendasi Tambahan Petugas:</label>
                            <input type="text" name="catatan_rekomendasi" class="underline-input" placeholder="Opsional: Tuliskan rekomendasi penyehatan atau catatan inspeksi" value="{{ old('catatan_rekomendasi') }}">
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>

        <p style="font-size: 12px; color: #64748b; margin-bottom: 25px;">
            * &nbsp;&nbsp; Isilah tanda &#10003; Untuk jawaban dimaksud &nbsp;&bull;&nbsp; <span class="italic-eng">Please tick appropriate option</span>
        </p>

        <!-- 11. Geotagging & Documentation Photo Upload Section -->
        <div class="geotag-section" id="geotagSection">
            <div class="geotag-header">
                <div>
                    <div class="geotag-title">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#0284c7" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14.5 4h-5L7 7H4a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-3l-2.5-3z"/><circle cx="12" cy="13" r="3"/></svg>
                        <span>Dokumentasi Foto Lapangan &amp; Geotagging GPS</span>
                    </div>
                    <div style="font-size: 12px; color: #64748b; margin-top: 2px;">
                        Bukti foto kelaikan sanitasi pesawat (kabin, galley, lavatory, penanganan limbah). Koordinat GPS terekam otomatis.
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
                            <span>Lat: <strong id="lblLatitude">-6.903890</strong> &bull; Lng: <strong id="lblLongitude">107.618610</strong></span>
                            <span style="font-size: 11px; color: #0369a1; background: #e0f2fe; padding: 2px 6px; border-radius: 4px;" id="lblAccuracy">Akurasi: &plusmn; 10m</span>
                        </div>

                        <div style="margin-top: 8px;">
                            <label style="font-size: 11.5px; font-weight: 600; color: #475569; display: block; margin-bottom: 3px;">Keterangan / Lokasi Bandara:</label>
                            <input type="text" name="lokasi_geotag" id="input_lokasi_geotag" class="underline-input" style="font-size: 12px; padding: 4px 6px;" placeholder="Contoh: Apron Bandara Husein Sastranegara (BDO) / Kertajati (KJT)" value="{{ old('lokasi_geotag', 'Apron Bandara Internasional Husein Sastranegara (BDO)') }}">
                        </div>

                        <div style="margin-top: 10px; display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
                            <span style="font-size: 11px; color: #64748b;">Pilihan Cepat:</span>
                            <button type="button" class="preset-badge" onclick="setPresetLocation(-6.90389, 107.61861, 'Bandara Husein Sastranegara (BDO) - Bandung')">Bandara BDO</button>
                            <button type="button" class="preset-badge" onclick="setPresetLocation(-6.65750, 108.19667, 'Bandara Internasional Kertajati (KJT) - Majalengka')">Bandara KJT</button>
                            <button type="button" class="preset-badge" onclick="setPresetLocation(-6.12750, 106.65360, 'Bandara Internasional Soekarno-Hatta (CGK) - Cengkareng')">Bandara CGK</button>
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
            <input type="hidden" name="latitude" id="input_latitude" value="{{ old('latitude', '-6.903890') }}">
            <input type="hidden" name="longitude" id="input_longitude" value="{{ old('longitude', '107.618610') }}">
            
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
                <div style="font-weight: 700; color: #1e293b; font-size: 13px;">Seret &amp; letakkan foto dokumentasi di sini</div>
                <div style="font-size: 11.5px; color: #64748b; margin-top: 4px;">Mendukung format JPG, PNG, WEBP (Maksimal 12MB per berkas foto).</div>
            </div>

            <!-- Uploaded Photos Gallery Preview -->
            <div class="photo-gallery-grid" id="photoGalleryGrid">
                <!-- Populated dynamically via JavaScript -->
            </div>

            <div id="emptyPhotoNotice" style="text-align: center; padding: 25px 15px; color: #94a3b8; font-size: 12.5px; background: #fafafa; border: 1px dashed #e2e8f0; border-radius: 12px; margin-top: 15px;">
                Belum ada foto dokumentasi yang dilampirkan. Klik tombol <strong>Ambil Foto Kamera</strong> atau <strong>Pilih Berkas Foto</strong> di atas untuk menambahkan bukti dokumentasi inspeksi kelaikan.
            </div>
        </div>

        <!-- Signature Digital Pads Section -->
        <div class="signature-wrapper">
            <!-- Petugas Kabin -->
            <div class="sig-card">
                <div style="font-weight: 700; font-size: 14px;"><u>Petugas Kabin</u></div>
                <div class="italic-eng" style="margin-bottom: 10px;">Cabin Crew</div>
                
                <canvas id="pad_kabin" class="sig-canvas"></canvas>
                <br>
                <button type="button" class="btn-clear" id="clear_kabin">Bersihkan Tanda Tangan</button>
                <br><br>
                ( <input type="text" name="petugas_kabin_nama" class="underline-input" style="width: 70%; text-align: center;" placeholder="Ketik Nama Lengkap" required value="{{ old('petugas_kabin_nama') }}"> )<br>
                <span class="italic-eng">Name &amp; signature</span>
                <input type="hidden" name="petugas_kabin_ttd" id="ttd_kabin" value="{{ old('petugas_kabin_ttd') }}">
            </div>

            <!-- Petugas BKK -->
            <div class="sig-card">
                <div style="font-weight: 700; font-size: 14px;"><u>Petugas BKK Bandung</u></div>
                <div class="italic-eng" style="margin-bottom: 10px;">Health Quarantine Officer</div>
                
                <canvas id="pad_bkk" class="sig-canvas"></canvas>
                <br>
                <button type="button" class="btn-clear" id="clear_bkk">Bersihkan Tanda Tangan</button>
                <br><br>
                ( <input type="text" name="petugas_bkk_nama" class="underline-input" style="width: 70%; text-align: center;" placeholder="Ketik Nama Lengkap" required value="{{ old('petugas_bkk_nama') }}"> )<br>
                <span class="italic-eng">Name &amp; signature</span>
                <input type="hidden" name="petugas_bkk_ttd" id="ttd_bkk" value="{{ old('petugas_bkk_ttd') }}">
            </div>
        </div>

        <div style="text-align: center; margin-top: 40px;">
            <button type="submit" class="btn-submit" id="btnSubmit">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                <span>Submit Formulir Inspeksi Pesawat</span>
            </button>
            <div style="margin-top: 12px; font-size: 12px; color: #64748b;">
                Data akan tersimpan secara terenkripsi dan nomor registrasi pelacakan resmi akan diterbitkan.
            </div>
        </div>
    </form>
</div>

<!-- Live Camera WebRTC Viewfinder Modal -->
<div class="camera-modal-overlay" id="liveCameraModal">
    <div class="camera-modal-card">
        <div class="camera-modal-header">
            <div style="font-weight: 700; font-size: 14px; display: flex; align-items: center; gap: 8px;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#38bdf8" stroke-width="2.5"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>
                <span>Kamera Live Inspeksi &bull; Geotagging GPS</span>
            </div>
            <button type="button" class="btn-clear" onclick="closeLiveCamera()" style="margin: 0; background: #334155; color: white; border: none; padding: 5px 12px; border-radius: 8px; cursor: pointer;">&times; Tutup</button>
        </div>

        <div class="camera-viewfinder-wrapper">
            <video id="cameraVideo" autoplay playsinline muted></video>
            <div class="camera-live-overlay">
                <div>✈️ BKK BANDUNG &bull; SANITASI PESAWAT</div>
                <div id="camLiveCoords">📍 Koordinat: -6.903890, 107.618610</div>
                <div id="camLiveTime">🕒 Memuat waktu...</div>
            </div>
        </div>

        <div class="camera-controls-bar">
            <button type="button" class="btn-cam-switch" id="btnSwitchCamera" title="Ganti Kamera Depan / Belakang">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21.5 2v6h-6M21.34 15.57a10 10 0 1 1-.57-8.38l5.67-5.67"/></svg>
                <span>Putar Kamera</span>
            </button>

            <button type="button" class="btn-shutter" id="btnShutter" title="Jepret Foto Sekarang">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#0284c7" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="3" fill="#0284c7"/></svg>
            </button>

            <button type="button" class="btn-cam-close" onclick="closeLiveCamera()">
                <span>Selesai</span>
            </button>
        </div>
    </div>
</div>
<canvas id="cameraCanvas" style="display:none;"></canvas>

<!-- Signature Pad Library -->
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
<!-- Leaflet Map Library -->
<script src="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
    /* =========================================================
     * 1. Signature Pad Logic
     * ========================================================= */
    function resizeCanvas(canvas, pad) {
        var ratio = Math.max(window.devicePixelRatio || 1, 1);
        canvas.width = canvas.offsetWidth * ratio;
        canvas.height = canvas.offsetHeight * ratio;
        canvas.getContext("2d").scale(ratio, ratio);
        if (pad) pad.clear();
    }

    var canvasKabin = document.getElementById('pad_kabin');
    var canvasBkk = document.getElementById('pad_bkk');
    var padKabin = null;
    var padBkk = null;

    if (canvasKabin) {
        padKabin = new SignaturePad(canvasKabin, { backgroundColor: 'rgb(255, 255, 255)', penColor: 'rgb(15, 23, 42)' });
        resizeCanvas(canvasKabin, padKabin);
    }
    if (canvasBkk) {
        padBkk = new SignaturePad(canvasBkk, { backgroundColor: 'rgb(255, 255, 255)', penColor: 'rgb(15, 23, 42)' });
        resizeCanvas(canvasBkk, padBkk);
    }

    window.addEventListener("resize", function() {
        if (canvasKabin && padKabin && padKabin.isEmpty()) resizeCanvas(canvasKabin, padKabin);
        if (canvasBkk && padBkk && padBkk.isEmpty()) resizeCanvas(canvasBkk, padBkk);
    });

    document.getElementById('clear_kabin').addEventListener('click', function() {
        if (padKabin) padKabin.clear();
        document.getElementById('ttd_kabin').value = '';
    });

    document.getElementById('clear_bkk').addEventListener('click', function() {
        if (padBkk) padBkk.clear();
        document.getElementById('ttd_bkk').value = '';
    });

    /* =========================================================
     * 2. GPS Geolocation & Interactive Map
     * ========================================================= */
    var currentLatitude = parseFloat(document.getElementById('input_latitude').value) || -6.903890;
    var currentLongitude = parseFloat(document.getElementById('input_longitude').value) || 107.618610;
    var currentAccuracy = 10;
    var mapInstance = null;
    var markerInstance = null;

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
                markerInstance.bindPopup('<b>Lokasi Inspeksi Pesawat</b><br>BKK Bandung').openPopup();
                L.control.zoom({ position: 'bottomright' }).addTo(mapInstance);
            } else {
                mapInstance.setView([lat, lng], 15);
                if (markerInstance) {
                    markerInstance.setLatLng([lat, lng]);
                }
            }
        } catch (err) {
            console.warn('Map initialization failed:', err);
        }
    }

    function updateGpsUI(lat, lng, acc) {
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
                    // Keep existing fallback coordinates (Bandara Husein Sastranegara)
                    updateGpsUI(currentLatitude, currentLongitude, 25);

                    if (showNotice) {
                        alert('Izin akses lokasi perangkat belum aktif atau sinyal GPS lemah. Koordinat default bandara digunakan. Anda dapat memilih preset bandara di bawah.');
                    }
                },
                { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
            );
        } else {
            updateGpsUI(currentLatitude, currentLongitude, 50);
        }
    }

    function setPresetLocation(lat, lng, label) {
        document.getElementById('input_lokasi_geotag').value = label;
        updateGpsUI(lat, lng, 5);
    }

    document.getElementById('btnRefreshGps').addEventListener('click', function() {
        detectGeolocation(true);
    });

    /* =========================================================
     * 3. Pure JS EXIF GPS Parser (Client-Side)
     * ========================================================= */
    function extractExifGps(buffer) {
        try {
            var dv = new DataView(buffer);
            if (dv.getUint16(0, false) !== 0xFFD8) return null; // Not JPEG

            var length = dv.byteLength;
            var offset = 2;

            while (offset < length) {
                var marker = dv.getUint16(offset, false);
                offset += 2;

                if (marker === 0xFFE1) { // APP1 Exif marker
                    var app1Length = dv.getUint16(offset, false);
                    offset += 2;
                    var exifHeader = dv.getUint32(offset, false);
                    if (exifHeader !== 0x45786966) return null; // "Exif"

                    var tiffStart = offset + 6;
                    var littleEndian = dv.getUint16(tiffStart, false) === 0x4949;
                    var firstIfdOffset = dv.getUint32(tiffStart + 4, littleEndian);

                    var ifdStart = tiffStart + firstIfdOffset;
                    var numEntries = dv.getUint16(ifdStart, littleEndian);
                    var gpsOffset = null;

                    for (var i = 0; i < numEntries; i++) {
                        var entryOffset = ifdStart + 2 + (i * 12);
                        var tag = dv.getUint16(entryOffset, littleEndian);
                        if (tag === 0x8825) { // GPS Info IFD
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
     * 3.5. WebRTC Live Camera Controller (Direct Camera Viewfinder)
     * ========================================================= */
    var cameraStream = null;
    var currentFacingMode = 'environment';
    var liveClockInterval = null;

    function openLiveCamera() {
        var modal = document.getElementById('liveCameraModal');
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            alert('Browser Anda tidak mendukung akses live kamera WebRTC. Membuka dialog pemilih berkas...');
            document.getElementById('cameraInput').click();
            return;
        }

        modal.style.display = 'flex';
        updateLiveCameraOverlayClock();
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

        // Draw camera frame
        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

        // Render official geotag banner at the bottom of captured photo
        var bannerHeight = Math.max(68, Math.round(canvas.height * 0.13));
        ctx.fillStyle = 'rgba(15, 23, 42, 0.85)';
        ctx.fillRect(0, canvas.height - bannerHeight, canvas.width, bannerHeight);

        var fontSize = Math.max(15, Math.round(canvas.height * 0.026));
        ctx.font = 'bold ' + fontSize + 'px sans-serif';
        ctx.fillStyle = '#ffffff';

        var line1 = '✈️ BKK BANDUNG • AIRCRAFT SANITATION INSPECTION';
        var line2 = '📍 Lat: ' + currentLatitude.toFixed(6) + ', Lng: ' + currentLongitude.toFixed(6) + ' (±' + currentAccuracy + 'm)';
        var regNo = document.querySelector('input[name="tanda_kebangsaan"]')?.value || 'PESAWAT';
        var line3 = '🕒 ' + new Date().toLocaleString('id-ID') + ' • Reg: ' + regNo;

        ctx.fillText(line1, 24, canvas.height - bannerHeight + (fontSize * 1.3));
        ctx.font = 'bold ' + (fontSize * 0.85) + 'px monospace';
        ctx.fillStyle = '#38bdf8';
        ctx.fillText(line2, 24, canvas.height - bannerHeight + (fontSize * 2.6));
        ctx.font = (fontSize * 0.8) + 'px monospace';
        ctx.fillStyle = '#cbd5e1';
        ctx.fillText(line3, 24, canvas.height - bannerHeight + (fontSize * 3.8));

        canvas.toBlob(function(blob) {
            if (!blob) return;
            var fileName = 'live_snap_' + Date.now() + '.jpg';
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
            'Kabin Penumpang',
            'Kompartemen Toilet',
            'Galley / Dapur Pesawat',
            'Penanganan Air & Limbah',
            'Binatang Pengganggu / Sanitasi',
            'Dokumentasi Umum Lapangan'
        ];

        Array.from(files).forEach(function(file) {
            if (!file.type.match(/^image\//)) {
                alert('File ' + file.name + ' bukan gambar yang didukung.');
                return;
            }

            var currentIndex = photoTransfer.items.length;
            photoTransfer.items.add(file);

            // Read EXIF and preview
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

        card.innerHTML = `
            <button type="button" class="btn-delete-card" title="Hapus foto ini">&times;</button>
            <div class="photo-preview-wrap">
                <img src="${dataUrl}" alt="${file.name}">
                <div class="geotag-stamp-badge">
                    <div>✈️ BKK BANDUNG &bull; SANITASI PESAWAT</div>
                    <div>📍 ${lat.toFixed(6)}, ${lng.toFixed(6)} (&plusmn;${acc}m)</div>
                    <div>🕒 ${timeStr}</div>
                </div>
            </div>
            <div class="photo-details-body">
                <div style="font-weight: 700; font-size: 11.5px; color: #1e293b; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                    ${file.name}
                </div>
                <div>
                    <label style="font-size: 10.5px; color: #64748b; font-weight: 600; display: block; margin-bottom: 2px;">Kategori Area:</label>
                    <select name="foto_kategori[]" class="photo-category-input">
                        <option value="Kabin Penumpang" ${defaultCat === 'Kabin Penumpang' ? 'selected' : ''}>Kabin Penumpang</option>
                        <option value="Kompartemen Toilet" ${defaultCat === 'Kompartemen Toilet' ? 'selected' : ''}>Kompartemen Toilet</option>
                        <option value="Galley / Dapur Pesawat" ${defaultCat === 'Galley / Dapur Pesawat' ? 'selected' : ''}>Galley / Dapur Pesawat</option>
                        <option value="Penanganan Air & Limbah" ${defaultCat === 'Penanganan Air & Limbah' ? 'selected' : ''}>Penanganan Air & Limbah</option>
                        <option value="Binatang Pengganggu / Vektor" ${defaultCat === 'Binatang Pengganggu / Sanitasi' ? 'selected' : ''}>Binatang Pengganggu / Vektor</option>
                        <option value="Dokumen Kesehatan" ${defaultCat === 'Dokumen Kesehatan' ? 'selected' : ''}>Dokumen Kesehatan</option>
                        <option value="Dokumentasi Umum Lapangan" ${defaultCat === 'Dokumentasi Umum Lapangan' ? 'selected' : ''}>Dokumentasi Umum Lapangan</option>
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

        // Re-render gallery from scratch to keep indexes matched
        photoGalleryGrid.innerHTML = '';
        var remainingFiles = Array.from(photoTransfer.files);
        photoTransfer = new DataTransfer(); // will refill via handleSelectedFiles
        handleSelectedFiles(remainingFiles);
        updateEmptyNotice();
    }

    /* Initialize Map & GPS on Document Ready */
    document.addEventListener("DOMContentLoaded", function() {
        detectGeolocation(false);
        initMiniMap(currentLatitude, currentLongitude);
    });

    /* Final Submit Hook */
    document.getElementById('aircraftForm').addEventListener('submit', function(e) {
        if (padKabin && !padKabin.isEmpty()) {
            document.getElementById('ttd_kabin').value = padKabin.toDataURL();
        }
        if (padBkk && !padBkk.isEmpty()) {
            document.getElementById('ttd_bkk').value = padBkk.toDataURL();
        }
        // Ensure files are synced to real input
        realPhotoInput.files = photoTransfer.files;
    });
</script>

</body>
</html>
