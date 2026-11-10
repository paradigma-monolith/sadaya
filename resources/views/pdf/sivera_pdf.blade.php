<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>{{ $title ?? 'Laporan Kegiatan Pengendalian Vektor SIVERA' }}</title>
  <style>
    @page {
      margin: 1.2cm 2cm 1.5cm 2cm;
      size: A4 portrait;
    }
    body {
      font-family: Arial, Helvetica, sans-serif;
      font-size: 11pt;
      line-height: 1.45;
      color: #000;
      margin: 0;
      padding: 0;
    }
    .kop-container {
      width: 100%;
      text-align: center;
      margin-bottom: 15px;
    }
    .kop-img {
      width: 100%;
      max-height: 115px;
    }
    .title-block {
      text-align: center;
      margin-top: 10px;
      margin-bottom: 22px;
    }
    .title-block .line-1 {
      font-size: 11pt;
      letter-spacing: 0.5px;
      margin: 0 0 4px 0;
    }
    .title-block .line-2 {
      font-size: 11pt;
      font-weight: bold;
      letter-spacing: 0.5px;
      margin: 0 0 4px 0;
    }
    .title-block .line-3 {
      font-size: 11pt;
      letter-spacing: 0.5px;
      margin: 0;
    }
    .table-info {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 12px;
    }
    .table-info td {
      padding: 3px 0;
      vertical-align: top;
      font-size: 11pt;
    }
    .col-label {
      width: 170px;
      white-space: nowrap;
    }
    .col-colon {
      width: 18px;
      text-align: left;
    }
    .col-val {
      text-align: left;
    }
    .section-title {
      font-size: 11pt;
      font-weight: bold;
      margin-top: 14px;
      margin-bottom: 6px;
    }
    .bullet-list {
      margin: 0 0 12px 0;
      padding-left: 20px;
    }
    .bullet-list li {
      margin-bottom: 3px;
      font-size: 11pt;
    }
    .table-data {
      width: 100%;
      border-collapse: collapse;
      margin-top: 8px;
      margin-bottom: 14px;
      font-size: 10pt;
    }
    .table-data th, .table-data td {
      border: 1px solid #000;
      padding: 5px 7px;
      text-align: center;
    }
    .table-data th {
      background-color: #f2f2f2;
      font-weight: bold;
    }
    .table-data td.text-left {
      text-align: left;
    }
    .table-pelaksana {
      width: 100%;
      border-collapse: collapse;
      border: 1px solid #000;
      margin-top: 25px;
    }
    .table-pelaksana td {
      border: 1px solid #000;
      width: 50%;
      vertical-align: top;
      padding: 10px 14px;
    }
    .box-left {
      height: 120px;
      font-size: 10pt;
      color: #333;
    }
    .box-right-header {
      font-size: 11pt;
      margin-bottom: 75px;
    }
    .box-right-name {
      font-size: 11pt;
      font-weight: bold;
    }
    .badge {
      display: inline-block;
      padding: 2px 6px;
      font-weight: bold;
      font-size: 10pt;
      border: 1px solid #000;
    }
  </style>
