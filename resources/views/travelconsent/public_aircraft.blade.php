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
    </style>
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

    <form action="{{ route('travelconsent.submit_aircraft') }}" method="POST" id="aircraftForm">
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

<!-- Signature Pad Library -->
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
<script>
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

    document.getElementById('aircraftForm').addEventListener('submit', function(e) {
        if (padKabin && !padKabin.isEmpty()) {
            document.getElementById('ttd_kabin').value = padKabin.toDataURL();
        }
        if (padBkk && !padBkk.isEmpty()) {
            document.getElementById('ttd_bkk').value = padBkk.toDataURL();
        }
    });
</script>

</body>
</html>
