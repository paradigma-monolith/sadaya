<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Formulir Karantina Kapal Dari Dalam Negeri &bull; BKK Kelas I Bandung</title>

  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;600&display=swap" rel="stylesheet">
  
  <!-- Font Awesome & Bootstrap 5 & Tailwind -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

  <style>
    body {
      background-color: #f1f5f9;
      font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
      color: #0f172a;
      min-height: 100vh;
      padding-bottom: 80px;
    }
    .form-header-card {
      background: linear-gradient(135deg, #005851 0%, #00A99D 100%);
      color: white;
      border-radius: 20px;
      padding: 24px 30px;
      box-shadow: 0 10px 25px rgba(0, 169, 157, 0.25);
      margin-bottom: 24px;
    }
    .card-box {
      background: #ffffff;
      border-radius: 18px;
      border: 1px solid #e2e8f0;
      box-shadow: 0 4px 16px rgba(15, 23, 42, 0.04);
      padding: 24px;
      margin-bottom: 20px;
    }
    .question-row {
      padding: 14px 10px;
      border-bottom: 1px solid #f1f5f9;
      transition: background-color 0.2s;
    }
    .question-row:hover {
      background-color: #f8fafc;
    }
    .form-check-input {
      width: 1.5em;
      height: 1.5em;
      cursor: pointer;
    }
    .sticky-score-bar {
      position: sticky;
      bottom: 15px;
      z-index: 100;
      background: #ffffff;
      border: 2px solid #00A99D;
      border-radius: 20px;
      box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
      padding: 14px 20px;
      backdrop-filter: blur(10px);
    }
  </style>
</head>
<body>

<div class="container max-w-5xl py-4">

  <!-- Top Official Header: Logo Kemenkes & Karantina -->
  <div class="text-center mb-3">
    <a href="{{ route('ship-surveillance.portal') }}" title="Portal Self-Assessment TANGKAP">
      <img 
        src="{{ asset('storage/images/LOGO KEMENKES & KARANTINA.png') }}" 
        alt="Logo Kemenkes & Karantina Kesehatan" 
        class="h-12 sm:h-14 w-auto mx-auto object-contain drop-shadow-xs"
        style="max-height: 54px;"
        onerror="this.onerror=null;this.src='{{ asset('images/LOGO KEMENKES & KARANTINA.png') }}';"
      >
    </a>
  </div>

  <!-- Top Breadcrumb & Action -->
  <div class="d-flex justify-content-between align-items-center mb-3">
    <a href="{{ route('ship-surveillance.portal') }}" class="btn btn-outline-secondary btn-sm rounded-pill font-weight-bold">
      <i class="fas fa-arrow-left me-1"></i> Kembali ke Pilihan Formulir
    </a>
    <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-pill font-monospace">
      <i class="fas fa-flag me-1"></i> RUTE DALAM NEGERI (DOMESTIK)
    </span>
  </div>

  <!-- Header Banner -->
  <div class="form-header-card text-center text-md-start d-md-flex align-items-center justify-content-between">
    <div class="d-flex align-items-center gap-3">
      <img 
        src="{{ asset('storage/images/LOGO KARANTINA.png') }}" 
        alt="Logo Karantina" 
        class="d-none d-md-block h-14 w-auto object-contain drop-shadow-sm"
        style="height: 56px;"
        onerror="this.onerror=null;this.src='{{ asset('images/LOGO KARANTINA.png') }}';"
      >
      <div>
        <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill bg-white/20 text-white text-xs mb-2">
          <i class="fas fa-ship"></i>
          <span>Formulir Self-Assessment Karantina Kesehatan</span>
        </div>
        <h1 class="h3 fw-bold mb-1">KEDATANGAN KAPAL DARI DALAM NEGERI</h1>
        <p class="mb-0 text-white-50 small">
          BKK Kelas I Bandung &bull; Wilayah Kerja Patimban, Cirebon, Indramayu, Palabuhanratu
        </p>
      </div>
    </div>
    <div class="mt-3 mt-md-0 text-end">
      <span class="badge bg-white text-dark px-3 py-2 rounded-pill small font-monospace shadow-sm">
        <i class="fas fa-shield-alt text-success me-1"></i> Standar WHO IHR 2005
      </span>
    </div>
  </div>

  @if(isset($errors) && $errors->any())
  <div class="alert alert-danger rounded-4 shadow-sm mb-4">
    <h5 class="fw-bold mb-2"><i class="fas fa-exclamation-triangle me-2"></i> Mohon lengkapi isian berikut:</h5>
    <ul class="mb-0 ps-3">
      @foreach($errors->all() as $error)
      <li>{{ $error }}</li>
      @endforeach
    </ul>
  </div>
  @endif

  <!-- Main Form -->
  <form action="{{ route('ship-surveillance.assessment.submit') }}" method="POST" id="formQuarantineDomestic">
    @csrf
    <input type="hidden" name="kategori" value="Dalam Negeri">
    <input type="hidden" name="skoring" id="inputSkoring" value="0">
    <input type="hidden" name="kresiko" id="inputKresiko" value="RENDAH">
    <input type="hidden" name="hasil_status" id="inputHasilStatus" value="SANDAR">

    <!-- Section 1: Data Identitas Kapal -->
    <div class="card-box">
      <h5 class="fw-bold text-dark border-bottom pb-2 mb-3 d-flex align-items-center gap-2">
        <i class="fas fa-info-circle text-teal" style="color: #00A99D;"></i>
        <span>1. Identitas & Informasi Kedatangan Kapal</span>
      </h5>

      <div class="row g-3">
        <!-- Nama Kapal -->
        <div class="col-md-6">
          <label class="form-label fw-semibold text-secondary small">Nama Kapal <span class="text-danger">*</span></label>
          <input 
            type="text" 
            name="nama_kapal" 
            id="namaKapal" 
            required 
            placeholder="Contoh: KM. NUSANTARA BAHARI" 
            class="form-control form-control-lg text-uppercase"
            value="{{ old('nama_kapal') }}"
          >
        </div>

        <!-- Pelabuhan Asal -->
        <div class="col-md-6">
          <label class="form-label fw-semibold text-secondary small">Pelabuhan Terakhir / Asal <span class="text-danger">*</span></label>
          <input 
            type="text" 
            name="datang_dari" 
            id="datangDari" 
            required 
            placeholder="Contoh: PELABUHAN TANJUNG PERAK SURABAYA" 
            class="form-control form-control-lg text-uppercase"
            value="{{ old('datang_dari') }}"
          >
        </div>

        <!-- Pelabuhan Tujuan BKK Bandung -->
        <div class="col-md-4">
          <label class="form-label fw-semibold text-secondary small">Pelabuhan Tujuan (BKK Bandung) <span class="text-danger">*</span></label>
          <select name="pelabuhan" id="pelabuhan" class="form-select form-select-lg" required>
            <option value="">-- Pilih Pelabuhan --</option>
            <option value="Wilayah Kerja Patimban" {{ old('pelabuhan') == 'Wilayah Kerja Patimban' ? 'selected' : '' }}>Wilayah Kerja Patimban</option>
            <option value="Wilayah Kerja Cirebon" {{ old('pelabuhan') == 'Wilayah Kerja Cirebon' ? 'selected' : '' }}>Wilayah Kerja Cirebon</option>
            <option value="Wilayah Kerja Indramayu" {{ old('pelabuhan') == 'Wilayah Kerja Indramayu' ? 'selected' : '' }}>Wilayah Kerja Indramayu</option>
            <option value="Wilayah Kerja Palabuhanratu" {{ old('pelabuhan') == 'Wilayah Kerja Palabuhanratu' ? 'selected' : '' }}>Wilayah Kerja Palabuhanratu</option>
            <option value="Balai Kekarantinaan Kesehatan Kelas I Bandung (Induk)" {{ old('pelabuhan') == 'Balai Kekarantinaan Kesehatan Kelas I Bandung (Induk)' ? 'selected' : '' }}>Balai Kekarantinaan Kesehatan Kelas I Bandung (Induk)</option>
          </select>
        </div>

        <!-- Tanggal Kedatangan -->
        <div class="col-md-4">
          <label class="form-label fw-semibold text-secondary small">Tanggal Kedatangan <span class="text-danger">*</span></label>
          <input 
            type="date" 
            name="tanggal_kedatangan" 
            id="tanggalKedatangan" 
            required 
            class="form-control form-control-lg"
            value="{{ old('tanggal_kedatangan', date('Y-m-d')) }}"
          >
        </div>

        <!-- Gross Tonnage (GT) -->
        <div class="col-md-2">
          <label class="form-label fw-semibold text-secondary small">Gross Tonnage (GT) <span class="text-danger">*</span></label>
          <input 
            type="number" 
            name="gross_tonnage" 
            id="grossTonnage" 
            min="0" 
            required 
            placeholder="GT" 
            class="form-control form-control-lg"
            value="{{ old('gross_tonnage') }}"
          >
        </div>

        <!-- Jumlah Kru -->
        <div class="col-md-2">
          <label class="form-label fw-semibold text-secondary small">Jumlah Kru <span class="text-danger">*</span></label>
          <input 
            type="number" 
            name="jml_crew" 
            id="jmlCrew" 
            min="1" 
            required 
            placeholder="Orang" 
            class="form-control form-control-lg"
            value="{{ old('jml_crew') }}"
          >
        </div>
      </div>
    </div>

    <!-- Section 2: Daftar Pertanyaan Karantina Kesehatan -->
    <div class="card-box">
      <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
        <h5 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
          <i class="fas fa-clipboard-check text-teal" style="color: #00A99D;"></i>
          <span>2. Daftar Pertanyaan Karantina Kesehatan (IHR / Permenkes)</span>
        </h5>
        <span class="small text-muted">Jawab "Ya" atau "Tidak" sesuai kondisi sebenarnya</span>
      </div>

      <!-- Q1 hanya berlaku pada formulir Kedatangan Luar Negeri (daftar negara singgah 14 hari) -->

      <!-- Question 1 (Q2) -->
      <div class="question-row d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
        <div class="pe-md-4">
          <span class="fw-bold text-dark">1.</span>
          <span class="text-secondary">Apakah ada orang yang meninggal di atas kapal selama dalam perjalanan yang bukan diakibatkan oleh kecelakaan?</span>
        </div>
        <div class="d-flex gap-4 shrink-0">
          <div class="form-check form-check-inline">
            <input class="form-check-input q-radio" type="radio" name="Q2" id="Q2_ya" value="20" onchange="calculateTotal()">
            <label class="form-check-label fw-semibold" for="Q2_ya">Ya</label>
          </div>
          <div class="form-check form-check-inline">
            <input class="form-check-input q-radio" type="radio" name="Q2" id="Q2_tidak" value="0" checked onchange="calculateTotal()">
            <label class="form-check-label fw-semibold" for="Q2_tidak">Tidak</label>
          </div>
        </div>
      </div>

      <!-- Question 2 (Q3) -->
      <div class="question-row d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
        <div class="pe-md-4">
          <span class="fw-bold text-dark">2.</span>
          <span class="text-secondary">Apakah dalam perjalanan ada crew dievakuasi karena sakit atau meninggal?</span>
        </div>
        <div class="d-flex gap-4 shrink-0">
          <div class="form-check form-check-inline">
            <input class="form-check-input q-radio" type="radio" name="Q3" id="Q3_ya" value="20" onchange="calculateTotal()">
            <label class="form-check-label fw-semibold" for="Q3_ya">Ya</label>
          </div>
          <div class="form-check form-check-inline">
            <input class="form-check-input q-radio" type="radio" name="Q3" id="Q3_tidak" value="0" checked onchange="calculateTotal()">
            <label class="form-check-label fw-semibold" for="Q3_tidak">Tidak</label>
          </div>
        </div>
      </div>

      <!-- Question 3 (Q4) -->
      <div class="question-row d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
        <div class="pe-md-4">
          <span class="fw-bold text-dark">3.</span>
          <span class="text-secondary">Apakah di atas kapal selama dalam perjalanan terdapat kasus penyakit yang diduga karena penyakit menular?</span>
        </div>
        <div class="d-flex gap-4 shrink-0">
          <div class="form-check form-check-inline">
            <input class="form-check-input q-radio" type="radio" name="Q4" id="Q4_ya" value="20" onchange="calculateTotal()">
            <label class="form-check-label fw-semibold" for="Q4_ya">Ya</label>
          </div>
          <div class="form-check form-check-inline">
            <input class="form-check-input q-radio" type="radio" name="Q4" id="Q4_tidak" value="0" checked onchange="calculateTotal()">
            <label class="form-check-label fw-semibold" for="Q4_tidak">Tidak</label>
          </div>
        </div>
      </div>

      <!-- Question 4 (Q5) -->
      <div class="question-row d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
        <div class="pe-md-4">
          <span class="fw-bold text-dark">4.</span>
          <span class="text-secondary">Apakah jumlah orang sakit saat ini lebih banyak dari biasanya?</span>
        </div>
        <div class="d-flex gap-4 shrink-0">
          <div class="form-check form-check-inline">
            <input class="form-check-input q-radio" type="radio" name="Q5" id="Q5_ya" value="10" onchange="calculateTotal()">
            <label class="form-check-label fw-semibold" for="Q5_ya">Ya</label>
          </div>
          <div class="form-check form-check-inline">
            <input class="form-check-input q-radio" type="radio" name="Q5" id="Q5_tidak" value="0" checked onchange="calculateTotal()">
            <label class="form-check-label fw-semibold" for="Q5_tidak">Tidak</label>
          </div>
        </div>
      </div>

      <!-- Question 5 (Q6) -->
      <div class="question-row d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
        <div class="pe-md-4">
          <span class="fw-bold text-dark">5.</span>
          <span class="text-secondary">Apakah ada orang sakit di atas kapal saat ini?</span>
        </div>
        <div class="d-flex gap-4 shrink-0">
          <div class="form-check form-check-inline">
            <input class="form-check-input q-radio" type="radio" name="Q6" id="Q6_ya" value="10" onchange="calculateTotal()">
            <label class="form-check-label fw-semibold" for="Q6_ya">Ya</label>
          </div>
          <div class="form-check form-check-inline">
            <input class="form-check-input q-radio" type="radio" name="Q6" id="Q6_tidak" value="0" checked onchange="calculateTotal()">
            <label class="form-check-label fw-semibold" for="Q6_tidak">Tidak</label>
          </div>
        </div>
      </div>

      <!-- Question 6 (Q7) -->
      <div class="question-row d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
        <div class="pe-md-4">
          <span class="fw-bold text-dark">6.</span>
          <span class="text-secondary">Apakah dokter kapal dipanggil untuk mengobati sakit tersebut?</span>
        </div>
        <div class="d-flex gap-4 shrink-0">
          <div class="form-check form-check-inline">
            <input class="form-check-input q-radio" type="radio" name="Q7" id="Q7_ya" value="10" onchange="calculateTotal()">
            <label class="form-check-label fw-semibold" for="Q7_ya">Ya</label>
          </div>
          <div class="form-check form-check-inline">
            <input class="form-check-input q-radio" type="radio" name="Q7" id="Q7_tidak" value="0" checked onchange="calculateTotal()">
            <label class="form-check-label fw-semibold" for="Q7_tidak">Tidak</label>
          </div>
        </div>
      </div>

      <!-- Question 7 (Q8 - Conditional based on Q7) -->
      <div id="wrapperQ8" class="question-row d-none bg-warning-subtle/30 justify-content-between align-items-md-center gap-2 ps-3 border-start border-warning border-3">
        <div class="pe-md-4">
          <span class="fw-bold text-dark">7.</span>
          <span class="text-secondary">Apakah ada kondisi di atas kapal yang dapat menyebabkan infeksi atau penyebaran penyakit?</span>
        </div>
        <div class="d-flex gap-4 shrink-0">
          <div class="form-check form-check-inline">
            <input class="form-check-input q-radio" type="radio" name="Q8" id="Q8_ya" value="20" onchange="calculateTotal()">
            <label class="form-check-label fw-semibold" for="Q8_ya">Ya</label>
          </div>
          <div class="form-check form-check-inline">
            <input class="form-check-input q-radio" type="radio" name="Q8" id="Q8_tidak" value="0" checked onchange="calculateTotal()">
            <label class="form-check-label fw-semibold" for="Q8_tidak">Tidak</label>
          </div>
        </div>
      </div>

      <!-- Question 8 (Q9) -->
      <div class="question-row d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
        <div class="pe-md-4">
          <span class="fw-bold text-dark">8.</span>
          <span class="text-secondary">Apakah dilakukan tindakan penyehatan selama dalam perjalanan (desinfeksi, desinseksi, deratisasi)?</span>
        </div>
        <div class="d-flex gap-4 shrink-0">
          <div class="form-check form-check-inline">
            <input class="form-check-input q-radio" type="radio" name="Q9" id="Q9_ya" value="5" onchange="calculateTotal()">
            <label class="form-check-label fw-semibold" for="Q9_ya">Ya</label>
          </div>
          <div class="form-check form-check-inline">
            <input class="form-check-input q-radio" type="radio" name="Q9" id="Q9_tidak" value="0" checked onchange="calculateTotal()">
            <label class="form-check-label fw-semibold" for="Q9_tidak">Tidak</label>
          </div>
        </div>
      </div>

      <!-- Question 9 (Q10) -->
      <div class="question-row d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
        <div class="pe-md-4">
          <span class="fw-bold text-dark">9.</span>
          <span class="text-secondary">Apakah ditemukan binatang pembawa penyakit (tikus, pinjal, lalat, nyamuk) di atas kapal?</span>
        </div>
        <div class="d-flex gap-4 shrink-0">
          <div class="form-check form-check-inline">
            <input class="form-check-input q-radio" type="radio" name="Q10" id="Q10_ya" value="10" onchange="calculateTotal()">
            <label class="form-check-label fw-semibold" for="Q10_ya">Ya</label>
          </div>
          <div class="form-check form-check-inline">
            <input class="form-check-input q-radio" type="radio" name="Q10" id="Q10_tidak" value="0" checked onchange="calculateTotal()">
            <label class="form-check-label fw-semibold" for="Q10_tidak">Tidak</label>
          </div>
        </div>
      </div>

      <!-- Question 10 (Q11) -->
      <div class="question-row d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
        <div class="pe-md-4">
          <span class="fw-bold text-dark">10.</span>
          <span class="text-secondary">Apakah ditemukan orang sakit dengan gejala demam (suhu &ge; 38&deg;C) disertai batuk/pilek/sesak nafas?</span>
        </div>
        <div class="d-flex gap-4 shrink-0">
          <div class="form-check form-check-inline">
            <input class="form-check-input q-radio" type="radio" name="Q11" id="Q11_ya" value="20" onchange="calculateTotal()">
            <label class="form-check-label fw-semibold" for="Q11_ya">Ya</label>
          </div>
          <div class="form-check form-check-inline">
            <input class="form-check-input q-radio" type="radio" name="Q11" id="Q11_tidak" value="0" checked onchange="calculateTotal()">
            <label class="form-check-label fw-semibold" for="Q11_tidak">Tidak</label>
          </div>
        </div>
      </div>

      <!-- Question 11 (Q12) -->
      <div class="question-row d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
        <div class="pe-md-4">
          <span class="fw-bold text-dark">11.</span>
          <span class="text-secondary">Apakah ditemukan orang sakit dengan gejala ruam/bintik merah pada kulit?</span>
        </div>
        <div class="d-flex gap-4 shrink-0">
          <div class="form-check form-check-inline">
            <input class="form-check-input q-radio" type="radio" name="Q12" id="Q12_ya" value="20" onchange="calculateTotal()">
            <label class="form-check-label fw-semibold" for="Q12_ya">Ya</label>
          </div>
          <div class="form-check form-check-inline">
            <input class="form-check-input q-radio" type="radio" name="Q12" id="Q12_tidak" value="0" checked onchange="calculateTotal()">
            <label class="form-check-label fw-semibold" for="Q12_tidak">Tidak</label>
          </div>
        </div>
      </div>

      <!-- Question 12 (Q13) -->
      <div class="question-row d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
        <div class="pe-md-4">
          <span class="fw-bold text-dark">12.</span>
          <span class="text-secondary">Apakah ditemukan orang sakit dengan gejala diare akut?</span>
        </div>
        <div class="d-flex gap-4 shrink-0">
          <div class="form-check form-check-inline">
            <input class="form-check-input q-radio" type="radio" name="Q13" id="Q13_ya" value="20" onchange="calculateTotal()">
            <label class="form-check-label fw-semibold" for="Q13_ya">Ya</label>
          </div>
          <div class="form-check form-check-inline">
            <input class="form-check-input q-radio" type="radio" name="Q13" id="Q13_tidak" value="0" checked onchange="calculateTotal()">
            <label class="form-check-label fw-semibold" for="Q13_tidak">Tidak</label>
          </div>
        </div>
      </div>

      <!-- Question 13 (Q14) -->
      <div class="question-row d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
        <div class="pe-md-4">
          <span class="fw-bold text-dark">13.</span>
          <span class="text-secondary">Apakah ditemukan orang sakit dengan gejala muntah terus menerus?</span>
        </div>
        <div class="d-flex gap-4 shrink-0">
          <div class="form-check form-check-inline">
            <input class="form-check-input q-radio" type="radio" name="Q14" id="Q14_ya" value="20" onchange="calculateTotal()">
            <label class="form-check-label fw-semibold" for="Q14_ya">Ya</label>
          </div>
          <div class="form-check form-check-inline">
            <input class="form-check-input q-radio" type="radio" name="Q14" id="Q14_tidak" value="0" checked onchange="calculateTotal()">
            <label class="form-check-label fw-semibold" for="Q14_tidak">Tidak</label>
          </div>
        </div>
      </div>

      <!-- Question 14 (Q15) -->
      <div class="question-row d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
        <div class="pe-md-4">
          <span class="fw-bold text-dark">14.</span>
          <span class="text-secondary">Apakah ditemukan orang sakit dengan gejala penyakit kuning (mata/kulit kuning)?</span>
        </div>
        <div class="d-flex gap-4 shrink-0">
          <div class="form-check form-check-inline">
            <input class="form-check-input q-radio" type="radio" name="Q15" id="Q15_ya" value="20" onchange="calculateTotal()">
            <label class="form-check-label fw-semibold" for="Q15_ya">Ya</label>
          </div>
          <div class="form-check form-check-inline">
            <input class="form-check-input q-radio" type="radio" name="Q15" id="Q15_tidak" value="0" checked onchange="calculateTotal()">
            <label class="form-check-label fw-semibold" for="Q15_tidak">Tidak</label>
          </div>
        </div>
      </div>

      <!-- Question 15 (Q16) -->
      <div class="question-row d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
        <div class="pe-md-4">
          <span class="fw-bold text-dark">15.</span>
          <span class="text-secondary">Apakah ditemukan orang sakit dengan gejala pembesaran kelenjar leher/ketiak/selangkangan?</span>
        </div>
        <div class="d-flex gap-4 shrink-0">
          <div class="form-check form-check-inline">
            <input class="form-check-input q-radio" type="radio" name="Q16" id="Q16_ya" value="20" onchange="calculateTotal()">
            <label class="form-check-label fw-semibold" for="Q16_ya">Ya</label>
          </div>
          <div class="form-check form-check-inline">
            <input class="form-check-input q-radio" type="radio" name="Q16" id="Q16_tidak" value="0" checked onchange="calculateTotal()">
            <label class="form-check-label fw-semibold" for="Q16_tidak">Tidak</label>
          </div>
        </div>
      </div>

      <!-- Question 16 (Q17) -->
      <div class="question-row d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
        <div class="pe-md-4">
          <span class="fw-bold text-dark">16.</span>
          <span class="text-secondary">Apakah ditemukan orang sakit dengan pendarahan spontan (hidung, gusi, muntah darah, BAB darah)?</span>
        </div>
        <div class="d-flex gap-4 shrink-0">
          <div class="form-check form-check-inline">
            <input class="form-check-input q-radio" type="radio" name="Q17" id="Q17_ya" value="20" onchange="calculateTotal()">
            <label class="form-check-label fw-semibold" for="Q17_ya">Ya</label>
          </div>
          <div class="form-check form-check-inline">
            <input class="form-check-input q-radio" type="radio" name="Q17" id="Q17_tidak" value="0" checked onchange="calculateTotal()">
            <label class="form-check-label fw-semibold" for="Q17_tidak">Tidak</label>
          </div>
        </div>
      </div>

      <!-- Question 17 (Q18) -->
      <div class="question-row d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
        <div class="pe-md-4">
          <span class="fw-bold text-dark">17.</span>
          <span class="text-secondary">Apakah ditemukan orang sakit dengan penurunan kesadaran/koma?</span>
        </div>
        <div class="d-flex gap-4 shrink-0">
          <div class="form-check form-check-inline">
            <input class="form-check-input q-radio" type="radio" name="Q18" id="Q18_ya" value="20" onchange="calculateTotal()">
            <label class="form-check-label fw-semibold" for="Q18_ya">Ya</label>
          </div>
          <div class="form-check form-check-inline">
            <input class="form-check-input q-radio" type="radio" name="Q18" id="Q18_tidak" value="0" checked onchange="calculateTotal()">
            <label class="form-check-label fw-semibold" for="Q18_tidak">Tidak</label>
          </div>
        </div>
      </div>

      <!-- Question 18 (Q19 - ICV Certificate) -->
      <div class="question-row d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 border-bottom-0">
        <div class="pe-md-4">
          <span class="fw-bold text-dark">18.</span>
          <span class="text-secondary">Apakah seluruh ABK memiliki Sertifikat Vaksinasi Internasional (ICV) yang masih berlaku?</span>
        </div>
        <div class="d-flex gap-4 shrink-0">
          <div class="form-check form-check-inline">
            <input class="form-check-input q-radio" type="radio" name="Q19" id="Q19_ya" value="0" checked onchange="calculateTotal()">
            <label class="form-check-label fw-semibold" for="Q19_ya">Ya</label>
          </div>
          <div class="form-check form-check-inline">
            <input class="form-check-input q-radio" type="radio" name="Q19" id="Q19_tidak" value="10" onchange="calculateTotal()">
            <label class="form-check-label fw-semibold" for="Q19_tidak">Tidak</label>
          </div>
        </div>
      </div>

    </div>

    <!-- Section 3: Pernyataan Nahkoda & Persetujuan -->
    <div class="card-box">
      <h5 class="fw-bold text-dark border-bottom pb-2 mb-3 d-flex align-items-center gap-2">
        <i class="fas fa-file-signature text-teal" style="color: #00A99D;"></i>
        <span>3. Pernyataan Mandiri Nahkoda Kapal</span>
      </h5>

      <div class="p-3 bg-light rounded-3 mb-3 border">
        <p class="mb-0 text-dark fw-medium" style="line-height: 1.6;">
          Saya Kapten Kapal: <strong id="captainLabel" class="text-primary">[Nama Kapal]</strong>, 
          menyatakan bahwa seluruh isian dan keterangan di atas telah sesuai dengan kondisi yang sebenarnya di kapal 
          <strong id="shipLabel" class="text-primary">[Nama Kapal]</strong>. 
          Jika di kemudian hari ditemukan ketidaksesuaian fakta kesehatan di kapal, saya bersedia bertanggung jawab sesuai dengan peraturan perundang-undangan karantina kesehatan yang berlaku di Negara Republik Indonesia.
        </p>
      </div>

      <div class="form-check form-switch ps-5 py-2">
        <input class="form-check-input" type="checkbox" role="switch" name="setuju" id="setujuCheck" value="1" onchange="toggleSubmitButton()">
        <label class="form-check-label fw-bold text-dark" for="setujuCheck">
          Saya menyatakan data dan jawaban deklarasi di atas adalah benar dan sesuai dengan kondisi kapal yang sesungguhnya.
        </label>
      </div>
    </div>

    <!-- Sticky Score & Decision Header Bar -->
    <div class="sticky-score-bar d-flex flex-wrap align-items-center justify-content-between gap-3">
      <div class="d-flex align-items-center gap-3">
        <div>
          <span class="text-muted small d-block">TOTAL SKOR</span>
          <span class="h4 fw-black text-dark mb-0 font-monospace" id="displaySkor">0</span>
        </div>
        <div class="border-start ps-3">
          <span class="text-muted small d-block">FAKTOR RISIKO</span>
          <span class="badge bg-success px-3 py-1.5 fs-6" id="badgeRisiko">RENDAH</span>
        </div>
        <div class="border-start ps-3">
          <span class="text-muted small d-block">STATUS REKOMENDASI</span>
          <span class="badge bg-primary px-3 py-1.5 fs-6" id="badgeStatus">SANDAR</span>
        </div>
      </div>

      <div class="d-flex align-items-center gap-2">
        <button 
          type="submit" 
          id="btnSubmit" 
          disabled 
          class="btn btn-lg px-4 py-2 text-white fw-bold shadow"
          style="background: linear-gradient(135deg, #005851 0%, #00A99D 100%); border-radius: 14px;"
        >
          <i class="fas fa-paper-plane me-2"></i> Kirim Form Karantina
        </button>
      </div>
    </div>

  </form>

</div>

<script>
  // Dynamic Captain & Ship Name synchronization
  const namaKapalInput = document.getElementById('namaKapal');
  const captainLabel = document.getElementById('captainLabel');
  const shipLabel = document.getElementById('shipLabel');

  namaKapalInput.addEventListener('input', function() {
    const val = this.value.trim().toUpperCase() || '[Nama Kapal]';
    captainLabel.innerText = val;
    shipLabel.innerText = val;
  });

  // Realtime Scoring Calculation Function
  function calculateTotal() {
    let total = 0;
    const questions = ['Q2','Q3','Q4','Q5','Q6','Q7','Q8','Q9','Q10','Q11','Q12','Q13','Q14','Q15','Q16','Q17','Q18','Q19'];
    
    questions.forEach(q => {
      const selected = document.querySelector(`input[name="${q}"]:checked`);
      if (selected) {
        const val = parseInt(selected.value) || 0;
        
        // Dynamic Question 8 conditional visibility
        if (q === 'Q7') {
          const wrapperQ8 = document.getElementById('wrapperQ8');
          if (val > 0) {
            wrapperQ8.classList.remove('d-none');
            wrapperQ8.classList.add('d-flex');
          } else {
            wrapperQ8.classList.add('d-none');
            wrapperQ8.classList.remove('d-flex');
            const q8Tidak = document.getElementById('Q8_tidak');
            if (q8Tidak) q8Tidak.checked = true;
          }
        }
        
        total += val;
      }
    });

    // Update Hidden Form Inputs
    document.getElementById('inputSkoring').value = total;
    document.getElementById('displaySkor').innerText = total;

    // Update Risk Level and Status
    const badgeRisiko = document.getElementById('badgeRisiko');
    const badgeStatus = document.getElementById('badgeStatus');
    const inputKresiko = document.getElementById('inputKresiko');
    const inputHasilStatus = document.getElementById('inputHasilStatus');

    if (total >= 20) {
      badgeRisiko.innerText = 'TINGGI';
      badgeRisiko.className = 'badge bg-danger px-3 py-1.5 fs-6';
      inputKresiko.value = 'TINGGI';

      badgeStatus.innerText = 'ZONA KARANTINA';
      badgeStatus.className = 'badge bg-danger px-3 py-1.5 fs-6';
      inputHasilStatus.value = 'ZONA KARANTINA';
    } else if (total >= 10) {
      badgeRisiko.innerText = 'SEDANG';
      badgeRisiko.className = 'badge bg-warning text-dark px-3 py-1.5 fs-6';
      inputKresiko.value = 'SEDANG';

      badgeStatus.innerText = 'ZONA KARANTINA';
      badgeStatus.className = 'badge bg-warning text-dark px-3 py-1.5 fs-6';
      inputHasilStatus.value = 'ZONA KARANTINA';
    } else {
      badgeRisiko.innerText = 'RENDAH';
      badgeRisiko.className = 'badge bg-success px-3 py-1.5 fs-6';
      inputKresiko.value = 'RENDAH';

      badgeStatus.innerText = 'SANDAR';
      badgeStatus.className = 'badge bg-primary px-3 py-1.5 fs-6';
      inputHasilStatus.value = 'SANDAR';
    }
  }

  // Toggle submit button based on agreement checkbox
  function toggleSubmitButton() {
    const check = document.getElementById('setujuCheck');
    const btn = document.getElementById('btnSubmit');
    btn.disabled = !check.checked;
  }

  // Initial Calculation on load
  document.addEventListener('DOMContentLoaded', function() {
    calculateTotal();
  });
</script>

</body>
</html>