</head>
<body>

  <!-- Kop Surat Resmi -->
  <div class="kop-container">
    @if(!empty($kop))
      <img src="{{ $kop }}" class="kop-img" alt="Kop Surat BKK Bandung">
    @else
      <div style="font-size: 12pt; font-weight: bold; text-transform: uppercase;">KEMENTERIAN KESEHATAN REPUBLIK INDONESIA</div>
      <div style="font-size: 14pt; font-weight: bold; text-transform: uppercase;">BALAI KEKARANTINAAN KESEHATAN KELAS I BANDUNG</div>
      <div style="font-size: 9pt; border-bottom: 2px solid #000; padding-bottom: 6px; margin-top: 2px;">Substansi Pengendalian Risiko Lingkungan &bull; Surveilans Vektor dan Reservoar (SIVERA)</div>
    @endif
  </div>

  <!-- Judul Laporan Sesuai Format Baku Simply CI4 -->
  <div class="title-block">
    <div class="line-1">LAPORAN KEGIATAN</div>
    <div class="line-2">{{ strtoupper($judul_kegiatan ?? 'PENGENDALIAN VEKTOR') }}</div>
    <div class="line-3">BALAI KEKARANTINAAN KESEHATAN KELAS I BANDUNG</div>
  </div>

  <!-- Info Kegiatan -->
  <table class="table-info">
    <tr>
      <td class="col-label">Wilayah Kerja</td>
      <td class="col-colon">:</td>
      <td class="col-val">{{ $wilker ?? '-' }}</td>
    </tr>
    <tr>
      <td class="col-label">Hari, Tanggal</td>
      <td class="col-colon">:</td>
      <td class="col-val">{{ $hari_tanggal ?? '-' }}</td>
    </tr>
    <tr>
      <td class="col-label">Lokasi</td>
      <td class="col-colon">:</td>
      <td class="col-val">{{ $lokasi ?? '-' }}</td>
    </tr>
    <tr>
      <td class="col-label">Persiapan</td>
      <td class="col-colon">:</td>
      <td class="col-val">{{ $persiapan ?? 'Persiapan alat perlindungan diri (APD), bahan pengendali, dan koordinasi lokasi surveilans.' }}</td>
    </tr>
  </table>

  <!-- Alat dan Bahan -->
  <div class="section-title">Alat dan Bahan</div>
  @if(!empty($alat_dan_bahan_split))
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 10px;">
      <tr>
        <td style="width: 50%; vertical-align: top;">
          <ul class="bullet-list">
            @foreach($alat_dan_bahan_split[0] as $ab)
              <li>{{ $ab }}</li>
            @endforeach
          </ul>
        </td>
        <td style="width: 50%; vertical-align: top;">
          <ul class="bullet-list">
            @foreach($alat_dan_bahan_split[1] as $ab)
              <li>{{ $ab }}</li>
            @endforeach
          </ul>
        </td>
      </tr>
    </table>
  @elseif(!empty($alat_dan_bahan))
    <ul class="bullet-list">
      @foreach($alat_dan_bahan as $ab)
        <li>{{ $ab }}</li>
      @endforeach
    </ul>
  @endif

  <!-- Pelaksanaan -->
  <div class="section-title">Pelaksanaan</div>
  <table class="table-info">
    <tr>
      <td class="col-label">Hasil Survei Vektor</td>
      <td class="col-colon">:</td>
      <td class="col-val"><strong>{{ $hasil_survei ?? '-' }}</strong></td>
    </tr>
    <tr>
      <td class="col-label">Pengendalian</td>
      <td class="col-colon">:</td>
      <td class="col-val">{{ $metode_pengendalian ?? 'Kimia/ Fisika/ Biologi' }}</td>
    </tr>
    <tr>
      <td class="col-label">Jumlah insektisida</td>
      <td class="col-colon">:</td>
      <td class="col-val">{{ $jumlah_insektisida ?? '-' }}</td>
    </tr>
    @if(!empty($penggunaan_solar))
    <tr>
      <td class="col-label">Penggunaan Solar</td>
      <td class="col-colon">:</td>
      <td class="col-val">{{ $penggunaan_solar }}</td>
    </tr>
    @endif
    @if(!empty($penggunaan_bensin))
    <tr>
      <td class="col-label">Penggunaan Bensin</td>
      <td class="col-colon">:</td>
      <td class="col-val">{{ $penggunaan_bensin }}</td>
    </tr>
    @endif
    @if(!empty($catatan_pengendalian))
    <tr>
      <td class="col-label">Catatan Intervensi</td>
      <td class="col-colon">:</td>
      <td class="col-val">{{ $catatan_pengendalian }}</td>
    </tr>
    @endif
  </table>

  <!-- Detail Data Spesifik Vektor (Tabel Rincian Tambahan) -->
  @if(!empty($tabel_lokasi_lalat))
    <div class="section-title" style="margin-top: 10px; font-size: 10pt;">Rincian Pengamatan Fly Grill per Titik Lokasi (Top 5 &amp; Rata-Rata):</div>
    <table class="table-data">
      <thead>
        <tr>
          <th style="width: 30px;">No</th>
          <th class="text-left">Titik Lokasi</th>
          <th>T1 - T10 Pengamatan (30 Detik)</th>
          <th style="width: 60px;">Total N</th>
          <th style="width: 75px;">Fly Index</th>
          <th style="width: 70px;">Status</th>
        </tr>
      </thead>
      <tbody>
        @foreach($tabel_lokasi_lalat as $i => $lok)
          <tr>
            <td>{{ $i + 1 }}</td>
            <td class="text-left font-bold">{{ $lok['lokasi'] ?? '-' }}</td>
            <td>{{ implode(', ', $lok['t'] ?? []) }}</td>
            <td>{{ $lok['total_n'] ?? '-' }}</td>
            <td><strong>{{ $lok['rata_rata'] ?? '-' }}</strong></td>
            <td>{{ $lok['status'] ?? ($lok['rata_rata'] < 2 ? 'MS' : 'TMS') }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>
  @endif

  @if(!empty($tabel_anopheles_12jam))
    <div class="section-title" style="margin-top: 10px; font-size: 10pt;">Matriks Penangkapan Nyamuk Anopheles 12 Jam (18.00 - 06.00 WIB):</div>
    <table class="table-data">
      <thead>
        <tr>
          <th>Jam Penangkapan</th>
          <th>Dalam Rumah (D)</th>
          <th>Luar Rumah (L)</th>
          <th>Total Jam</th>
        </tr>
      </thead>
      <tbody>
        @foreach($tabel_anopheles_12jam as $slot)
          <tr>
            <td class="text-left">{{ $slot['label'] }}</td>
            <td>{{ $slot['dalam'] }}</td>
            <td>{{ $slot['luar'] }}</td>
            <td><strong>{{ $slot['total'] }}</strong></td>
          </tr>
        @endforeach
        <tr style="background-color: #f2f2f2; font-weight: bold;">
          <td class="text-left">TOTAL TERTANGKAP</td>
          <td>{{ $anopheles_total_dalam ?? 0 }} ekor</td>
          <td>{{ $anopheles_total_luar ?? 0 }} ekor</td>
          <td>{{ $anopheles_total_semua ?? 0 }} ekor</td>
        </tr>
      </tbody>
    </table>
  @endif

  <!-- Pelaksana Kegiatan Box -->
  <table class="table-pelaksana">
    <tr>
      <td class="box-left">
        <strong>Catatan / Dokumentasi Lapangan:</strong><br>
        <span style="font-size: 9.5pt; color: #555;">
          Tindakan pengendalian telah dilaksanakan sesuai standar operasional prosedur Permenkes No. 50 Tahun 2017 tentang Baku Mutu Kesehatan Lingkungan dan Vektor Pembawa Penyakit.
        </span>
      </td>
      <td>
        <div class="box-right-header">
          Pelaksana Kegiatan,<br>
          Petugas Karantina Kesehatan
        </div>
        <div class="box-right-name">{{ $nama_pegawai ?? 'Petugas Pengendalian' }}</div>
        <div style="font-size: 9.5pt; color: #555; margin-top: 2px;">BKK Kelas I Bandung</div>
      </td>
    </tr>
  </table>

</body>
</html>
