<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>{{ $title }}</title>
  <style>
    body {
      font-family: Arial, Helvetica, sans-serif;
      font-size: 11px;
      line-height: 1.4;
      color: #1e293b;
      margin: 15px 20px;
    }
    .header-table {
      width: 100%;
      border-bottom: 2px solid #005851;
      padding-bottom: 10px;
      margin-bottom: 15px;
    }
    .title {
      text-align: center;
      font-size: 14px;
      font-weight: bold;
      color: #005851;
      text-transform: uppercase;
      margin-bottom: 4px;
    }
    .subtitle {
      text-align: center;
      font-size: 10px;
      color: #64748b;
      margin-bottom: 15px;
    }
    .info-table {
      width: 100%;
      margin-bottom: 15px;
      border-collapse: collapse;
    }
    .info-table td {
      padding: 3px 6px;
      vertical-align: top;
    }
    .data-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 15px;
    }
    .data-table th, .data-table td {
      border: 1px solid #cbd5e1;
      padding: 6px 8px;
    }
    .data-table th {
      background-color: #f1f5f9;
      color: #0f172a;
      font-weight: bold;
      text-align: left;
    }
    .badge {
      display: inline-block;
      padding: 3px 8px;
      border-radius: 4px;
      font-weight: bold;
      font-size: 10px;
      color: #fff;
    }
    .badge-success { background-color: #10b981; }
    .badge-warning { background-color: #f59e0b; }
    .badge-danger { background-color: #ef4444; }
    .footer-table {
      width: 100%;
      margin-top: 30px;
    }
  </style>
</head>
<body>

  <table class="header-table">
    <tr>
      <td style="width: 100%; text-align: center;">
        <div style="font-size: 13px; font-weight: bold; color: #005851; text-transform: uppercase;">KEMENTERIAN KESEHATAN REPUBLIK INDONESIA</div>
        <div style="font-size: 15px; font-weight: 800; color: #00A99D; text-transform: uppercase;">BALAI KEKARANTINAAN KESEHATAN KELAS I BANDUNG</div>
        <div style="font-size: 9px; color: #64748b; margin-top: 2px;">Sistem Informasi Satu Pintu Sadaya &bull; Substansi Pengendalian Risiko Lingkungan (PRL)</div>
      </td>
    </tr>
  </table>

  <div class="title">{{ $title }}</div>
  <div class="subtitle">Nomor Registrasi Sistem: PRL-{{ strtoupper($type) }}-{{ str_pad($item->id, 5, '0', STR_PAD_LEFT) }}</div>

  <table class="info-table">
    <tr>
      <td style="width: 25%; font-weight: bold;">Wilayah Kerja</td>
      <td style="width: 2%;">:</td>
      <td style="width: 73%;">{{ $item->wilker ?: '-' }}</td>
    </tr>
    <tr>
      <td style="font-weight: bold;">Tanggal Inspeksi</td>
      <td>:</td>
      <td>{{ $item->tanggal_inspeksi ? $item->tanggal_inspeksi->translatedFormat('d F Y') : '-' }}</td>
    </tr>
    <tr>
      <td style="font-weight: bold;">Nama Sarana / Gedung</td>
      <td>:</td>
      <td style="font-weight: bold; color: #005851;">{{ $item->nama_tpp ?? ($item->nama_sab ?? ($item->nama_gedung ?? '-')) }}</td>
    </tr>
    <tr>
      <td style="font-weight: bold;">Penanggung Jawab</td>
      <td>:</td>
      <td>{{ $item->penanggung_jawab_tpp ?? ($item->penanggung_jawab_sab ?? ($item->penanggung_jawab_hsbu ?? '-')) }}</td>
    </tr>
    @if(isset($item->sumber_air_sab))
    <tr>
      <td style="font-weight: bold;">Sumber Air / Kapasitas</td>
      <td>:</td>
      <td>{{ $item->sumber_air_sab }} (Kapasitas: {{ $item->kapasitas_penampungan_sab }} L)</td>
    </tr>
    @endif
    <tr>
      <td style="font-weight: bold;">Petugas Pemeriksa</td>
      <td>:</td>
      <td>{{ $item->nama_pemeriksa ?: 'Petugas Sanitasi BKK Kelas I Bandung' }}</td>
    </tr>
  </table>

  <table class="data-table">
    <thead>
      <tr>
        <th style="width: 50%;">Indikator Penilaian Sanitasi</th>
        <th style="width: 25%; text-align: center;">Hasil Skor</th>
        <th style="width: 25%; text-align: center;">Tingkat Risiko</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>
          <strong>Hasil Evaluasi Higiene Sanitasi</strong><br>
          <span style="font-size: 10px; color: #64748b;">
            @if($type === 'tpp')
              Kategori Usaha: {{ $item->sec_c_keterangan ?: 'Standar' }} &bull; Nilai Lalu: {{ $item->sec_d_nilai_inspeksi_sebelumnya }}
            @elseif($type === 'sab')
              Checklist Perlindungan Fisik & Kualitas Sanitasi Sarana
            @else
              Checklist Higiene Bangunan, Penyehatan Ruang, Vektor & Limbah
            @endif
          </span>
        </td>
        <td style="text-align: center; font-size: 14px; font-weight: bold;">
          {{ $item->hasil ?? ($item->total ?? 0) }}
        </td>
        <td style="text-align: center;">
          @php
            $risk = $item->tingkat_risiko ?: 'Rendah';
            $badgeClass = match($risk) {
              'Rendah' => 'badge-success',
              'Sedang' => 'badge-warning',
              default => 'badge-danger',
            };
          @endphp
          <span class="badge {{ $badgeClass }}">{{ strtoupper($risk) }}</span>
        </td>
      </tr>
    </tbody>
  </table>

  @if(!empty($item->kesimpulan))
  <div style="margin-bottom: 10px;">
    <strong>Kesimpulan Pemeriksaan:</strong>
    <p style="margin: 3px 0; padding: 6px; background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 4px;">
      {{ $item->kesimpulan }}
    </p>
  </div>
  @endif

  @if(!empty($item->saran_rencana_tindakan))
  <div style="margin-bottom: 15px;">
    <strong>Saran / Rencana Tindak Lanjut:</strong>
    <p style="margin: 3px 0; padding: 6px; background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 4px;">
      {{ $item->saran_rencana_tindakan }}
    </p>
  </div>
  @endif

  <table class="footer-table">
    <tr>
      <td style="width: 50%;"></td>
      <td style="width: 50%; text-align: center;">
        <div>Ditetapkan di: {{ $item->wilker ?: 'Bandung' }}</div>
        <div>Tanggal: {{ $item->tanggal_inspeksi ? $item->tanggal_inspeksi->translatedFormat('d F Y') : date('d F Y') }}</div>
        <div style="margin-top: 10px; font-weight: bold;">Petugas Pemeriksa Sanitasi,</div>
        <div style="height: 50px;"></div>
        <div style="font-weight: bold; text-decoration: underline;">{{ $item->nama_pemeriksa ?: 'Petugas BKK Kelas I Bandung' }}</div>
        <div style="font-size: 9px; color: #64748b;">Balai Kekarantinaan Kesehatan Kelas I Bandung</div>
      </td>
    </tr>
  </table>

</body>
</html>

