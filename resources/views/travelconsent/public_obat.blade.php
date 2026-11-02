<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Daftar Pemakaian Obat P3K & Emergensi &bull; BKK Kelas I Bandung</title>

    <!-- Google Fonts & Tailwind CDN -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'system-ui', '-apple-system', 'sans-serif'],
                        mono: ['"JetBrains Mono"', 'monospace'],
                    },
                    colors: {
                        primary: {
                            50: '#fdf2f8',
                            100: '#fce7f3',
                            500: '#ec4899',
                            600: '#db2777',
                            700: '#be185d',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        .glass-card {
            background: #ffffff;
            border: 1px solid rgba(226, 232, 240, 0.9);
            box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.04), 0 8px 10px -6px rgba(15, 23, 42, 0.02);
        }
        .signature-canvas {
            touch-action: none;
            cursor: crosshair;
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 antialiased min-h-screen py-6 sm:py-10 px-3 sm:px-6 lg:px-8">

    <div class="max-w-5xl mx-auto space-y-6">

        <!-- Header Resmi BKK Kelas I Bandung -->
        <div class="glass-card rounded-3xl p-6 sm:p-8 flex flex-col md:flex-row items-center justify-between gap-6 border-b-4 border-b-rose-600">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-rose-600 text-white flex items-center justify-center font-black text-xl shadow-md">
                    BKK
                </div>
                <div>
                    <div class="text-[11px] font-mono tracking-widest text-rose-700 font-bold uppercase">
                        Kementerian Kesehatan Republik Indonesia
                    </div>
                    <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">
                        Balai Kekarantinaan Kesehatan Kelas I Bandung
                    </h1>
                    <p class="text-xs text-slate-500 font-medium">
                        Wilayah Kerja Bandara Husein Sastranegara, Kertajati (BIJB), &amp; Pelabuhan Jawa Barat
                    </p>
                </div>
            </div>

            <div class="text-right flex md:flex-col items-center md:items-end gap-2 text-xs">
                <span class="px-3 py-1 rounded-full font-mono font-semibold bg-rose-50 text-rose-700 border border-rose-200">
                    FORMULIR OBAT-OBATAN
                </span>
                <span class="text-slate-400 font-mono text-[11px]">
                    Standar ICAO / Kemenkes RI
                </span>
            </div>
        </div>

        <!-- Form Container -->
        <form action="{{ route('travelconsent.submit_obat') }}" method="POST" enctype="multipart/form-data" id="mainForm" class="space-y-6">
            @csrf

            <!-- SECTION 1: Informasi Penerbangan & Kloter -->
            <div class="glass-card rounded-3xl p-6 sm:p-8 space-y-5">
                <div class="flex items-center gap-2.5 pb-3 border-b border-slate-200">
                    <span class="w-7 h-7 rounded-lg bg-rose-100 text-rose-700 font-bold flex items-center justify-center text-xs">1</span>
                    <h2 class="text-base font-extrabold text-slate-900">
                        Informasi Penerbangan &amp; Kloter Haji
                    </h2>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Tahun Haji -->
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Penerbangan Haji (H / M) <span class="text-rose-500">*</span>
                        </label>
                        <div class="flex items-center gap-2">
                            <input
                                type="text"
                                name="penerbangan_haji_h"
                                value="{{ old('penerbangan_haji_h', $currentH) }}"
                                placeholder="1447"
                                class="w-1/2 px-3 py-2 text-xs rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-rose-500 font-mono font-bold text-center"
                                required
                            />
                            <span class="text-slate-400 font-bold">H /</span>
                            <input
                                type="text"
                                name="penerbangan_haji_m"
                                value="{{ old('penerbangan_haji_m', $currentM) }}"
                                placeholder="2026"
                                class="w-1/2 px-3 py-2 text-xs rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-rose-500 font-mono font-bold text-center"
                                required
                            />
                            <span class="text-slate-400 font-bold">M</span>
                        </div>
                    </div>

                    <!-- Embarkasi -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Embarkasi <span class="text-rose-500">*</span>
                        </label>
                        <input
                            type="text"
                            name="embarkasi"
                            value="{{ old('embarkasi', 'Kertajati (KJT)') }}"
                            placeholder="Contoh: Kertajati (KJT) / JKS"
                            class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-rose-500 font-medium"
                            required
                        />
                    </div>

                    <!-- Nomor Kloter -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Nomor Kloter <span class="text-rose-500">*</span>
                        </label>
                        <input
                            type="text"
                            name="nomor_kloter"
                            value="{{ old('nomor_kloter') }}"
                            placeholder="Contoh: Kloter 01 / SOC 02"
                            class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-rose-500 font-medium"
                            required
                        />
                    </div>

                    <!-- Nomor Kontak -->
                    <div class="sm:col-span-2 lg:col-span-4">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Nomor Kontak PIC / Dokter Kloter (TKHI)
                        </label>
                        <input
                            type="text"
                            name="no_kontak"
                            value="{{ old('no_kontak') }}"
                            placeholder="Contoh: 0812-3456-7890 (WhatsApp)"
                            class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-rose-500 font-medium"
                        />
                    </div>
                </div>
            </div>

            <!-- SECTION 2: Pemakaian Obat Kotak P3K (6 Items) -->
            <div class="glass-card rounded-3xl p-6 sm:p-8 space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-200">
                    <div class="flex items-center gap-2.5">
                        <span class="w-7 h-7 rounded-lg bg-rose-100 text-rose-700 font-bold flex items-center justify-center text-xs">2</span>
                        <div>
                            <h2 class="text-base font-extrabold text-slate-900">
                                Daftar Pemakaian Obat Kotak P3K (6 Item)
                            </h2>
                            <p class="text-[11px] text-slate-400">First Aid Kit Medications standar kelaikan kesehatan penerbangan</p>
                        </div>
                    </div>

                    <button
                        type="button"
                        onclick="setAllP3kZero()"
                        class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-slate-100 hover:bg-slate-200 text-slate-700 transition-colors cursor-pointer self-start sm:self-auto"
                    >
                        Set Semua Terpakai: 0
                    </button>
                </div>

                <div class="border border-slate-200 rounded-2xl overflow-hidden overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 font-bold uppercase tracking-wider text-[11px]">
                                <th class="px-4 py-3 w-12 text-center">No</th>
                                <th class="px-4 py-3">Nama Obat P3K</th>
                                <th class="px-4 py-3 w-32 text-center">Bentuk / Satuan</th>
                                <th class="px-4 py-3 w-24 text-center">Isi Standar</th>
                                <th class="px-4 py-3 w-28 text-center">Terpakai</th>
                                <th class="px-4 py-3">Keterangan / Indikasi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($p3kItems as $idx => $item)
                            <tr class="hover:bg-rose-50/40 transition-colors">
                                <td class="px-4 py-2.5 text-center font-mono text-slate-400">{{ $idx + 1 }}</td>
                                <td class="px-4 py-2.5 font-semibold text-slate-800">
                                    {{ $item['nama'] }}
                                    <input type="hidden" name="items_p3k[{{ $idx }}][nama]" value="{{ $item['nama'] }}" />
                                </td>
                                <td class="px-4 py-2.5 text-center">
                                    <span class="px-2 py-0.5 rounded text-[10.5px] font-mono bg-slate-100 text-slate-600">
                                        {{ $item['satuan'] }}
                                    </span>
                                    <input type="hidden" name="items_p3k[{{ $idx }}][satuan]" value="{{ $item['satuan'] }}" />
                                </td>
                                <td class="px-4 py-2.5 text-center">
                                    <input
                                        type="text"
                                        name="items_p3k[{{ $idx }}][isi]"
                                        value="{{ $item['isi'] }}"
                                        class="w-16 px-2 py-1 text-center font-mono text-xs rounded-lg border border-slate-200 bg-slate-50 focus:bg-white"
                                    />
                                </td>
                                <td class="px-4 py-2.5 text-center">
                                    <input
                                        type="number"
                                        min="0"
                                        name="items_p3k[{{ $idx }}][terpakai]"
                                        value="0"
                                        class="p3k-terpakai w-16 px-2 py-1 text-center font-mono font-bold text-xs rounded-lg border border-rose-200 bg-rose-50/50 focus:bg-white focus:ring-2 focus:ring-rose-500"
                                    />
                                </td>
                                <td class="px-4 py-2.5">
                                    <input
                                        type="text"
                                        name="items_p3k[{{ $idx }}][keterangan]"
                                        placeholder="Gejala / pasien / kondisi..."
                                        class="w-full px-2.5 py-1 text-xs rounded-lg border border-slate-200 bg-slate-50 focus:bg-white"
                                    />
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- SECTION 3: Pemakaian Obat Kotak Emergensi (15 Items) -->
            <div class="glass-card rounded-3xl p-6 sm:p-8 space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-200">
                    <div class="flex items-center gap-2.5">
                        <span class="w-7 h-7 rounded-lg bg-rose-100 text-rose-700 font-bold flex items-center justify-center text-xs">3</span>
                        <div>
                            <h2 class="text-base font-extrabold text-slate-900">
                                Daftar Pemakaian Obat Kotak Emergensi (15 Item)
                            </h2>
                            <p class="text-[11px] text-slate-400">Emergency Medical Kit (EMK) Medications kabin pesawat udara</p>
                        </div>
                    </div>

                    <button
                        type="button"
                        onclick="setAllEmZero()"
                        class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-slate-100 hover:bg-slate-200 text-slate-700 transition-colors cursor-pointer self-start sm:self-auto"
                    >
                        Set Semua Terpakai: 0
                    </button>
                </div>

                <div class="border border-slate-200 rounded-2xl overflow-hidden overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 font-bold uppercase tracking-wider text-[11px]">
                                <th class="px-4 py-3 w-12 text-center">No</th>
                                <th class="px-4 py-3">Nama Obat Emergensi</th>
                                <th class="px-4 py-3 w-32 text-center">Bentuk / Satuan</th>
                                <th class="px-4 py-3 w-24 text-center">Isi Standar</th>
                                <th class="px-4 py-3 w-28 text-center">Terpakai</th>
                                <th class="px-4 py-3">Keterangan / Kasus Medis</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($emergensiItems as $idx => $item)
                            <tr class="hover:bg-rose-50/40 transition-colors">
                                <td class="px-4 py-2.5 text-center font-mono text-slate-400">{{ $idx + 1 }}</td>
                                <td class="px-4 py-2.5 font-semibold text-slate-800">
                                    {{ $item['nama'] }}
                                    <input type="hidden" name="items_emergensi[{{ $idx }}][nama]" value="{{ $item['nama'] }}" />
                                </td>
                                <td class="px-4 py-2.5 text-center">
                                    <span class="px-2 py-0.5 rounded text-[10.5px] font-mono bg-slate-100 text-slate-600">
                                        {{ $item['satuan'] }}
                                    </span>
                                    <input type="hidden" name="items_emergensi[{{ $idx }}][satuan]" value="{{ $item['satuan'] }}" />
                                </td>
                                <td class="px-4 py-2.5 text-center">
                                    <input
                                        type="text"
                                        name="items_emergensi[{{ $idx }}][isi]"
                                        value="{{ $item['isi'] }}"
                                        class="w-16 px-2 py-1 text-center font-mono text-xs rounded-lg border border-slate-200 bg-slate-50 focus:bg-white"
                                    />
                                </td>
                                <td class="px-4 py-2.5 text-center">
                                    <input
                                        type="number"
                                        min="0"
                                        name="items_emergensi[{{ $idx }}][terpakai]"
                                        value="0"
                                        class="em-terpakai w-16 px-2 py-1 text-center font-mono font-bold text-xs rounded-lg border border-rose-200 bg-rose-50/50 focus:bg-white focus:ring-2 focus:ring-rose-500"
                                    />
                                </td>
                                <td class="px-4 py-2.5">
                                    <input
                                        type="text"
                                        name="items_emergensi[{{ $idx }}][keterangan]"
                                        placeholder="Kegawatdaruratan kabin..."
                                        class="w-full px-2.5 py-1 text-xs rounded-lg border border-slate-200 bg-slate-50 focus:bg-white"
                                    />
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- SECTION 4: Dokumentasi & Geotagging GPS -->
            <div class="glass-card rounded-3xl p-6 sm:p-8 space-y-4">
                <div class="flex items-center gap-2.5 pb-3 border-b border-slate-200">
                    <span class="w-7 h-7 rounded-lg bg-rose-100 text-rose-700 font-bold flex items-center justify-center text-xs">4</span>
                    <h2 class="text-base font-extrabold text-slate-900">
                        Dokumentasi &amp; Geotagging GPS
                    </h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Foto Dokumentasi Pemeriksaan Kotak Obat P3K / Emergensi
                        </label>
                        <input
                            type="file"
                            name="foto_geotagging"
                            accept="image/*"
                            id="fotoInput"
                            class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-rose-50 file:text-rose-700 hover:file:bg-rose-100 cursor-pointer"
                        />
                        <div id="fotoPreview" class="hidden mt-3 rounded-2xl overflow-hidden border border-slate-200 max-h-48">
                            <img src="" alt="Preview" class="w-full h-48 object-cover" />
                        </div>
                    </div>

                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-slate-700">Koordinat Geotagging:</span>
                            <button
                                type="button"
                                onclick="getLocation()"
                                class="px-2.5 py-1 rounded-lg text-xs font-bold bg-rose-600 hover:bg-rose-500 text-white transition-colors cursor-pointer"
                            >
                                📍 Ambil GPS
                            </button>
                        </div>
                        <div class="grid grid-cols-2 gap-2 text-xs font-mono">
                            <div>
                                <span class="text-slate-400 block text-[10px]">LATITUDE:</span>
                                <input type="text" name="latitude" id="latInput" readonly class="w-full bg-white border border-slate-200 rounded-lg px-2 py-1" placeholder="-" />
                            </div>
                            <div>
                                <span class="text-slate-400 block text-[10px]">LONGITUDE:</span>
                                <input type="text" name="longitude" id="lngInput" readonly class="w-full bg-white border border-slate-200 rounded-lg px-2 py-1" placeholder="-" />
                            </div>
                        </div>
                        <input
                            type="text"
                            name="lokasi_geotag"
                            id="locTagInput"
                            placeholder="Keterangan Lokasi (contoh: Terminal Haji BIJB Kertajati)"
                            class="w-full text-xs bg-white border border-slate-200 rounded-lg px-2.5 py-1.5"
                        />
                    </div>
                </div>
            </div>

            <!-- SECTION 5: Tanda Tangan Digital Ganda -->
            <div class="glass-card rounded-3xl p-6 sm:p-8 space-y-4">
                <div class="flex items-center gap-2.5 pb-3 border-b border-slate-200">
                    <span class="w-7 h-7 rounded-lg bg-rose-100 text-rose-700 font-bold flex items-center justify-center text-xs">5</span>
                    <h2 class="text-base font-extrabold text-slate-900">
                        Pengesahan &amp; Tanda Tangan Digital Ganda
                    </h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Awak Kabin / Crew -->
                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 space-y-3">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                            Awak Kabin / Kru Pesawat <span class="text-rose-500">*</span>
                        </label>
                        <input
                            type="text"
                            name="crew_nama"
                            placeholder="Nama Lengkap Awak Kabin"
                            class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 bg-white font-medium"
                            required
                        />
                        <div class="space-y-1">
                            <div class="flex justify-between items-center text-[11px] text-slate-400">
                                <span>Tanda Tangan Digital Awak Kabin:</span>
                                <button type="button" onclick="clearCrewSign()" class="text-rose-500 hover:underline">Hapus</button>
                            </div>
                            <div class="border border-slate-300 rounded-xl bg-white overflow-hidden">
                                <canvas id="crewCanvas" width="400" height="150" class="signature-canvas w-full h-32"></canvas>
                            </div>
                            <input type="hidden" name="crew_ttd" id="crewSignInput" />
                        </div>
                    </div>

                    <!-- Petugas Karantina / Pelapor -->
                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 space-y-3">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                            Petugas Karantina BKK / Pelapor <span class="text-rose-500">*</span>
                        </label>
                        <input
                            type="text"
                            name="pelapor_nama"
                            placeholder="Nama Petugas Karantina BKK / TKHI"
                            class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 bg-white font-medium"
                            required
                        />
                        <div class="space-y-1">
                            <div class="flex justify-between items-center text-[11px] text-slate-400">
                                <span>Tanda Tangan Digital Petugas / Pelapor:</span>
                                <button type="button" onclick="clearPelaporSign()" class="text-rose-500 hover:underline">Hapus</button>
                            </div>
                            <div class="border border-slate-300 rounded-xl bg-white overflow-hidden">
                                <canvas id="pelaporCanvas" width="400" height="150" class="signature-canvas w-full h-32"></canvas>
                            </div>
                            <input type="hidden" name="pelapor_ttd" id="pelaporSignInput" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit Button Bar -->
            <div class="glass-card rounded-3xl p-6 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="text-xs text-slate-500">
                    Pastikan seluruh data pemakaian obat-obatan terisi dengan benar sebelum mengirim.
                </div>
                <button
                    type="submit"
                    class="w-full sm:w-auto px-8 py-3.5 rounded-2xl font-bold text-sm text-white bg-rose-600 hover:bg-rose-500 transition-all shadow-md cursor-pointer flex items-center justify-center gap-2"
                >
                    <span>Kirim Formulir Pemakaian Obat</span>
                    <span>→</span>
                </button>
            </div>

        </form>

        <!-- Footer -->
        <div class="text-center text-xs text-slate-400 py-4">
            &copy; 2026 Balai Kekarantinaan Kesehatan Kelas I Bandung &bull; Sistem Informasi Karantina Terpadu
        </div>

    </div>

    <!-- Script Signature & GPS -->
    <script>
        function setAllP3kZero() {
            document.querySelectorAll('.p3k-terpakai').forEach(el => el.value = '0');
        }

        function setAllEmZero() {
            document.querySelectorAll('.em-terpakai').forEach(el => el.value = '0');
        }

        // Preview Foto
        const fotoInput = document.getElementById('fotoInput');
        const fotoPreview = document.getElementById('fotoPreview');
        if (fotoInput) {
            fotoInput.addEventListener('change', function(e) {
                if (this.files && this.files[0]) {
                    const reader = new FileReader();
                    reader.onload = function(ev) {
                        fotoPreview.querySelector('img').src = ev.target.result;
                        fotoPreview.classList.remove('hidden');
                    }
                    reader.readAsDataURL(this.files[0]);
                }
            });
        }

        // GPS Geotagging
        function getLocation() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(pos) {
                    document.getElementById('latInput').value = pos.coords.latitude.toFixed(6);
                    document.getElementById('lngInput').value = pos.coords.longitude.toFixed(6);
                    if (!document.getElementById('locTagInput').value) {
                        document.getElementById('locTagInput').value = 'Bandara Kertajati (BIJB) / Koordinat Terverifikasi';
                    }
                }, function(err) {
                    alert('Gagal mengambil koordinat: ' + err.message);
                });
            } else {
                alert('Geolocation tidak didukung pada browser ini.');
            }
        }

        // Setup Signature Pad Helper
        function initSignature(canvasId, inputId) {
            const canvas = document.getElementById(canvasId);
            const input = document.getElementById(inputId);
            if (!canvas) return { clear: () => {} };

            const ctx = canvas.getContext('2d');
            let isDrawing = false;
            let hasDrawn = false;

            function resizeCanvas() {
                const rect = canvas.getBoundingClientRect();
                canvas.width = rect.width;
                canvas.height = rect.height;
                ctx.lineWidth = 2.5;
                ctx.lineCap = 'round';
                ctx.lineJoin = 'round';
                ctx.strokeStyle = '#0f172a';
            }
            window.addEventListener('resize', resizeCanvas);
            setTimeout(resizeCanvas, 100);

            function getPos(e) {
                const rect = canvas.getBoundingClientRect();
                const clientX = e.touches ? e.touches[0].clientX : e.clientX;
                const clientY = e.touches ? e.touches[0].clientY : e.clientY;
                return {
                    x: clientX - rect.left,
                    y: clientY - rect.top
                };
            }

            function startDraw(e) {
                isDrawing = true;
                const pos = getPos(e);
                ctx.beginPath();
                ctx.moveTo(pos.x, pos.y);
                if (e.cancelable) e.preventDefault();
            }

            function draw(e) {
                if (!isDrawing) return;
                const pos = getPos(e);
                ctx.lineTo(pos.x, pos.y);
                ctx.stroke();
                hasDrawn = true;
                if (e.cancelable) e.preventDefault();
            }

            function endDraw() {
                if (isDrawing && hasDrawn) {
                    input.value = canvas.toDataURL('image/png');
                }
                isDrawing = false;
            }

            canvas.addEventListener('mousedown', startDraw);
            canvas.addEventListener('mousemove', draw);
            window.addEventListener('mouseup', endDraw);

            canvas.addEventListener('touchstart', startDraw, { passive: false });
            canvas.addEventListener('touchmove', draw, { passive: false });
            window.addEventListener('touchend', endDraw);

            return {
                clear: function() {
                    ctx.clearRect(0, 0, canvas.width, canvas.height);
                    input.value = '';
                    hasDrawn = false;
                }
            };
        }

        let crewSignPad, pelaporSignPad;
        document.addEventListener('DOMContentLoaded', function() {
            crewSignPad = initSignature('crewCanvas', 'crewSignInput');
            pelaporSignPad = initSignature('pelaporCanvas', 'pelaporSignInput');
        });

        function clearCrewSign() {
            if (crewSignPad) crewSignPad.clear();
        }

        function clearPelaporSign() {
            if (pelaporSignPad) pelaporSignPad.clear();
        }
    </script>
</body>
</html>
