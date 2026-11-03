<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dokumen Pemakaian Alkes &bull; {{ $inspection->registration_no }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                        mono: ['"JetBrains Mono"', 'monospace'],
                    }
                }
            }
        }
    </script>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: #ffffff !important; padding: 0 !important; }
            .doc-sheet { box-shadow: none !important; border: none !important; max-width: 100% !important; padding: 0 !important; }
            @page { size: A4 portrait; margin: 12mm 15mm; }
        }
    </style>
</head>
<body class="bg-slate-100 text-slate-900 font-sans min-h-screen py-4 sm:py-8 px-2.5 sm:px-6">

    <!-- Top Action Bar (Floating Header) -->
    <div class="no-print max-w-4xl mx-auto mb-4 sm:mb-6 bg-white border border-slate-200/90 rounded-2xl p-3 sm:p-4 shadow-sm flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 sm:gap-4">
        <div class="flex items-center gap-2.5 sm:gap-3">
            <a href="{{ route('travelconsent.home') }}" class="p-1.5 sm:p-2 rounded-xl text-slate-500 hover:text-slate-900 hover:bg-slate-100 transition-colors shrink-0" title="Kembali ke Portal">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
            </a>
            <div class="min-w-0">
                <span class="text-[10px] font-bold uppercase tracking-wider text-blue-600 block">Dokumen Resmi Publik</span>
                <span class="font-mono text-xs sm:text-sm font-bold text-slate-900 truncate block">{{ $inspection->registration_no }}</span>
            </div>
        </div>

        <div class="flex items-center gap-2 flex-wrap sm:flex-nowrap">
            <div class="flex items-center gap-2 bg-slate-50 border border-slate-200 rounded-xl px-2.5 py-1.5 flex-1 sm:flex-initial">
                <span class="text-[11px] text-slate-500 shrink-0">🔗 Link:</span>
                <input 
                    type="text" 
                    id="pageLinkInput" 
                    readonly 
                    value="{{ url()->current() }}" 
                    class="bg-transparent text-[11px] sm:text-xs font-mono text-slate-800 w-full sm:w-60 select-all outline-none"
                    onclick="this.select()"
                >
                <button 
                    type="button" 
                    id="btnPageCopy" 
                    onclick="copyPageLink()" 
                    class="px-2.5 py-1 bg-slate-900 hover:bg-slate-800 text-white rounded-lg text-xs font-semibold transition-all shrink-0 cursor-pointer"
                >
                    Salin
                </button>
            </div>

            <button 
                type="button" 
                onclick="window.print()" 
                class="w-full sm:w-auto px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-xl text-xs font-bold transition-all flex items-center justify-center gap-1.5 shadow-sm cursor-pointer"
            >
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M6 9V2h12v7M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                <span>Cetak / PDF</span>
            </button>
        </div>
    </div>

    <!-- The Official Document Sheet -->
    <div class="doc-sheet max-w-4xl mx-auto bg-white rounded-2xl sm:rounded-3xl border border-slate-200/90 shadow-md sm:shadow-xl p-4 sm:p-8 md:p-12 text-slate-800 space-y-4 sm:space-y-6">
        
        <!-- Official Kop Surat Header -->
        <div class="border-b-2 border-slate-900 pb-3 sm:pb-4 text-center">
            <img 
                src="{{ asset('storage/images/Kop_BKK_bandung.png') }}" 
                alt="Balai Kekarantinaan Kesehatan Kelas I Bandung" 
                class="w-full max-h-24 sm:max-h-36 md:max-h-44 object-contain mx-auto"
                onerror="this.onerror=null;this.src='{{ asset('images/Kop_BKK_bandung.png') }}';"
            >
        </div>

        <!-- Document Title & Header Meta -->
        <div class="text-center space-y-1 pt-1 sm:pt-2">
            <h1 class="text-sm sm:text-lg md:text-xl font-black text-slate-900 uppercase tracking-tight leading-snug">
                Berita Acara &amp; Daftar Pemakaian Alat Kesehatan
            </h1>
            <p class="text-[11px] sm:text-xs font-bold text-slate-600 uppercase tracking-wider">
                Kotak Pertolongan Pertama Pada Kecelakaan (P3K) &amp; Kotak Emergensi Pada Pesawat Udara
            </p>
            <div class="pt-2 flex items-center justify-center flex-wrap gap-2 sm:gap-3">
                <span class="inline-block px-3 py-1 rounded-full text-xs font-mono font-bold bg-blue-50 text-blue-700 border border-blue-200">
                    No. Registrasi: {{ $inspection->registration_no }}
                </span>
                <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200 uppercase">
                    Status: {{ $inspection->status ?? 'Tercatat Resmi' }}
                </span>
            </div>
        </div>

        <!-- Identitas Penerbangan Table -->
        <div class="bg-slate-50/80 rounded-2xl p-5 border border-slate-200/90 text-xs">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700 mb-3 flex items-center gap-1.5">
                <span>✈️</span> Informasi Penerbangan &amp; Kloter
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                <div>
                    <span class="text-slate-500 block">Penerbangan Haji:</span>
                    <span class="font-bold text-slate-900 font-mono text-sm">
                        {{ $inspection->penerbangan_haji_h ? $inspection->penerbangan_haji_h . ' H' : '-' }} / {{ $inspection->penerbangan_haji_m ? $inspection->penerbangan_haji_m . ' M' : '-' }}
                    </span>
                </div>
                <div>
                    <span class="text-slate-500 block">Embarkasi:</span>
                    <span class="font-bold text-slate-900">{{ $inspection->embarkasi }}</span>
                </div>
                <div>
                    <span class="text-slate-500 block">Nomor Kloter:</span>
                    <span class="font-mono font-bold text-blue-700 text-sm">{{ $inspection->nomor_kloter }}</span>
                </div>
                <div>
                    <span class="text-slate-500 block">Waktu Pelaporan:</span>
                    <span class="font-mono font-medium text-slate-700">{{ $inspection->created_at->format('d/m/Y H:i') }} WIB</span>
                </div>
            </div>
            @if($inspection->no_kontak)
            <div class="mt-3 pt-2.5 border-t border-slate-200/80">
                <span class="text-slate-500">Nomor Kontak / Telepon:</span>
                <span class="font-mono font-semibold text-slate-900 ml-1">{{ $inspection->no_kontak }}</span>
            </div>
            @endif
        </div>

        <!-- Tabel 1: Pemakaian Alkes Kotak P3K -->
        <div class="space-y-2 pt-2">
            <div class="flex items-center justify-between">
                <h3 class="text-xs font-bold uppercase tracking-wider text-blue-900 flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-blue-600"></span>
                    <span>1. Daftar Pemakaian Alat Kesehatan Kotak P3K (First Aid Kit)</span>
                </h3>
            </div>
            <p class="text-[10px] text-slate-400 sm:hidden">👉 Geser tabel ke kanan untuk melihat rincian isi & pemakaian</p>
            <div class="border border-slate-200 rounded-xl overflow-x-auto shadow-2xs">
                <table class="w-full text-xs text-left min-w-[500px]">
                    <thead class="bg-slate-100 text-slate-700 border-b border-slate-200 font-bold uppercase text-[10.5px]">
                        <tr>
                            <th class="px-3 py-2.5 w-10 text-center">No</th>
                            <th class="px-3 py-2.5">Nama Alat / Bahan Medis</th>
                            <th class="px-3 py-2.5 w-20 text-center">Satuan</th>
                            <th class="px-3 py-2.5 w-24 text-center">Isi Standar</th>
                            <th class="px-3 py-2.5 w-24 text-center bg-blue-50 text-blue-900">Terpakai</th>
                            <th class="px-3 py-2.5">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200/70">
                        @if(!empty($inspection->items_p3k) && is_array($inspection->items_p3k))
                            @foreach($inspection->items_p3k as $idx => $it)
                            <tr class="{{ ($it['terpakai'] ?? 0) > 0 ? 'bg-amber-50/50 font-medium' : 'hover:bg-slate-50/50' }}">
                                <td class="px-3 py-2 text-center text-slate-500">{{ $idx + 1 }}</td>
                                <td class="px-3 py-2 font-medium text-slate-900">{{ $it['nama'] ?? '-' }}</td>
                                <td class="px-3 py-2 text-center text-slate-600 font-mono">{{ $it['satuan'] ?? 'Pcs' }}</td>
                                <td class="px-3 py-2 text-center text-slate-600 font-mono">{{ $it['isi'] ?? '-' }}</td>
                                <td class="px-3 py-2 text-center font-mono font-bold {{ ($it['terpakai'] ?? 0) > 0 ? 'text-amber-700 bg-amber-100/50' : 'text-slate-400' }}">
                                    {{ $it['terpakai'] ?? 0 }}
                                </td>
                                <td class="px-3 py-2 text-slate-600">{{ !empty($it['keterangan']) ? $it['keterangan'] : '-' }}</td>
                            </tr>
                            @endforeach
                        @else
                            <tr><td colspan="6" class="px-3 py-4 text-center text-slate-400">Tidak ada rincian pemakaian P3K tercatat.</td></tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tabel 2: Pemakaian Alkes Kotak Emergensi -->
        <div class="space-y-2 pt-2 sm:pt-3">
            <div class="flex items-center justify-between">
                <h3 class="text-xs font-bold uppercase tracking-wider text-indigo-900 flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-indigo-600"></span>
                    <span>2. Daftar Pemakaian Alat Kesehatan Kotak Emergensi (Medical Emergency Kit)</span>
                </h3>
            </div>
            <p class="text-[10px] text-slate-400 sm:hidden">👉 Geser tabel ke kanan untuk melihat rincian isi & pemakaian</p>
            <div class="border border-slate-200 rounded-xl overflow-x-auto shadow-2xs">
                <table class="w-full text-xs text-left min-w-[500px]">
                    <thead class="bg-slate-100 text-slate-700 border-b border-slate-200 font-bold uppercase text-[10.5px]">
                        <tr>
                            <th class="px-3 py-2.5 w-10 text-center">No</th>
                            <th class="px-3 py-2.5">Nama Alat Emergensi</th>
                            <th class="px-3 py-2.5 w-20 text-center">Satuan</th>
                            <th class="px-3 py-2.5 w-24 text-center">Isi Standar</th>
                            <th class="px-3 py-2.5 w-24 text-center bg-indigo-50 text-indigo-900">Terpakai</th>
                            <th class="px-3 py-2.5">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200/70">
                        @if(!empty($inspection->items_emergensi) && is_array($inspection->items_emergensi))
                            @foreach($inspection->items_emergensi as $idx => $it)
                            <tr class="{{ ($it['terpakai'] ?? 0) > 0 ? 'bg-amber-50/50 font-medium' : 'hover:bg-slate-50/50' }}">
                                <td class="px-3 py-2 text-center text-slate-500">{{ $idx + 1 }}</td>
                                <td class="px-3 py-2 font-medium text-slate-900">{{ $it['nama'] ?? '-' }}</td>
                                <td class="px-3 py-2 text-center text-slate-600 font-mono">{{ $it['satuan'] ?? 'Pcs' }}</td>
                                <td class="px-3 py-2 text-center text-slate-600 font-mono">{{ $it['isi'] ?? '-' }}</td>
                                <td class="px-3 py-2 text-center font-mono font-bold {{ ($it['terpakai'] ?? 0) > 0 ? 'text-amber-700 bg-amber-100/50' : 'text-slate-400' }}">
                                    {{ $it['terpakai'] ?? 0 }}
                                </td>
                                <td class="px-3 py-2 text-slate-600">{{ !empty($it['keterangan']) ? $it['keterangan'] : '-' }}</td>
                            </tr>
                            @endforeach
                        @else
                            <tr><td colspan="6" class="px-3 py-4 text-center text-slate-400">Tidak ada rincian pemakaian emergensi tercatat.</td></tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Foto Geotagging & Lokasi Lapangan -->
        @if($inspection->foto_geotagging)
        <div class="pt-3 border-t border-slate-200">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700 mb-2 flex items-center gap-1.5">
                <span>📸</span> Dokumentasi Foto Lapangan &amp; Geotagging
            </h3>
            <div class="flex flex-col sm:flex-row gap-3 sm:gap-4 items-start bg-slate-50 p-3 sm:p-4 rounded-xl sm:rounded-2xl border border-slate-200">
                <a href="{{ asset($inspection->foto_geotagging) }}" target="_blank" class="w-full sm:w-auto shrink-0 group">
                    <img 
                        src="{{ asset($inspection->foto_geotagging) }}" 
                        alt="Foto Geotagging" 
                        class="w-full sm:w-48 h-40 sm:h-32 object-cover rounded-xl border border-slate-300 shadow-2xs group-hover:opacity-90 transition-opacity"
                        onerror="this.onerror=null;this.src='/images/photo-placeholder.png';"
                    >
                </a>
                <div class="text-xs space-y-1.5 flex-1">
                    <div class="font-semibold text-slate-800">Bukti Verifikasi Fisik Kotak P3K / Emergensi</div>
                    <div class="text-slate-500 font-mono text-[11px]">
                        📍 Koordinat: {{ $inspection->latitude ?? '-' }}, {{ $inspection->longitude ?? '-' }}
                    </div>
                    <div class="text-slate-600 text-[11px] leading-relaxed">
                        Lokasi: {{ $inspection->lokasi_geotag ?? 'Area Wilayah Kerja Bandara / Pesawat Udara' }}
                    </div>
                    @if($inspection->latitude && $inspection->longitude)
                    <a 
                        href="https://www.google.com/maps?q={{ $inspection->latitude }},{{ $inspection->longitude }}" 
                        target="_blank" 
                        class="inline-flex items-center gap-1 text-[11px] font-bold text-blue-600 hover:underline pt-1"
                    >
                        <span>Lihat Titik Peta Google Maps</span> ↗
                    </a>
                    @endif
                </div>
            </div>
        </div>
        @endif

        <!-- Lembar Pengesahan & Tanda Tangan -->
        <div class="pt-6 border-t border-slate-300 grid grid-cols-1 sm:grid-cols-2 gap-6 sm:gap-8 text-center text-xs">
            <div>
                <p class="text-slate-500 mb-1">Awak Kabin (Crew) Penerbangan</p>
                <div class="h-24 flex items-center justify-center my-1">
                    @if($inspection->crew_ttd)
                        <img src="{{ $inspection->crew_ttd }}" alt="Tanda Tangan Crew" class="max-h-20 max-w-[180px] object-contain">
                    @else
                        <span class="text-slate-300 italic text-[11px]">[Tanda Tangan Tercatat]</span>
                    @endif
                </div>
                <p class="font-bold text-slate-900 border-t border-slate-300 pt-1.5 mx-auto max-w-[200px]">{{ $inspection->crew_nama }}</p>
                <p class="text-[10px] text-slate-400">Crew on Duty</p>
            </div>

            <div>
                <p class="text-slate-500 mb-1">Petugas Pelapor / BKK Kelas I Bandung</p>
                <div class="h-24 flex items-center justify-center my-1">
                    @if($inspection->pelapor_ttd)
                        <img src="{{ $inspection->pelapor_ttd }}" alt="Tanda Tangan Petugas" class="max-h-20 max-w-[180px] object-contain">
                    @else
                        <span class="text-slate-300 italic text-[11px]">[Tanda Tangan Tercatat]</span>
                    @endif
                </div>
                <p class="font-bold text-slate-900 border-t border-slate-300 pt-1.5 mx-auto max-w-[200px]">{{ $inspection->pelapor_nama }}</p>
                <p class="text-[10px] text-slate-400">Tim Pengawasan Faktor Risiko Alat Angkut</p>
            </div>
        </div>

        <!-- Official Institutional Footer -->
        <div class="pt-4 border-t border-slate-200 text-center text-[11px] text-slate-400 space-y-1">
            <p>Dokumen ini diterbitkan secara sah oleh <strong>Balai Kekarantinaan Kesehatan Kelas I Bandung</strong>, Kementerian Kesehatan Republik Indonesia.</p>
            <p class="font-mono text-[10px] text-slate-400">Sistem Sadaya &bull; Keaslian berkas dapat divalidasi melalui nomor registrasi {{ $inspection->registration_no }}</p>
        </div>

    </div>

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
                btn.style.backgroundColor = '#16a34a';
                btn.textContent = 'Tersalin! ✓';
                setTimeout(function() {
                    btn.style.backgroundColor = '#0f172a';
                    btn.textContent = 'Salin';
                }, 2500);
            }
        }
    </script>
</body>
</html>
