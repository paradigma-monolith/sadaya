<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Tanda Bukti Lapor Self-Assessment Karantina Kapal &bull; {{ $surveillance->registration_no }}</title>

  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
  
  <!-- Font Awesome & Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

  <style>
    body {
      background-color: #f8fafc;
      font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
      color: #0f172a;
      min-height: 100vh;
      padding: 30px 15px;
    }
    .receipt-card {
      background: #ffffff;
      border: 1px solid #e2e8f0;
      border-radius: 24px;
      box-shadow: 0 15px 35px rgba(0, 0, 0, 0.06);
      max-width: 780px;
      margin: 0 auto;
      padding: 36px 32px;
    }
    .badge-reg {
      font-family: 'JetBrains Mono', monospace;
      font-weight: 700;
      letter-spacing: 0.5px;
      font-size: 15px;
      padding: 6px 16px;
      border-radius: 10px;
    }
    @media print {
      body {
        background-color: #fff !important;
        padding: 0 !important;
      }
      .receipt-card {
        border: none !important;
        box-shadow: none !important;
        padding: 0 !important;
        max-width: 100% !important;
      }
      .d-print-none {
        display: none !important;
      }
    }
  </style>
</head>
<body>

<div class="container">

  <!-- Top Action Bar (Print / Back) -->
  <div class="d-flex justify-content-between align-items-center mb-3 max-w-780 mx-auto d-print-none" style="max-width: 780px;">
    <a href="{{ route('ship-surveillance.portal') }}" class="btn btn-outline-secondary btn-sm rounded-pill font-weight-bold">
      <i class="fas fa-arrow-left me-1"></i> Kembali ke Portal
    </a>
    <div class="d-flex gap-2">
      <button onclick="window.print()" class="btn btn-dark btn-sm rounded-pill px-3 font-weight-bold">
        <i class="fas fa-print me-1"></i> Cetak Tanda Bukti
      </button>
      <a href="{{ url('/ship-surveillance') }}" class="btn btn-primary btn-sm rounded-pill px-3 font-weight-bold" style="background-color: #00A99D; border-color: #00A99D;">
        <i class="fas fa-tachometer-alt me-1"></i> Masuk ke Dashboard
      </a>
    </div>
  </div>

  <!-- Main Receipt Card -->
  <div class="receipt-card">
    
    <!-- Kop Surat Resmi BKK Kelas I Bandung -->
    <div class="text-center pb-3 border-bottom mb-4">
      <img 
        src="{{ asset('storage/images/Kop_BKK_bandung.png') }}" 
        alt="Kop Surat Resmi Balai Kekarantinaan Kesehatan Kelas I Bandung" 
        class="w-100 max-w-full h-auto object-contain mb-2"
        style="max-height: 125px;"
        onerror="this.onerror=null;this.src='{{ asset('images/Kop_BKK_bandung.png') }}';"
      >
      <div class="text-uppercase small fw-bold text-secondary tracking-wider">
        Digitalisasi Pengawasan Kekarantinaan Kesehatan di Pelabuhan (TANGKAP)
      </div>
    </div>

    <!-- Success Indicator -->
    <div class="text-center mb-4">
      <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-success-subtle text-success mb-2" style="width: 56px; height: 56px; font-size: 24px;">
        <i class="fas fa-check"></i>
      </div>
      <h3 class="fw-bold text-dark mb-1">Self-Assessment Berhasil Dikirim</h3>
      <p class="text-muted small mb-2">Data pengawasan kedatangan kapal telah tersimpan di sistem BKK Kelas I Bandung.</p>
      
      <span class="badge bg-success-subtle text-success border border-success-subtle badge-reg">
        {{ $surveillance->registration_no }}
      </span>
    </div>

    <!-- Summary Details Grid -->
    <div class="bg-light rounded-4 p-4 border mb-4">
      <div class="row g-3">
        <div class="col-sm-6">
          <span class="text-muted small d-block">Nama Kapal</span>
          <span class="fw-bold text-dark fs-5 text-uppercase">{{ $surveillance->nama_kapal }}</span>
        </div>
        <div class="col-sm-6">
          <span class="text-muted small d-block">Kategori Rute</span>
          <span class="badge {{ $surveillance->kategori == 'Luar Negeri' ? 'bg-info text-dark' : 'bg-secondary' }} px-2.5 py-1">
            {{ $surveillance->kategori }}
          </span>
        </div>

        <div class="col-sm-6">
          <span class="text-muted small d-block">Pelabuhan Asal</span>
          <span class="fw-semibold text-dark text-uppercase">{{ $surveillance->datang_dari }} {{ $surveillance->origin_country ? '('.$surveillance->origin_country.')' : '' }}</span>
        </div>
        <div class="col-sm-6">
          <span class="text-muted small d-block">Pelabuhan Tujuan</span>
          <span class="fw-semibold text-dark">{{ $surveillance->pelabuhan }}</span>
        </div>

        <div class="col-sm-4">
          <span class="text-muted small d-block">Tanggal Kedatangan</span>
          <span class="fw-semibold text-dark">{{ $surveillance->tanggal_kedatangan?->format('d/m/Y') ?? date('d/m/Y') }}</span>
        </div>
        <div class="col-sm-4">
          <span class="text-muted small d-block">Gross Tonnage (GT)</span>
          <span class="fw-semibold text-dark">{{ number_format($surveillance->gross_tonnage, 0, ',', '.') }} GT</span>
        </div>
        <div class="col-sm-4">
          <span class="text-muted small d-block">Jumlah Kru</span>
          <span class="fw-semibold text-dark">{{ $surveillance->jml_crew }} Orang</span>
        </div>

        <div class="col-12 border-top pt-3 mt-3">
          <div class="row g-3">
            <div class="col-sm-4 text-center border-end">
              <span class="text-muted small d-block">Skor Penilaian</span>
              <span class="h4 fw-bold font-monospace text-dark mb-0">{{ $surveillance->skoring }}</span>
            </div>
            <div class="col-sm-4 text-center border-end">
              <span class="text-muted small d-block">Faktor Risiko</span>
              @if($surveillance->kresiko == 'TINGGI')
                <span class="badge bg-danger fs-6 px-3 py-1">TINGGI</span>
              @elseif($surveillance->kresiko == 'SEDANG')
                <span class="badge bg-warning text-dark fs-6 px-3 py-1">SEDANG</span>
              @else
                <span class="badge bg-success fs-6 px-3 py-1">RENDAH</span>
              @endif
            </div>
            <div class="col-sm-4 text-center">
              <span class="text-muted small d-block">Keputusan Status</span>
              @if($surveillance->hasil_status == 'ZONA KARANTINA')
                <span class="badge bg-danger fs-6 px-3 py-1">ZONA KARANTINA</span>
              @else
                <span class="badge bg-success fs-6 px-3 py-1">SANDAR</span>
              @endif
            </div>
          </div>
        </div>

        <div class="col-12 border-top pt-3">
          <div class="d-flex justify-content-between align-items-center">
            <div>
              <span class="text-muted small d-block">Estimasi Tarif PNBP (PP Kemenkes)</span>
              <span class="fw-bold text-success fs-6">Rp {{ number_format($surveillance->estimasi_pnbp, 0, ',', '.') }}</span>
            </div>
            <div class="text-end">
              <span class="text-muted small d-block">Status Verifikasi</span>
              <span class="badge {{ $surveillance->status_verifikasi == 'verified' ? 'bg-success' : 'bg-warning text-dark' }} px-3 py-1">
                {{ $surveillance->status_verifikasi == 'verified' ? 'Terverifikasi' : 'Menunggu Verifikasi Petugas' }}
              </span>
            </div>
          </div>
        </div>

      </div>
    </div>

    <!-- Notice Box -->
    <div class="alert alert-info border-0 rounded-3 small mb-4">
      <i class="fas fa-info-circle me-1"></i>
      <strong>Catatan untuk Nahkoda / Agen Kapal:</strong> 
      Simpan atau cetak bukti tanda lapor ini. Petugas Karantina Kesehatan BKK Kelas I Bandung akan melakukan verifikasi fisik / administratif pada saat kapal tiba di zona labuh atau dermaga pelabuhan.
    </div>

    <!-- Footer Timestamp & Copyright -->
    <div class="text-center text-muted small border-top pt-3">
      <p class="mb-0">Diterbitkan secara elektronik oleh Sistem TANGKAP &bull; BKK Kelas I Bandung</p>
      <p class="font-monospace text-xs mb-0">ID Laporan: {{ $surveillance->id }} &bull; Tanggal Lapor: {{ $surveillance->created_at?->format('d/m/Y H:i') ?? date('d/m/Y H:i') }} WIB</p>
    </div>

  </div>

</div>

</body>
</html>

