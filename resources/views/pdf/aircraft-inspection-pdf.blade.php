<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Check List Sanitasi Pesawat - {{ $inspection->registration_no }}</title>
    <style>
        @page {
            margin: 15mm 15mm 15mm 15mm;
            size: A4 portrait;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 11px;
            line-height: 1.3;
            color: #1e293b;
        }
        .kop-surat {
            width: 100%;
            text-align: center;
            margin-bottom: 12px;
        }
        .kop-surat img {
            width: 100%;
            height: auto;
            display: block;
            margin: 0 auto;
        }
        .doc-title {
            text-align: center;
            font-size: 13px;
            font-weight: bold;
            text-decoration: underline;
            margin-bottom: 3px;
            letter-spacing: 0.5px;
        }
        .doc-subtitle {
            text-align: center;
            font-size: 10px;
            font-style: italic;
            margin-bottom: 15px;
            color: #475569;
        }
        .reg-pill {
            text-align: right;
            font-family: monospace;
            font-size: 9px;
            font-weight: bold;
            color: #0369a1;
            margin-bottom: 6px;
        }
        .meta-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            font-size: 10.5px;
        }
        .meta-table td {
            padding: 2px 4px;
            vertical-align: top;
        }
        .italic-eng {
            font-style: italic;
            font-size: 9px;
            color: #64748b;
        }
        .check-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            font-size: 10px;
        }
        .check-table th, .check-table td {
            border: 1px solid #94a3b8;
            padding: 3.5px 5px;
            vertical-align: middle;
        }
        .check-table th {
            background-color: #f1f5f9;
            font-weight: bold;
            text-align: center;
        }
        .check-mark {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            font-weight: bold;
            color: #0f172a;
            text-align: center;
        }
        .badge-layak {
            background-color: #dcfce7;
            color: #166534;
            padding: 3px 8px;
            font-weight: bold;
            border-radius: 4px;
            display: inline-block;
        }
        .badge-tidak-layak {
            background-color: #ffe4e6;
            color: #9f1239;
            padding: 3px 8px;
            font-weight: bold;
            border-radius: 4px;
            display: inline-block;
        }
        .signature-table {
            width: 100%;
            margin-top: 15px;
            border-collapse: collapse;
        }
        .signature-table td {
            width: 50%;
            text-align: center;
            vertical-align: top;
            padding: 5px;
        }
        .signature-box {
            height: 70px;
            margin: 5px auto;
        }
        .signature-box img {
            max-height: 65px;
            max-width: 180px;
        }
        .footer-note {
            margin-top: 15px;
            font-size: 8px;
            color: #64748b;
            text-align: center;
            border-top: 1px dashed #cbd5e1;
            padding-top: 5px;
        }
    </style>
