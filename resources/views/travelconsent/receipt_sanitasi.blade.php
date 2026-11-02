<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sertifikat Sanitasi Kapal &bull; {{ $inspection->registration_no }}</title>
    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,600,700&display=swap">
    <!-- Bootstrap 4 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        /* Base / Screen Styles */
        body { 
            background-color: #f4f6f9; 
            font-family: "Source Sans Pro", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            font-size: 13.5px; 
            color: #212529;
        }
        .main-container {
            max-width: 1140px;
            margin: 20px auto;
            background: #ffffff;
            border: 1px solid #dee2e6;
            border-radius: 12px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.06);
            padding: 30px;
        }
        .kop-surat { text-align: center; margin-bottom: 20px; }
        .kop-surat img { max-height: 120px; width: auto; max-width: 100%; }

        .top-action-bar {
            max-width: 1140px;
            margin: 0 auto 16px auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
        }

        .table-print { 
            width: 100%; 
            border-collapse: collapse; 
            margin-bottom: 16px; 
            background: #fff; 
        }
        .table-print th, .table-print td { 
            border: 1px solid #000; 
            padding: 6px 8px; 
            vertical-align: middle; 
            font-size: 12px;
        }
        .table-print th { 
            background-color: #e9ecef !important; 
            -webkit-print-color-adjust: exact; 
            print-color-adjust: exact;
            text-align: center; 
            font-weight: 700; 
        }
        .table-borderless td { border: none !important; padding: 4px; }

        /* Print Mode Styles (Exact A4 Landscape Format) */
        @media print {
            @page { size: A4 landscape; margin: 12mm 15mm; }
            body { background-color: #fff !important; color: #000; font-size: 11px; padding: 0 !important; }
            .main-container { 
                max-width: 100% !important; 
                margin: 0 !important; 
                padding: 0 !important; 
                border: none !important; 
                box-shadow: none !important; 
            }
            .d-print-none, .top-action-bar { display: none !important; }
            .print-only { display: block !important; }
            
            .kop-surat { margin-top: 0; margin-bottom: 12px; }
            .kop-surat img { width: 100% !important; max-width: 100% !important; max-height: none !important; }
            h4 { font-size: 16px; margin: 5px 0; }
            p { margin: 2px 0; }
            .table-print th, .table-print td { padding: 4px 6px; font-size: 10.5px; border: 1px solid #000 !important; }
            .table-print th { background-color: #e9ecef !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
        
        /* Responsive Mobile Styles for Document View */
        @media screen and (max-width: 767.98px) {
            body { font-size: 12px; }
            .main-container { padding: 15px; margin: 10px; border-radius: 8px; }
            .kop-surat { margin-top: 8px !important; margin-bottom: 10px !important; }
            .kop-surat img { max-height: 70px; }
            .kop-surat h4 { font-size: 14px; margin-top: 6px; margin-bottom: 2px; }
            .table-info-umum, .table-info-umum tbody { display: block; width: 100%; border: none; }
            .table-info-umum tr { display: flex; flex-wrap: wrap; width: 100%; }
            .table-info-umum td {
                box-sizing: border-box;
                font-size: 11.5px;
                padding: 4px 6px;
                border: 1px solid #000;
                margin-top: -1px;
                word-break: break-word;
            }
            .table-info-umum td:nth-child(odd) {
                width: 42% !important;
                background-color: #f8f9fa;
            }
            .table-info-umum td:nth-child(even) {
                width: 58% !important;
                margin-left: -1px;
            }
            .table-area-scroll {
                width: 100%;
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
                margin-bottom: 12px;
                border: 1px solid #000;
            }
            .table-area-print {
                min-width: 780px;
                margin-bottom: 0;
                font-size: 11px;
                border: none;
            }
            .table-area-print th, .table-area-print td {
                padding: 4px 5px;
            }
            .table-ket-ttd, .table-ket-ttd tbody, .table-ket-ttd tr {
                display: block;
                width: 100%;
            }
            .table-ket-ttd td:nth-child(1) {
                display: block;
                width: 100% !important;
                padding-bottom: 2px !important;
            }
            .table-ket-ttd td:nth-child(2) {
                display: block;
                width: 100% !important;
                padding-top: 0 !important;
                padding-bottom: 10px !important;
                margin-bottom: 10px;
                border-bottom: 1px dashed #ccc !important;
            }
            .table-ket-ttd td:nth-child(3) {
                display: block;
                width: 100% !important;
                text-align: center;
            }
        }
    </style>
</head>
<body class="hold-transition layout-top-nav py-3">

    <!-- Top Action Bar (Floating Controls on Screen) -->
    <div class="top-action-bar d-print-none px-3">
        <div class="d-flex align-items-center">
            <a href="{{ route('travelconsent.home') }}" class="btn btn-outline-secondary btn-sm mr-2" title="Kembali ke Portal">
                <i class="fas fa-arrow-left mr-1"></i> Portal
            </a>
            <span class="badge badge-success text-uppercase py-1.5 px-2.5 font-weight-bold" style="font-size: 11px;">
                {{ $inspection->registration_no }}
            </span>
        </div>

        <div class="d-flex align-items-center flex-wrap" style="gap: 8px;">
            <div class="input-group input-group-sm" style="width: auto;">
                <div class="input-group-prepend">
                    <span class="input-group-text bg-white"><i class="fas fa-link text-muted"></i></span>
                </div>
                <input 
                    type="text" 
                    id="pageLinkInput" 
                    readonly 
                    value="{{ url()->current() }}" 
                    class="form-control form-control-sm bg-white" 
                    style="width: 220px; font-family: monospace; font-size: 11px;"
                    onclick="this.select()"
                >
                <div class="input-group-append">
                    <button type="button" id="btnPageCopy" class="btn btn-dark btn-sm font-weight-bold" onclick="copyPageLink()">
                        Salin Link
                    </button>
                </div>
            </div>

            <button type="button" class="btn btn-primary btn-sm font-weight-bold shadow-sm" onclick="window.print()">
                <i class="fas fa-print mr-1"></i> Cetak / Simpan PDF
            </button>
        </div>
    </div>

    <!-- MAIN PRINTABLE DOCUMENT CONTAINER -->
    <div class="main-container">
        
        <!-- HEADER / KOP SURAT RESMI -->
        <div class="kop-surat">
            <img 
                src="{{ asset('storage/images/Kop_BKK_bandung.png') }}" 
                alt="Balai Kekarantinaan Kesehatan Kelas I Bandung" 
                onerror="this.onerror=null;this.src='{{ asset('images/Kop_BKK_bandung.png') }}';"
            >
            <h4 class="font-weight-bold mt-2 text-uppercase mb-1" style="letter-spacing: 0.5px;">
                SERTIFIKAT {{ $inspection->sertifikat_mencatat === 'Pembebasan' ? 'PEMBEBASAN' : 'PENGENDALIAN' }} SANITASI KAPAL
            </h4>
            <p class="text-muted text-uppercase mb-0 font-weight-bold" style="font-size: 11px; letter-spacing: 0.5px;">
                {{ $inspection->sertifikat_mencatat === 'Pembebasan' ? 'SHIP SANITATION CONTROL EXEMPTION CERTIFICATE (SSCEC)' : 'SHIP SANITATION CONTROL CERTIFICATE (SSCC)' }}
            </p>
        </div>

        <!-- ============================================== -->
        <!-- INFORMASI UMUM KAPAL & PELABUHAN              -->
        <!-- ============================================== -->
        <table class="table-print table-info-umum">
            <tr>
                <td width="15%"><b>Nama Pelabuhan</b></td>
                <td width="20%">{{ $inspection->pelabuhan }}</td>
                <td width="15%"><b>Tanggal</b></td>
                <td width="15%">{{ $inspection->tanggal ? \Carbon\Carbon::parse($inspection->tanggal)->format('d M Y') : '-' }}</td>
                <td width="15%"><b>Sertifikat</b></td>
                <td width="20%"><b>{{ $inspection->sertifikat_mencatat }}</b></td>
            </tr>
            <tr>
                <td><b>Nama Kapal</b></td>
                <td><b>{{ $inspection->nama_kapal }}</b></td>
                <td><b>Nomor Registrasi / IMO</b></td>
                <td>{{ $inspection->imo_no ?: '-' }}</td>
                <td><b>Bendera</b></td>
                <td>{{ $inspection->bendera ?: 'Indonesia' }}</td>
            </tr>
            <tr>
                <td><b>Jenis Kapal</b></td>
                <td>{{ $inspection->jenis_kapal }} ({{ $inspection->detail_jenis_kapal }})</td>
                <td><b>Berat (GT)</b></td>
                <td>{{ $inspection->berat_gt ? number_format($inspection->berat_gt) . ' GT' : '-' }}</td>
                <td><b>Kondisi Palka</b></td>
                <td>{{ $inspection->kondisi_palka ?: 'Kosong' }}</td>
            </tr>
            <tr>
                <td><b>Nama Pemeriksa</b></td>
                <td colspan="2"><b>{{ $inspection->nama_pemeriksa }}</b></td>
                <td><b>Alamat</b></td>
                <td colspan="2">{{ $inspection->alamat_pemeriksa ?: 'Balai Kekarantinaan Kesehatan Kelas I Bandung' }}</td>
            </tr>
        </table>

        <!-- ============================================== -->
        <!-- TABEL 32 AREA PEMERIKSAAN (9 KOLOM WHO IHR)    -->
        <!-- ============================================== -->
        <h6 class="font-weight-bold mt-3 mb-1" style="font-size: 13px;">Hasil Pemeriksaan Area / Fasilitas</h6>
        <small class="text-muted d-block d-md-none mb-1 d-print-none">
            <i class="fas fa-arrows-alt-h mr-1"></i>Geser tabel ke samping untuk melihat detail seluruh kolom
        </small>
        
        <div class="table-area-scroll">
            <table class="table-print table-area-print">
                <thead>
                    <tr>
                        <th width="3%">No</th>
                        <th width="15%">Area / Fasilitas</th>
                        <th width="13%">Status Pemeriksaan</th>
                        <th width="14%">Bukti Ditemukan</th>
                        <th width="11%">Hasil Sampel</th>
                        <th width="13%">Dokumen Diperiksa</th>
                        <th width="13%">Tindakan Pengendalian</th>
                        <th width="9%">Tgl Pemeriksaan Ulang</th>
                        <th width="9%">Komentar</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $areasList = $inspection->formatted_areas ?? $inspection->areas;
                    @endphp
                    @if(!empty($areasList) && is_array($areasList))
                        @foreach($areasList as $it)
                        @php
                            $st = strtolower($it['status'] ?? '');
                            $isClean = str_contains($st, 'tidak ditemukan') || str_contains($st, 'bebas') || str_contains($st, 'memenuhi') || $st === 'layak';
                            $isWarning = str_contains($st, 'ditemukan') || str_contains($st, 'kendali');
                        @endphp
                        <tr>
                            <td class="text-center font-weight-bold text-muted">{{ $it['no'] ?? $loop->iteration }}</td>
                            <td><b>{{ $it['nama'] ?? '-' }}</b></td>
                            <td class="text-center">
                                @if($isClean)
                                    <span class="text-success font-weight-bold" style="color: #047857 !important;">
                                        {{ $it['status'] ?: 'Tidak Ditemukan Faktor Risiko' }}
                                    </span>
                                @elseif($isWarning)
                                    <span class="text-warning font-weight-bold" style="color: #b45309 !important;">
                                        {{ $it['status'] ?: 'Ditemukan Faktor Risiko' }}
                                    </span>
                                @else
                                    <span>{{ $it['status'] ?: '-' }}</span>
                                @endif
                            </td>
                            <td>{{ $it['bukti'] ?? '-' }}</td>
                            <td>{{ $it['sampel'] ?? '-' }}</td>
                            <td>{{ $it['dokumen'] ?? '-' }}</td>
                            <td>{{ $it['tindakan'] ?? '-' }}</td>
                            <td class="text-center">{{ $it['tanggal_ulang'] ?? '-' }}</td>
                            <td>{{ $it['komentar'] ?? '-' }}</td>
                        </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="9" class="text-center text-muted py-3">Tidak ada data checklist area pemeriksaan kapal.</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>

        <!-- ============================================== -->
        <!-- KETERANGAN & TANDA TANGAN                      -->
        <!-- ============================================== -->
        <table class="table-print table-borderless table-ket-ttd mt-3">
            <tr>
                <td width="15%"><b>Keterangan :</b></td>
                <td width="55%" style="vertical-align: top;">
                    {{ $inspection->keterangan ?: 'Seluruh pemeriksaan telah dilaksanakan sesuai petunjuk teknis IHR 2005.' }}
                </td>
                <td width="30%" class="text-center" style="vertical-align: top;">
                    <p class="mb-2">Mengetahui / Petugas Pemeriksa,</p>
                    @if($inspection->tanda_tangan)
                        <img src="{{ $inspection->tanda_tangan }}" style="max-height: 80px; margin-bottom: 10px;" alt="Tanda Tangan">
                    @else
                        <div style="height: 60px;"></div>
                    @endif
                    <p class="mb-0"><b><u>{{ $inspection->nama_pemeriksa }}</u></b></p>
                </td>
            </tr>
        </table>

        <!-- ============================================== -->
        <!-- LAMPIRAN: BUKTI FOTO GEOTAGGING                -->
        <!-- ============================================== -->
        @if($inspection->foto_geotagging)
        <div class="mt-4 text-center" style="page-break-inside: avoid;">
            <p class="font-weight-bold mb-2" style="font-size: 13px;"><u>Lampiran: Bukti Foto Geotagging</u></p>
            <a href="{{ asset($inspection->foto_geotagging) }}" target="_blank" class="d-inline-block">
                <img 
                    src="{{ asset($inspection->foto_geotagging) }}" 
                    style="max-height: 520px; max-width: 100%; border: 1px solid #dee2e6; border-radius: 6px; padding: 4px; box-shadow: 0 2px 8px rgba(0,0,0,0.06);" 
                    alt="Bukti Foto Geotagging" 
                    onerror="this.onerror=null;this.src='/images/photo-placeholder.png';"
                >
            </a>
            @if($inspection->latitude && $inspection->longitude)
            <p class="mt-2 text-muted" style="font-size: 11px;">
                📍 Koordinat: <b>{{ $inspection->latitude }}, {{ $inspection->longitude }}</b> 
                &bull; Lokasi: {{ $inspection->lokasi_geotag ?? 'Wilayah Kerja Pelabuhan' }}
                &bull; <a href="https://www.google.com/maps?q={{ $inspection->latitude }},{{ $inspection->longitude }}" target="_blank" class="text-primary font-weight-bold d-print-none">Lihat Titik di Google Maps ↗</a>
            </p>
            @endif
        </div>
        @endif

    </div>

    <!-- Scripts -->
    <script>
        function copyPageLink() {
            var input = document.getElementById('pageLinkInput');
            if (!input) return;
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(input.value).then(showCopyFeedback).catch(function() {
                    input.select();
                    document.execCommand('copy');
                    showCopyFeedback();
                });
            } else {
                input.select();
                document.execCommand('copy');
                showCopyFeedback();
            }
        }

        function showCopyFeedback() {
            var btn = document.getElementById('btnPageCopy');
            if (btn) {
                var origText = btn.textContent;
                btn.className = 'btn btn-success btn-sm font-weight-bold';
                btn.textContent = 'Tersalin! ✓';
                setTimeout(function() {
                    btn.className = 'btn btn-dark btn-sm font-weight-bold';
                    btn.textContent = origText;
                }, 2500);
            }
        }
    </script>
</body>
</html>
