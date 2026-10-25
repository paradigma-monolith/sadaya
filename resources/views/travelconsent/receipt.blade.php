<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konfirmasi Submit &bull; {{ $inspection->registration_no }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    <style>
        *, ::before, ::after { box-sizing: border-box; border-width: 0; }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f8fafc;
            color: #0f172a;
            margin: 0;
            padding: 40px 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }
        .card {
            background: #ffffff;
            max-width: 680px;
            width: 100%;
            border-radius: 24px;
            box-shadow: 0 20px 40px -15px rgba(0,0,0,0.07);
            border: 1px solid #e2e8f0;
            padding: 40px;
            text-align: center;
        }
        .success-icon {
            width: 72px;
            height: 72px;
            border-radius: 50%;
            background: #dcfce7;
            color: #16a34a;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
        }
        .badge-reg {
            display: inline-block;
            font-family: 'JetBrains Mono', monospace;
            background: #f0fdf4;
            color: #15803d;
            border: 1.5px solid #86efac;
            padding: 8px 18px;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 700;
            margin: 15px 0 25px 0;
            letter-spacing: 0.5px;
        }
        .detail-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 20px;
            text-align: left;
            margin-bottom: 30px;
            font-size: 13.5px;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px dashed #e2e8f0;
        }
        .detail-row:last-child {
            border-bottom: none;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 12px 24px;
            font-size: 14px;
            font-weight: 600;
            border-radius: 12px;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s;
        }
        .btn-primary {
            background: #0284c7;
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(2, 132, 199, 0.3);
        }
        .btn-primary:hover {
            background: #0369a1;
        }
        .btn-secondary {
            background: #f1f5f9;
            color: #334155;
            border: 1px solid #cbd5e1;
        }
        .btn-secondary:hover {
            background: #e2e8f0;
            color: #0f172a;
        }
    </style>
</head>
<body>

<div class="card">
    <div style="margin-bottom: 24px; padding-bottom: 16px; border-bottom: 1px solid #e2e8f0;">
        <img src="{{ asset('storage/images/Kop_BKK_bandung.png') }}" alt="Balai Kekarantinaan Kesehatan Bandung" style="width: 100%; height: auto; display: block; margin: 0 auto;" onerror="this.onerror=null;this.src='{{ asset('images/Kop_BKK_bandung.png') }}';">
    </div>

    <div class="success-icon">
        <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
    </div>

    <h2 style="margin: 0; font-size: 24px; font-weight: 800; color: #0f172a;">Inspeksi Berhasil Disimpan</h2>
    <p style="margin: 8px 0 0 0; color: #64748b; font-size: 14px;">
        Formulir Aircraft Sanitation Inspection telah terekam resmi dalam sistem Balai Kekarantinaan Kesehatan Bandung.
    </p>

    <div>
        <span class="badge-reg">{{ $inspection->registration_no }}</span>
    </div>

    <div class="detail-box">
        <div class="detail-row">
            <span style="color: #64748b;">Pemilik / Operator:</span>
            <strong>{{ $inspection->pemilik }}</strong>
        </div>
        <div class="detail-row">
            <span style="color: #64748b;">Tanda Kebangsaan &amp; Reg:</span>
            <strong>{{ $inspection->tanda_kebangsaan }}</strong>
        </div>
        <div class="detail-row">
            <span style="color: #64748b;">Tanggal &amp; Waktu:</span>
            <span>{{ $inspection->tanggal ? $inspection->tanggal->format('d M Y') : '-' }} &bull; {{ $inspection->waktu }} WIB</span>
        </div>
        <div class="detail-row">
            <span style="color: #64748b;">Kelaikan Sanitasi:</span>
            @if($inspection->kesimpulan === 'layak')
                <span style="color: #16a34a; font-weight: 700;">LAYAK (Sertifikat Bebas Serangga)</span>
            @else
                <span style="color: #e11d48; font-weight: 700;">TIDAK LAYAK (Perlu Tindakan)</span>
            @endif
        </div>
        @if($inspection->tindakan)
        <div class="detail-row">
            <span style="color: #64748b;">Tindakan Penyehatan:</span>
            <span style="color: #0284c7; font-weight: 600; text-transform: uppercase;">{{ str_replace('_', ' ', $inspection->tindakan) }}</span>
        </div>
        @endif
        <div class="detail-row">
            <span style="color: #64748b;">Petugas Kabin (Crew):</span>
            <span>{{ $inspection->petugas_kabin_nama }}</span>
        </div>
        <div class="detail-row">
            <span style="color: #64748b;">Petugas BKK Bandung:</span>
            <span>{{ $inspection->petugas_bkk_nama }}</span>
        </div>
    </div>

    <div style="display: flex; gap: 12px; justify-content: center; flex-wrap: wrap;">
        <a href="{{ route('travelconsent.public_pdf', $inspection->registration_no) }}" class="btn btn-primary">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            <span>Unduh Berkas PDF Resmi</span>
        </a>
        <a href="{{ route('travelconsent.public_aircraft') }}" class="btn btn-secondary">
            <span>Input Formulir Lain</span>
        </a>
    </div>

    <div style="margin-top: 30px; border-top: 1px solid #f1f5f9; padding-top: 15px; font-size: 11.5px; color: #94a3b8;">
        Balai Kekarantinaan Kesehatan Kelas I Bandung &bull; Kementerian Kesehatan Republik Indonesia
    </div>
</div>

</body>
</html>
