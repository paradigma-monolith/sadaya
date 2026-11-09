<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Ship Health Quarantine Assessment (MDH) &bull; BKK Bandung</title>

  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;600&display=swap" rel="stylesheet">
  
  <!-- Font Awesome & Bootstrap 5 -->
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
      background: linear-gradient(135deg, #0f172a 0%, #0369a1 100%);
      color: white;
      border-radius: 20px;
      padding: 24px 30px;
      box-shadow: 0 10px 25px rgba(3, 105, 161, 0.25);
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
      border: 2px solid #0284c7;
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
    <a href="{{ route('ship-surveillance.portal') }}" title="Back to Portal Hub">
      <img 
        src="{{ asset('storage/images/LOGO KEMENKES & KARANTINA.png') }}" 
        alt="Ministry of Health & Quarantine Authority" 
        class="h-12 sm:h-14 w-auto mx-auto object-contain drop-shadow-xs"
        style="max-height: 54px;"
        onerror="this.onerror=null;this.src='{{ asset('images/LOGO KEMENKES & KARANTINA.png') }}';"
      >
    </a>
  </div>

  <!-- Top Breadcrumb & Action -->
  <div class="d-flex justify-content-between align-items-center mb-3">
    <a href="{{ route('ship-surveillance.portal') }}" class="btn btn-outline-secondary btn-sm rounded-pill font-weight-bold">
      <i class="fas fa-arrow-left me-1"></i> Back to Portal Hub
    </a>
    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 rounded-pill font-monospace">
      <i class="fas fa-globe-americas me-1"></i> INTERNATIONAL / ABROAD
    </span>
  </div>

  <!-- Header Banner -->
  <div class="form-header-card text-center text-md-start d-md-flex align-items-center justify-content-between">
    <div class="d-flex align-items-center gap-3">
      <img 
        src="{{ asset('storage/images/LOGO KARANTINA.png') }}" 
        alt="Quarantine Logo" 
        class="d-none d-md-block h-14 w-auto object-contain drop-shadow-sm"
        style="height: 56px;"
        onerror="this.onerror=null;this.src='{{ asset('images/LOGO KARANTINA.png') }}';"
      >
      <div>
        <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill bg-white/20 text-white text-xs mb-2">
          <i class="fas fa-ship"></i>
          <span>Maritime Declaration of Health (MDH) Self-Assessment</span>
        </div>
        <h1 class="h3 fw-bold mb-1">SHIP ARRIVING FROM ABROAD</h1>
        <p class="mb-0 text-white-50 small">
          Bandung Port Health / Quarantine Authority (BKK Kelas I Bandung) &bull; WHO IHR 2005 Compliance
        </p>
      </div>
    </div>
    <div class="mt-3 mt-md-0 text-end">
      <span class="badge bg-white text-dark px-3 py-2 rounded-pill small font-monospace shadow-sm">
        <i class="fas fa-shield-alt text-primary me-1"></i> IHR 2005 Standard
      </span>
    </div>
  </div>

  @if(isset($errors) && $errors->any())
  <div class="alert alert-danger rounded-4 shadow-sm mb-4">
    <h5 class="fw-bold mb-2"><i class="fas fa-exclamation-triangle me-2"></i> Please complete required fields:</h5>
    <ul class="mb-0 ps-3">
      @foreach($errors->all() as $error)
      <li>{{ $error }}</li>
      @endforeach
    </ul>
  </div>
  @endif

  <!-- Main Form -->
  <form action="{{ route('ship-surveillance.assessment.submit') }}" method="POST" enctype="multipart/form-data" id="formQuarantineInternational">
    @csrf
    <input type="hidden" name="kategori" value="Luar Negeri">
    <input type="hidden" name="skoring" id="inputSkoring" value="0">
    <input type="hidden" name="kresiko" id="inputKresiko" value="RENDAH">
    <input type="hidden" name="hasil_status" id="inputHasilStatus" value="SANDAR">

    <!-- Section 1: Ship Particulars -->
    <div class="card-box">
      <h5 class="fw-bold text-dark border-bottom pb-2 mb-3 d-flex align-items-center gap-2">
        <i class="fas fa-ship text-primary"></i>
        <span>1. Vessel Particulars & Arrival Information</span>
      </h5>

      <div class="row g-3">
        <!-- Ship Name -->
        <div class="col-md-6">
          <label class="form-label fw-semibold text-secondary small">Ship Name / Nama Kapal <span class="text-danger">*</span></label>
          <input 
            type="text" 
            name="nama_kapal" 
            id="namaKapal" 
            required 
            placeholder="e.g. MV. PACIFIC VOYAGER" 
            class="form-control form-control-lg text-uppercase"
            value="{{ old('nama_kapal') }}"
          >
        </div>

        <!-- Last Port -->
        <div class="col-md-3">
          <label class="form-label fw-semibold text-secondary small">Last Port of Call <span class="text-danger">*</span></label>
          <input 
            type="text" 
            name="datang_dari" 
            id="datangDari" 
            required 
            placeholder="e.g. PORT OF SINGAPORE" 
            class="form-control form-control-lg text-uppercase"
            value="{{ old('datang_dari') }}"
          >
        </div>

        <!-- Country of Origin -->
        <div class="col-md-3">
          <label class="form-label fw-semibold text-secondary small">Country of Origin <span class="text-danger">*</span></label>
          <select name="origin_country" id="originCountry" class="form-select form-select-lg" required>
            <option value="">-- Select Country --</option>
            <option value="Singapore">Singapore</option>
            <option value="Malaysia">Malaysia</option>
            <option value="China">China</option>
            <option value="Japan">Japan</option>
            <option value="South Korea">South Korea</option>
            <option value="Philippines">Philippines</option>
            <option value="Thailand">Thailand</option>
            <option value="Vietnam">Vietnam</option>
            <option value="India">India</option>
            <option value="Australia">Australia</option>
            <option value="Panama">Panama</option>
            <option value="Liberia">Liberia</option>
            <option value="Marshall Islands">Marshall Islands</option>
            <option value="United States">United States</option>
            <option value="United Kingdom">United Kingdom</option>
            <option value="Netherlands">Netherlands</option>
            <option value="Germany">Germany</option>
            <option value="Hong Kong">Hong Kong</option>
            <option value="United Arab Emirates">United Arab Emirates</option>
            <option value="Saudi Arabia">Saudi Arabia</option>
            <option value="Other">Other Country</option>
          </select>
        </div>

        <!-- Destination Port in BKK Bandung -->
        <div class="col-md-4">
          <label class="form-label fw-semibold text-secondary small">Port of Destination (BKK Bandung) <span class="text-danger">*</span></label>
          <select name="pelabuhan" id="pelabuhan" class="form-select form-select-lg" required>
            <option value="">-- Select Port --</option>
            <option value="Wilayah Kerja Patimban" {{ old('pelabuhan') == 'Wilayah Kerja Patimban' ? 'selected' : '' }}>Wilayah Kerja Patimban (Subang)</option>
            <option value="Wilayah Kerja Cirebon" {{ old('pelabuhan') == 'Wilayah Kerja Cirebon' ? 'selected' : '' }}>Wilayah Kerja Cirebon</option>
            <option value="Wilayah Kerja Indramayu" {{ old('pelabuhan') == 'Wilayah Kerja Indramayu' ? 'selected' : '' }}>Wilayah Kerja Indramayu</option>
            <option value="Wilayah Kerja Palabuhanratu" {{ old('pelabuhan') == 'Wilayah Kerja Palabuhanratu' ? 'selected' : '' }}>Wilayah Kerja Palabuhanratu (Sukabumi)</option>
            <option value="Balai Kekarantinaan Kesehatan Kelas I Bandung (Induk)" {{ old('pelabuhan') == 'Balai Kekarantinaan Kesehatan Kelas I Bandung (Induk)' ? 'selected' : '' }}>BKK Kelas I Bandung (Induk)</option>
          </select>
        </div>

        <!-- Arrival Date -->
        <div class="col-md-4">
          <label class="form-label fw-semibold text-secondary small">Arrival Date <span class="text-danger">*</span></label>
          <input 
            type="date" 
            name="tanggal_kedatangan" 
            id="tanggalKedatangan" 
            required 
            class="form-control form-control-lg"
            value="{{ old('tanggal_kedatangan', date('Y-m-d')) }}"
          >
        </div>

        <!-- Gross Tonnage -->
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

        <!-- Number of Crew -->
        <div class="col-md-2">
          <label class="form-label fw-semibold text-secondary small">Number of Crew <span class="text-danger">*</span></label>
          <input 
            type="number" 
            name="jml_crew" 
            id="jmlCrew" 
            min="1" 
            required 
            placeholder="Persons" 
            class="form-control form-control-lg"
            value="{{ old('jml_crew') }}"
          >
        </div>
      </div>
    </div>

    <!-- Section 2: Health Questions (IHR 2005) -->
    <div class="card-box">
      <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
        <h5 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
          <i class="fas fa-file-medical text-primary"></i>
          <span>2. Maritime Health Declaration Questions (WHO IHR 2005)</span>
        </h5>
        <span class="small text-muted">Please answer YES or NO according to actual vessel conditions</span>
      </div>

      <!-- Question 1 -->
      <div class="question-row d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
        <div class="pe-md-4">
          <span class="fw-bold text-dark">1.</span>
          <span class="text-secondary">Have you visited any countries with reported infectious disease outbreaks in the last 14 days?</span>
        </div>
        <div class="d-flex gap-4 shrink-0">
          <div class="form-check form-check-inline">
            <input class="form-check-input q-radio" type="radio" name="Q1" id="Q1_ya" value="20" onchange="calculateTotal()">
            <label class="form-check-label fw-semibold" for="Q1_ya">Yes</label>
          </div>
          <div class="form-check form-check-inline">
            <input class="form-check-input q-radio" type="radio" name="Q1" id="Q1_tidak" value="0" checked onchange="calculateTotal()">
            <label class="form-check-label fw-semibold" for="Q1_tidak">No</label>
          </div>
        </div>
      </div>

      <!-- Question 2 -->
      <div class="question-row d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
        <div class="pe-md-4">
          <span class="fw-bold text-dark">2.</span>
          <span class="text-secondary">Has any person died on board during the voyage other than as a result of accident?</span>
        </div>
        <div class="d-flex gap-4 shrink-0">
          <div class="form-check form-check-inline">
            <input class="form-check-input q-radio" type="radio" name="Q2" id="Q2_ya" value="20" onchange="calculateTotal()">
            <label class="form-check-label fw-semibold" for="Q2_ya">Yes</label>
          </div>
          <div class="form-check form-check-inline">
            <input class="form-check-input q-radio" type="radio" name="Q2" id="Q2_tidak" value="0" checked onchange="calculateTotal()">
            <label class="form-check-label fw-semibold" for="Q2_tidak">No</label>
          </div>
        </div>
      </div>

      <!-- Question 3 -->
      <div class="question-row d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
        <div class="pe-md-4">
          <span class="fw-bold text-dark">3.</span>
          <span class="text-secondary">Was any person evacuated from the ship during the voyage due to illness or death?</span>
        </div>
        <div class="d-flex gap-4 shrink-0">
          <div class="form-check form-check-inline">
            <input class="form-check-input q-radio" type="radio" name="Q3" id="Q3_ya" value="20" onchange="calculateTotal()">
            <label class="form-check-label fw-semibold" for="Q3_ya">Yes</label>
          </div>
          <div class="form-check form-check-inline">
            <input class="form-check-input q-radio" type="radio" name="Q3" id="Q3_tidak" value="0" checked onchange="calculateTotal()">
            <label class="form-check-label fw-semibold" for="Q3_tidak">No</label>
          </div>
        </div>
      </div>

      <!-- Question 4 -->
      <div class="question-row d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
        <div class="pe-md-4">
          <span class="fw-bold text-dark">4.</span>
          <span class="text-secondary">Is there any person on board suffering from an illness that is suspected of being an infectious disease?</span>
        </div>
        <div class="d-flex gap-4 shrink-0">
          <div class="form-check form-check-inline">
            <input class="form-check-input q-radio" type="radio" name="Q4" id="Q4_ya" value="20" onchange="calculateTotal()">
            <label class="form-check-label fw-semibold" for="Q4_ya">Yes</label>
          </div>
          <div class="form-check form-check-inline">
            <input class="form-check-input q-radio" type="radio" name="Q4" id="Q4_tidak" value="0" checked onchange="calculateTotal()">
            <label class="form-check-label fw-semibold" for="Q4_tidak">No</label>
          </div>
        </div>
      </div>

      <!-- Question 5 -->
      <div class="question-row d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
        <div class="pe-md-4">
          <span class="fw-bold text-dark">5.</span>
          <span class="text-secondary">Is the number of sick persons currently on board greater than normal?</span>
        </div>
        <div class="d-flex gap-4 shrink-0">
          <div class="form-check form-check-inline">
            <input class="form-check-input q-radio" type="radio" name="Q5" id="Q5_ya" value="10" onchange="calculateTotal()">
            <label class="form-check-label fw-semibold" for="Q5_ya">Yes</label>
          </div>
          <div class="form-check form-check-inline">
            <input class="form-check-input q-radio" type="radio" name="Q5" id="Q5_tidak" value="0" checked onchange="calculateTotal()">
            <label class="form-check-label fw-semibold" for="Q5_tidak">No</label>
          </div>
        </div>
      </div>

      <!-- Question 6 -->
      <div class="question-row d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
        <div class="pe-md-4">
          <span class="fw-bold text-dark">6.</span>
          <span class="text-secondary">Are there any sick persons on board now?</span>
        </div>
        <div class="d-flex gap-4 shrink-0">
          <div class="form-check form-check-inline">
            <input class="form-check-input q-radio" type="radio" name="Q6" id="Q6_ya" value="10" onchange="calculateTotal()">
            <label class="form-check-label fw-semibold" for="Q6_ya">Yes</label>
          </div>
          <div class="form-check form-check-inline">
            <input class="form-check-input q-radio" type="radio" name="Q6" id="Q6_tidak" value="0" checked onchange="calculateTotal()">
            <label class="form-check-label fw-semibold" for="Q6_tidak">No</label>
          </div>
        </div>
      </div>

      <!-- Question 7 -->
      <div class="question-row d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
        <div class="pe-md-4">
          <span class="fw-bold text-dark">7.</span>
          <span class="text-secondary">Was a medical practitioner or doctor consulted?</span>
        </div>
        <div class="d-flex gap-4 shrink-0">
          <div class="form-check form-check-inline">
            <input class="form-check-input q-radio" type="radio" name="Q7" id="Q7_ya" value="10" onchange="calculateTotal()">
            <label class="form-check-label fw-semibold" for="Q7_ya">Yes</label>
          </div>
          <div class="form-check form-check-inline">
            <input class="form-check-input q-radio" type="radio" name="Q7" id="Q7_tidak" value="0" checked onchange="calculateTotal()">
            <label class="form-check-label fw-semibold" for="Q7_tidak">No</label>
          </div>
        </div>
      </div>

      <!-- Question 8 (Conditional) -->
      <div id="wrapperQ8" class="question-row d-none bg-warning-subtle/30 justify-content-between align-items-md-center gap-2 ps-3 border-start border-warning border-3">
        <div class="pe-md-4">
          <span class="fw-bold text-dark">8.</span>
          <span class="text-secondary">Are you aware of any condition on board which may lead to infection or the spread of disease?</span>
        </div>
        <div class="d-flex gap-4 shrink-0">
          <div class="form-check form-check-inline">
            <input class="form-check-input q-radio" type="radio" name="Q8" id="Q8_ya" value="20" onchange="calculateTotal()">
            <label class="form-check-label fw-semibold" for="Q8_ya">Yes</label>
          </div>
          <div class="form-check form-check-inline">
            <input class="form-check-input q-radio" type="radio" name="Q8" id="Q8_tidak" value="0" checked onchange="calculateTotal()">
            <label class="form-check-label fw-semibold" for="Q8_tidak">No</label>
          </div>
        </div>
      </div>

      <!-- Question 9 to 18 (Clinical Signs) -->
      <div class="question-row d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
        <div class="pe-md-4">
          <span class="fw-bold text-dark">9.</span>
          <span class="text-secondary">Has any sanitary measure (disinfection, disinsection, deratting) been applied on board during the voyage?</span>
        </div>
        <div class="d-flex gap-4 shrink-0">
          <div class="form-check form-check-inline">
            <input class="form-check-input q-radio" type="radio" name="Q9" id="Q9_ya" value="5" onchange="calculateTotal()">
            <label class="form-check-label fw-semibold" for="Q9_ya">Yes</label>
          </div>
          <div class="form-check form-check-inline">
            <input class="form-check-input q-radio" type="radio" name="Q9" id="Q9_tidak" value="0" checked onchange="calculateTotal()">
            <label class="form-check-label fw-semibold" for="Q9_tidak">No</label>
          </div>
        </div>
      </div>

      <div class="question-row d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
        <div class="pe-md-4">
          <span class="fw-bold text-dark">10.</span>
          <span class="text-secondary">Have any disease vectors (rodents, fleas, mosquitoes) been found on board?</span>
        </div>
        <div class="d-flex gap-4 shrink-0">
          <div class="form-check form-check-inline">
            <input class="form-check-input q-radio" type="radio" name="Q10" id="Q10_ya" value="10" onchange="calculateTotal()">
            <label class="form-check-label fw-semibold" for="Q10_ya">Yes</label>
          </div>
          <div class="form-check form-check-inline">
            <input class="form-check-input q-radio" type="radio" name="Q10" id="Q10_tidak" value="0" checked onchange="calculateTotal()">
            <label class="form-check-label fw-semibold" for="Q10_tidak">No</label>
          </div>
        </div>
      </div>

      <div class="question-row d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
        <div class="pe-md-4">
          <span class="fw-bold text-dark">11.</span>
          <span class="text-secondary">Fever (&ge; 38&deg;C) with acute respiratory distress, cough, or shortness of breath?</span>
        </div>
        <div class="d-flex gap-4 shrink-0">
          <div class="form-check form-check-inline">
            <input class="form-check-input q-radio" type="radio" name="Q11" id="Q11_ya" value="20" onchange="calculateTotal()">
            <label class="form-check-label fw-semibold" for="Q11_ya">Yes</label>
          </div>
          <div class="form-check form-check-inline">
            <input class="form-check-input q-radio" type="radio" name="Q11" id="Q11_tidak" value="0" checked onchange="calculateTotal()">
            <label class="form-check-label fw-semibold" for="Q11_tidak">No</label>
          </div>
        </div>
      </div>

      <div class="question-row d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
        <div class="pe-md-4">
          <span class="fw-bold text-dark">12.</span>
          <span class="text-secondary">Acute skin rash or eruptive lesions?</span>
        </div>
        <div class="d-flex gap-4 shrink-0">
          <div class="form-check form-check-inline">
            <input class="form-check-input q-radio" type="radio" name="Q12" id="Q12_ya" value="20" onchange="calculateTotal()">
            <label class="form-check-label fw-semibold" for="Q12_ya">Yes</label>
          </div>
          <div class="form-check form-check-inline">
            <input class="form-check-input q-radio" type="radio" name="Q12" id="Q12_tidak" value="0" checked onchange="calculateTotal()">
            <label class="form-check-label fw-semibold" for="Q12_tidak">No</label>
          </div>
        </div>
      </div>

      <div class="question-row d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
        <div class="pe-md-4">
          <span class="fw-bold text-dark">13.</span>
          <span class="text-secondary">Severe / acute diarrhea?</span>
        </div>
        <div class="d-flex gap-4 shrink-0">
          <div class="form-check form-check-inline">
            <input class="form-check-input q-radio" type="radio" name="Q13" id="Q13_ya" value="20" onchange="calculateTotal()">
            <label class="form-check-label fw-semibold" for="Q13_ya">Yes</label>
          </div>
          <div class="form-check form-check-inline">
            <input class="form-check-input q-radio" type="radio" name="Q13" id="Q13_tidak" value="0" checked onchange="calculateTotal()">
            <label class="form-check-label fw-semibold" for="Q13_tidak">No</label>
          </div>
        </div>
      </div>

      <div class="question-row d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
        <div class="pe-md-4">
          <span class="fw-bold text-dark">14.</span>
          <span class="text-secondary">Persistent vomiting?</span>
        </div>
        <div class="d-flex gap-4 shrink-0">
          <div class="form-check form-check-inline">
            <input class="form-check-input q-radio" type="radio" name="Q14" id="Q14_ya" value="20" onchange="calculateTotal()">
            <label class="form-check-label fw-semibold" for="Q14_ya">Yes</label>
          </div>
          <div class="form-check form-check-inline">
            <input class="form-check-input q-radio" type="radio" name="Q14" id="Q14_tidak" value="0" checked onchange="calculateTotal()">
            <label class="form-check-label fw-semibold" for="Q14_tidak">No</label>
          </div>
        </div>
      </div>

      <div class="question-row d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
        <div class="pe-md-4">
          <span class="fw-bold text-dark">15.</span>
          <span class="text-secondary">Jaundice (yellow skin or eyes)?</span>
        </div>
        <div class="d-flex gap-4 shrink-0">
          <div class="form-check form-check-inline">
            <input class="form-check-input q-radio" type="radio" name="Q15" id="Q15_ya" value="20" onchange="calculateTotal()">
            <label class="form-check-label fw-semibold" for="Q15_ya">Yes</label>
          </div>
          <div class="form-check form-check-inline">
            <input class="form-check-input q-radio" type="radio" name="Q15" id="Q15_tidak" value="0" checked onchange="calculateTotal()">
            <label class="form-check-label fw-semibold" for="Q15_tidak">No</label>
          </div>
        </div>
      </div>

      <div class="question-row d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
        <div class="pe-md-4">
          <span class="fw-bold text-dark">16.</span>
          <span class="text-secondary">Glandular swelling (lymph nodes in neck, armpit, groin)?</span>
        </div>
        <div class="d-flex gap-4 shrink-0">
          <div class="form-check form-check-inline">
            <input class="form-check-input q-radio" type="radio" name="Q16" id="Q16_ya" value="20" onchange="calculateTotal()">
            <label class="form-check-label fw-semibold" for="Q16_ya">Yes</label>
          </div>
          <div class="form-check form-check-inline">
            <input class="form-check-input q-radio" type="radio" name="Q16" id="Q16_tidak" value="0" checked onchange="calculateTotal()">
            <label class="form-check-label fw-semibold" for="Q16_tidak">No</label>
          </div>
        </div>
      </div>

      <div class="question-row d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
        <div class="pe-md-4">
          <span class="fw-bold text-dark">17.</span>
          <span class="text-secondary">Spontaneous bleeding (nose, gums, blood vomiting, bloody stool)?</span>
        </div>
        <div class="d-flex gap-4 shrink-0">
          <div class="form-check form-check-inline">
            <input class="form-check-input q-radio" type="radio" name="Q17" id="Q17_ya" value="20" onchange="calculateTotal()">
            <label class="form-check-label fw-semibold" for="Q17_ya">Yes</label>
          </div>
          <div class="form-check form-check-inline">
            <input class="form-check-input q-radio" type="radio" name="Q17" id="Q17_tidak" value="0" checked onchange="calculateTotal()">
            <label class="form-check-label fw-semibold" for="Q17_tidak">No</label>
          </div>
        </div>
      </div>

      <div class="question-row d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
        <div class="pe-md-4">
          <span class="fw-bold text-dark">18.</span>
          <span class="text-secondary">Loss of consciousness or coma?</span>
        </div>
        <div class="d-flex gap-4 shrink-0">
          <div class="form-check form-check-inline">
            <input class="form-check-input q-radio" type="radio" name="Q18" id="Q18_ya" value="20" onchange="calculateTotal()">
            <label class="form-check-label fw-semibold" for="Q18_ya">Yes</label>
          </div>
          <div class="form-check form-check-inline">
            <input class="form-check-input q-radio" type="radio" name="Q18" id="Q18_tidak" value="0" checked onchange="calculateTotal()">
            <label class="form-check-label fw-semibold" for="Q18_tidak">No</label>
          </div>
        </div>
      </div>

      <div class="question-row d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 border-bottom-0">
        <div class="pe-md-4">
          <span class="fw-bold text-dark">19.</span>
          <span class="text-secondary">Do all crew members hold valid International Certificates of Vaccination (ICV)?</span>
        </div>
        <div class="d-flex gap-4 shrink-0">
          <div class="form-check form-check-inline">
            <input class="form-check-input q-radio" type="radio" name="Q19" id="Q19_ya" value="0" checked onchange="calculateTotal()">
            <label class="form-check-label fw-semibold" for="Q19_ya">Yes</label>
          </div>
          <div class="form-check form-check-inline">
            <input class="form-check-input q-radio" type="radio" name="Q19" id="Q19_tidak" value="10" onchange="calculateTotal()">
            <label class="form-check-label fw-semibold" for="Q19_tidak">No</label>
          </div>
        </div>
      </div>

    </div>

    <!-- Section 3: Document Checklist & Statement Upload -->
    <div class="card-box">
      <h5 class="fw-bold text-dark border-bottom pb-2 mb-3 d-flex align-items-center gap-2">
        <i class="fas fa-file-upload text-primary"></i>
        <span>3. Document Readiness & Master's Statement Letter</span>
      </h5>

      <!-- Upload Master's Statement Letter -->
      <div class="p-3 bg-light rounded-3 mb-4 border">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-2">
          <label class="form-label fw-bold text-dark mb-1">
            <i class="fas fa-file-signature text-primary me-1"></i> Upload Master's Statement Letter <span class="text-danger">*</span>
          </label>
          <a href="https://selfassessment.bkkbandung.net/SuratPernyataanNahkoda.doc" download class="btn btn-sm btn-outline-primary rounded-pill">
            <i class="fas fa-download me-1"></i> Download Letter Template (.doc)
          </a>
        </div>
        <input 
          type="file" 
          name="surat_pernyataan_nahkoda" 
          id="fileSurat" 
          required 
          accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
          class="form-control"
        >
        <div class="form-text small text-muted">
          Allowed file formats: PDF, PNG, JPG, JPEG, DOCX (Max: 5MB).
        </div>
      </div>

      <!-- Document Checklists -->
      <div class="row g-2 mb-3">
        <div class="col-12"><span class="fw-semibold text-secondary small">Mandatory Vessel Quarantine Documents:</span></div>
        <div class="col-md-6">
          <div class="form-check">
            <input class="form-check-input" type="checkbox" name="checklist_dokumen[mdh]" id="x1" value="1" checked>
            <label class="form-check-label small" for="x1">Maritime Declaration of Health (MDH)</label>
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-check">
            <input class="form-check-input" type="checkbox" name="checklist_dokumen[port_clearance]" id="x2" value="1" checked>
            <label class="form-check-label small" for="x2">Port Clearance from Last Port</label>
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-check">
            <input class="form-check-input" type="checkbox" name="checklist_dokumen[sscec]" id="x3" value="1" checked>
            <label class="form-check-label small" for="x3">Ship Sanitation Certificate (SSCC / SSCEC)</label>
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-check">
            <input class="form-check-input" type="checkbox" name="checklist_dokumen[crew_temp]" id="x4" value="1" checked>
            <label class="form-check-label small" for="x4">Crew List & Temperature Log Results</label>
          </div>
        </div>
      </div>

      <!-- Master Declaration Text -->
      <div class="p-3 bg-light rounded-3 mb-3 border">
        <p class="mb-0 text-dark fw-medium" style="line-height: 1.6;">
          I, Captain of the Ship: <strong id="captainLabel" class="text-primary">[Ship Name]</strong>, 
          declare that the above entries are true and in accordance with the actual conditions on the ship 
          <strong id="shipLabel" class="text-primary">[Ship Name]</strong>. 
          If there are any differences or discrepancies, I am responsible according to the applicable laws and quarantine regulations in Indonesia.
        </p>
      </div>

      <div class="form-check form-switch ps-5 py-2">
        <input class="form-check-input" type="checkbox" role="switch" name="setuju" id="setujuCheck" value="1" onchange="toggleSubmitButton()">
        <label class="form-check-label fw-bold text-dark" for="setujuCheck">
          I confirm that all statements and clinical declarations above are true and complete.
        </label>
      </div>
    </div>

    <!-- Sticky Score Bar -->
    <div class="sticky-score-bar d-flex flex-wrap align-items-center justify-content-between gap-3">
      <div class="d-flex align-items-center gap-3">
        <div>
          <span class="text-muted small d-block">TOTAL SCORE</span>
          <span class="h4 fw-black text-dark mb-0 font-monospace" id="displaySkor">0</span>
        </div>
        <div class="border-start ps-3">
          <span class="text-muted small d-block">RISK LEVEL</span>
          <span class="badge bg-success px-3 py-1.5 fs-6" id="badgeRisiko">LOW</span>
        </div>
        <div class="border-start ps-3">
          <span class="text-muted small d-block">RECOMMENDED STATUS</span>
          <span class="badge bg-primary px-3 py-1.5 fs-6" id="badgeStatus">ANCHORED</span>
        </div>
      </div>

      <div class="d-flex align-items-center gap-2">
        <button 
          type="submit" 
          id="btnSubmit" 
          disabled 
          class="btn btn-lg px-4 py-2 text-white fw-bold shadow"
          style="background: linear-gradient(135deg, #0f172a 0%, #0284c7 100%); border-radius: 14px;"
        >
          <i class="fas fa-paper-plane me-2"></i> Submit Quarantine Assessment
        </button>
      </div>
    </div>

  </form>

</div>

<script>
  // Sync Ship & Captain Name
  const namaKapalInput = document.getElementById('namaKapal');
  const captainLabel = document.getElementById('captainLabel');
  const shipLabel = document.getElementById('shipLabel');

  namaKapalInput.addEventListener('input', function() {
    const val = this.value.trim().toUpperCase() || '[Ship Name]';
    captainLabel.innerText = val;
    shipLabel.innerText = val;
  });

  // Calculate Total Score
  function calculateTotal() {
    let total = 0;
    const questions = ['Q1','Q2','Q3','Q4','Q5','Q6','Q7','Q8','Q9','Q10','Q11','Q12','Q13','Q14','Q15','Q16','Q17','Q18','Q19'];
    
    questions.forEach(q => {
      const selected = document.querySelector(`input[name="${q}"]:checked`);
      if (selected) {
        const val = parseInt(selected.value) || 0;
        
        // Dynamic Question 8 visibility based on Q7
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

    // Update Badges
    const badgeRisiko = document.getElementById('badgeRisiko');
    const badgeStatus = document.getElementById('badgeStatus');
    const inputKresiko = document.getElementById('inputKresiko');
    const inputHasilStatus = document.getElementById('inputHasilStatus');

    if (total >= 20) {
      badgeRisiko.innerText = 'HIGH';
      badgeRisiko.className = 'badge bg-danger px-3 py-1.5 fs-6';
      inputKresiko.value = 'TINGGI';

      badgeStatus.innerText = 'QUARANTINE';
      badgeStatus.className = 'badge bg-danger px-3 py-1.5 fs-6';
      inputHasilStatus.value = 'ZONA KARANTINA';
    } else if (total >= 10) {
      badgeRisiko.innerText = 'MEDIUM';
      badgeRisiko.className = 'badge bg-warning text-dark px-3 py-1.5 fs-6';
      inputKresiko.value = 'SEDANG';

      badgeStatus.innerText = 'QUARANTINE';
      badgeStatus.className = 'badge bg-warning text-dark px-3 py-1.5 fs-6';
      inputHasilStatus.value = 'ZONA KARANTINA';
    } else {
      badgeRisiko.innerText = 'LOW';
      badgeRisiko.className = 'badge bg-success px-3 py-1.5 fs-6';
      inputKresiko.value = 'RENDAH';

      badgeStatus.innerText = 'ANCHORED';
      badgeStatus.className = 'badge bg-primary px-3 py-1.5 fs-6';
      inputHasilStatus.value = 'SANDAR';
    }
  }

  function toggleSubmitButton() {
    const check = document.getElementById('setujuCheck');
    const btn = document.getElementById('btnSubmit');
    btn.disabled = !check.checked;
  }

  document.addEventListener('DOMContentLoaded', function() {
    calculateTotal();
  });
</script>

</body>
</html>