</head>
<body>

    <div class="kop-surat">
        @if(file_exists(public_path('storage/images/Kop_BKK_bandung.png')))
            <img src="{{ public_path('storage/images/Kop_BKK_bandung.png') }}" alt="Kop Balai Kekarantinaan Kesehatan Bandung">
        @elseif(file_exists(public_path('images/Kop_BKK_bandung.png')))
            <img src="{{ public_path('images/Kop_BKK_bandung.png') }}" alt="Kop Balai Kekarantinaan Kesehatan Bandung">
        @elseif(file_exists(storage_path('app/public/images/Kop_BKK_bandung.png')))
            <img src="{{ storage_path('app/public/images/Kop_BKK_bandung.png') }}" alt="Kop Balai Kekarantinaan Kesehatan Bandung">
        @elseif(file_exists(public_path('dist/img/Kop_BKK_bandung.png')))
            <img src="{{ public_path('dist/img/Kop_BKK_bandung.png') }}" alt="Kop Balai Kekarantinaan Kesehatan Bandung">
        @else
            <h3 style="margin: 0; text-transform: uppercase;">KEMENTERIAN KESEHATAN REPUBLIK INDONESIA</h3>
            <h4 style="margin: 2px 0;">DIREKTORAT JENDERAL PENCEGAHAN DAN PENGENDALIAN PENYAKIT</h4>
            <h2 style="margin: 3px 0;">BALAI KEKARANTINAAN KESEHATAN KELAS I BANDUNG</h2>
        @endif
    </div>

    <div class="reg-pill">
        No. Registrasi: {{ $inspection->registration_no }} &bull; Tanggal Cetak: {{ now()->format('d/m/Y H:i') }} WIB
    </div>

    <div class="doc-title">AIRCRAFT SANITATION INSPECTION CHECK LIST</div>
    <div class="doc-subtitle">Daftar Pemeriksaan Sanitasi Pesawat Udara</div>

    <table class="meta-table">
        <tr>
            <td width="26%"><strong><u>Pemilik atau Penyelenggara</u></strong><br><span class="italic-eng">Owner or Operator</span></td>
            <td width="2%">:</td>
            <td width="38%"><strong>{{ $inspection->pemilik }}</strong></td>
            <td width="14%"><strong><u>Tanggal</u></strong><br><span class="italic-eng">Date</span></td>
            <td width="2%">:</td>
            <td width="18%">{{ $inspection->tanggal ? $inspection->tanggal->format('d/m/Y') : '-' }}</td>
        </tr>
        <tr>
            <td><strong><u>Tanda Kebangsaan &amp; Reg.</u></strong><br><span class="italic-eng">Marks of Nationality &amp; Reg.</span></td>
            <td>:</td>
            <td><strong>{{ $inspection->tanda_kebangsaan }}</strong></td>
            <td><strong><u>Pukul</u></strong><br><span class="italic-eng">Time</span></td>
            <td>:</td>
            <td>{{ $inspection->waktu }} WIB</td>
        </tr>
    </table>

    <table class="check-table">
        <thead>
            <tr>
                <th rowspan="2" width="5%">No</th>
                <th rowspan="2" colspan="2" width="67%"><u>Yang Diperiksa :</u><br><span class="italic-eng">Item's Inspected</span></th>
                <th colspan="2" width="28%">Keadaan / <span class="italic-eng">Condition</span></th>
            </tr>
            <tr>
                <th width="14%">Baik <span class="italic-eng">(Good)</span></th>
                <th width="14%">Tidak Baik <span class="italic-eng">(Poor)</span></th>
            </tr>
        </thead>
        <tbody>
            <!-- 1. Kabin Penumpang -->
            <tr>
                <td align="center" rowspan="7"><strong>1</strong></td>
                <td colspan="2"><strong><u>Kabin Penumpang</u></strong> <span class="italic-eng">(Passenger Cabins)</span></td>
                <td></td>
                <td></td>
            </tr>
            @php
                $kabinItems = [
                    'a' => 'Lantai dan dinding (Floor & Wall)',
                    'b' => 'Kursi (Seats)',
                    'c' => 'Meja (Tables)',
                    'd' => 'Bagasi Kabin (Cabin Baggage)',
                    'e' => 'Kantong belakang kursi (Seat-back pocket)',
                    'f' => 'Langit-langit kabin (Ceiling)',
                ];
            @endphp
            @foreach($kabinItems as $kKey => $kLabel)
            <tr>
                <td width="4%" align="right">{{ $kKey }}.</td>
                <td>{{ $kLabel }}</td>
                <td align="center" class="check-mark">
                    {{ ($inspection->kabin_penumpang[$kKey] ?? '') === 'baik' ? '✔' : '' }}
                </td>
                <td align="center" class="check-mark">
                    {{ ($inspection->kabin_penumpang[$kKey] ?? '') === 'tidak_baik' ? '✔' : '' }}
                </td>
            </tr>
            @endforeach

            <!-- 2. Toilet -->
            <tr>
                <td align="center" rowspan="6"><strong>2</strong></td>
                <td colspan="2"><strong><u>Kompartemen Toilet</u></strong> <span class="italic-eng">(Toilet Compartment)</span></td>
                <td></td>
                <td></td>
            </tr>
            @php
                $toiletItems = [
                    'a' => 'Lantai (Floor)',
                    'b' => 'Alas duduk & penutup (Seat & Cover)',
                    'c' => 'Tempat sampah (Waste Bins)',
                    'd' => 'Tempat cuci tangan (Wash hand basin)',
                    'e' => 'Sabun & tissue (Soap & tissue)',
                ];
            @endphp
            @foreach($toiletItems as $tKey => $tLabel)
            <tr>
                <td width="4%" align="right">{{ $tKey }}.</td>
                <td>{{ $tLabel }}</td>
                <td align="center" class="check-mark">
                    {{ ($inspection->kompartemen_toilet[$tKey] ?? '') === 'baik' ? '✔' : '' }}
                </td>
                <td align="center" class="check-mark">
                    {{ ($inspection->kompartemen_toilet[$tKey] ?? '') === 'tidak_baik' ? '✔' : '' }}
                </td>
            </tr>
            @endforeach

            <!-- 3. Galley -->
            <tr>
                <td align="center" rowspan="6"><strong>3</strong></td>
                <td colspan="2"><strong><u>Tempat Penyimpanan Makanan</u></strong> <span class="italic-eng">(Galley)</span></td>
                <td></td>
                <td></td>
            </tr>
            @php
                $galleyItems = [
                    'a' => 'Lantai (Floor)',
                    'b' => 'Alas tempat kerja (Worktop)',
                    'c' => 'Tempat cuci tangan (Wash hand basin)',
                    'd' => 'Tempat sampah (Waste bins)',
                    'e' => 'Ruang tempat trolley (Trolley storage)',
                ];
            @endphp
            @foreach($galleyItems as $gKey => $gLabel)
            <tr>
                <td width="4%" align="right">{{ $gKey }}.</td>
                <td>{{ $gLabel }}</td>
                <td align="center" class="check-mark">
                    {{ ($inspection->galley[$gKey] ?? '') === 'baik' ? '✔' : '' }}
                </td>
                <td align="center" class="check-mark">
                    {{ ($inspection->galley[$gKey] ?? '') === 'tidak_baik' ? '✔' : '' }}
                </td>
            </tr>
            @endforeach

            <!-- 4. Dokumen Kesehatan -->
            <tr>
                <td align="center" rowspan="5"><strong>4</strong></td>
                <td colspan="2"><strong><u>Dokumen Kesehatan Pesawat</u></strong> <span class="italic-eng">(Health Documents)</span></td>
                <td></td>
                <td></td>
            </tr>
            @php
                $docItems = [
                    'a' => 'Health Part of the Aircraft General Declaration',
                    'b' => 'Surat keterangan hapus serangga yang terakhir',
                    'c' => 'Surat keterangan hapus hama (jika pernah diadakan)',
                    'd' => 'Buku kesehatan pesawat udara (perjalanan dalam negeri)',
                ];
            @endphp
            @foreach($docItems as $dKey => $dLabel)
            <tr>
                <td width="4%" align="right">{{ $dKey }}.</td>
                <td>{{ $dLabel }}</td>
                <td align="center" class="check-mark">
                    {{ ($inspection->dokumen_kesehatan[$dKey] ?? '') === 'baik' ? '✔' : '' }}
                </td>
                <td align="center" class="check-mark">
                    {{ ($inspection->dokumen_kesehatan[$dKey] ?? '') === 'tidak_baik' ? '✔' : '' }}
                </td>
            </tr>
            @endforeach

            <!-- 5. Pembuangan Kotoran -->
            <tr>
                <td align="center"><strong>5</strong></td>
                <td colspan="2">
                    <strong><u>Pembuangan Kotoran Cair</u></strong> <span class="italic-eng">(Excreta Disposal)</span><br>
                    Ditangani petugas khusus <span class="italic-eng">(Handled by special personal)</span>
                </td>
                <td align="center">Ya: {{ $inspection->pembuangan_kotoran === 'ya' ? '✔' : '-' }}</td>
                <td align="center">Tidak: {{ $inspection->pembuangan_kotoran === 'tidak' ? '✔' : '-' }}</td>
            </tr>

            <!-- 6. Penanganan Air -->
            <tr>
                <td align="center"><strong>6</strong></td>
                <td colspan="2">
                    <strong><u>Penanganan Penyediaan Air</u></strong> <span class="italic-eng">(Aircraft Water Supply)</span><br>
                    &bull; Ditangani petugas khusus: {{ ($inspection->penanganan_air['petugas'] ?? '') === 'ya' ? 'Ya (✔)' : 'Tidak' }}<br>
                    &bull; Kualitas Fisik: 
                    Bau: {{ !empty($inspection->penanganan_air['bau']) ? 'Ada' : 'Tidak' }} | 
                    Warna: {{ !empty($inspection->penanganan_air['warna']) ? 'Ada' : 'Tidak' }} | 
                    Rasa: {{ !empty($inspection->penanganan_air['rasa']) ? 'Ada' : 'Tidak' }}
                </td>
                <td align="center" colspan="2">
                    {{ empty($inspection->penanganan_air['bau']) && empty($inspection->penanganan_air['warna']) && empty($inspection->penanganan_air['rasa']) ? 'Baik (✔)' : 'Perlu Pengawasan' }}
                </td>
            </tr>

            <!-- 7. Makanan -->
            <tr>
                <td align="center"><strong>7</strong></td>
                <td colspan="4">
                    <strong><u>Keadaan Makanan Secara Fisik</u></strong> <span class="italic-eng">(Physical Appearance of Food)</span>: 
                    <strong>{{ ($inspection->keadaan_makanan['kondisi'] ?? '') === 'baik' ? 'Baik (Good)' : 'Tidak Baik (Poor)' }}</strong>
                    @if(!empty($inspection->keadaan_makanan['jumlah']))
                        &bull; Jumlah: {{ $inspection->keadaan_makanan['jumlah'] }}
                    @endif
                    @if(!empty($inspection->keadaan_makanan['jenis']))
                        &bull; Jenis: {{ $inspection->keadaan_makanan['jenis'] }}
                    @endif
                </td>
            </tr>

            <!-- 8. Binatang Pengganggu -->
            <tr>
                <td align="center"><strong>8</strong></td>
                <td colspan="4">
                    <strong><u>Binatang Pengganggu</u></strong> <span class="italic-eng">(Pest Infestation)</span>:
                    @php
                        $pests = [];
                        if(!empty($inspection->binatang_pengganggu['nyamuk'])) $pests[] = 'Nyamuk';
                        if(!empty($inspection->binatang_pengganggu['lalat'])) $pests[] = 'Lalat';
                        if(!empty($inspection->binatang_pengganggu['kecoa'])) $pests[] = 'Kecoa';
                        if(!empty($inspection->binatang_pengganggu['tikus'])) $pests[] = 'Tikus';
                        if(!empty($inspection->binatang_pengganggu['lain2']) && $inspection->binatang_pengganggu['lain2'] !== 'Nihil') $pests[] = $inspection->binatang_pengganggu['lain2'];
                    @endphp
                    @if(count($pests) > 0)
                        <span style="color: #e11d48; font-weight: bold;">Ditemukan: {{ implode(', ', $pests) }}</span>
                    @else
                        <span style="color: #166534; font-weight: bold;">Nihil (Bebas dari binatang vektor/pengganggu)</span>
                    @endif
                </td>
            </tr>

            <!-- 9. Surat Sanitasi -->
            <tr>
                <td align="center"><strong>9</strong></td>
                <td colspan="4">
                    <strong><u>Surat Keterangan Sanitasi Pesawat</u></strong> <span class="italic-eng">(Certificate Aircraft Sanitation)</span>:
                    <strong>{{ $inspection->surat_sanitasi === 'ada' ? 'Ada / There are (✔)' : 'Tidak Ada / Not Found' }}</strong>
                </td>
            </tr>

            <!-- 10. Kesimpulan & Rekomendasi -->
            <tr>
                <td align="center"><strong>10</strong></td>
                <td colspan="4" style="background-color: #fafaf9;">
                    <div style="margin-bottom: 4px;">
                        <strong>Kesimpulan / <span class="italic-eng">Conclusion</span>:</strong>
                        @if($inspection->kesimpulan === 'layak')
                            <span class="badge-layak">LAYAK diterbitkan "Sertifikat Bebas Serangga" (Qualified Disinsection Exemption)</span>
                        @else
                            <span class="badge-tidak-layak">TIDAK LAYAK diterbitkan "Sertifikat Bebas Serangga" (Unqualified)</span>
                        @endif
                    </div>
                    @if($inspection->tindakan)
                        <div>
                            <strong>Rekomendasi Tindakan Penyehatan:</strong> 
                            <span style="color: #0369a1; font-weight: bold; text-transform: uppercase;">
                                {{ str_replace('_', ' ', $inspection->tindakan) }}
                            </span>
                        </div>
                    @endif
                    @if($inspection->catatan_rekomendasi)
                        <div style="font-size: 9.5px; color: #475569; margin-top: 3px;">
                            Catatan: {{ $inspection->catatan_rekomendasi }}
                        </div>
                    @endif
                </td>
            </tr>
        </tbody>
    </table>

    <!-- Digital Signatures Table -->
    <table class="signature-table">
        <tr>
            <td>
                <u>Petugas Kabin</u><br>
                <span class="italic-eng">Cabin Crew</span>
                <div class="signature-box">
                    @if(!empty($inspection->petugas_kabin_ttd) && str_starts_with($inspection->petugas_kabin_ttd, 'data:image'))
                        <img src="{{ $inspection->petugas_kabin_ttd }}" alt="Tanda Tangan Petugas Kabin">
                    @else
                        <div style="padding-top: 25px; font-style: italic; color: #94a3b8;">(Tertanda secara Digital)</div>
                    @endif
                </div>
                <strong>( {{ $inspection->petugas_kabin_nama }} )</strong><br>
                <span class="italic-eng">Name &amp; Signature</span>
            </td>
            <td>
                <u>Petugas Balai Kekarantinaan Kesehatan</u><br>
                <span class="italic-eng">Health Quarantine Officer</span>
                <div class="signature-box">
                    @if(!empty($inspection->petugas_bkk_ttd) && str_starts_with($inspection->petugas_bkk_ttd, 'data:image'))
                        <img src="{{ $inspection->petugas_bkk_ttd }}" alt="Tanda Tangan Petugas BKK">
                    @else
                        <div style="padding-top: 25px; font-style: italic; color: #94a3b8;">(Tertanda secara Digital)</div>
                    @endif
                </div>
                <strong>( {{ $inspection->petugas_bkk_nama }} )</strong><br>
                <span class="italic-eng">Name &amp; Signature</span>
            </td>
        </tr>
    </table>

    <div class="footer-note">
        Dokumen elektronik ini diterbitkan secara sah oleh Sistem Informasi Manajemen Balai Kekarantinaan Kesehatan (BKK) Bandung.<br>
        Keaslian dokumen dapat diverifikasi melalui nomor registrasi resmi: <strong>{{ $inspection->registration_no }}</strong>
    </div>

</body>
</html>
