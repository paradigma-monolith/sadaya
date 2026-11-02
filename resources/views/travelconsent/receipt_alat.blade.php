<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konfirmasi Submit Alkes &bull; {{ $inspection->registration_no }}</title>
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
</head>
<body class="bg-slate-50 text-slate-900 font-sans min-h-screen py-10 px-4 flex items-center justify-center">

    <div class="max-w-xl w-full bg-white rounded-3xl p-8 sm:p-10 border border-slate-200/90 shadow-xl text-center space-y-6">
        
        <!-- Success Icon -->
        <div class="w-20 h-20 rounded-full bg-blue-50 text-blue-600 border border-blue-200 mx-auto flex items-center justify-center text-3xl">
            ✓
        </div>

        <div>
            <span class="text-xs font-bold uppercase tracking-wider text-blue-600 block mb-1">Pengiriman Berhasil</span>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Daftar Pemakaian Alat Kesehatan</h1>
            <p class="text-xs text-slate-500 mt-1">Laporan pemakaian alat kesehatan kotak P3K dan emergensi telah terekam secara resmi ke dalam sistem BKK Kelas I Bandung.</p>
        </div>

        <!-- Registration Code Badge -->
        <div class="inline-block px-5 py-2.5 rounded-2xl bg-blue-50 border border-blue-300 font-mono text-base font-bold text-blue-800 tracking-wider">
            {{ $inspection->registration_no }}
        </div>

        <!-- Details Summary Card -->
        <div class="bg-slate-50 rounded-2xl p-5 border border-slate-200 text-left text-xs space-y-2.5">
            <div class="flex justify-between items-center py-1 border-b border-slate-200/70">
                <span class="text-slate-500">Penerbangan Haji:</span>
                <span class="font-bold text-slate-900">{{ $inspection->penerbangan_haji_h }} H / {{ $inspection->penerbangan_haji_m }} M</span>
            </div>
            <div class="flex justify-between items-center py-1 border-b border-slate-200/70">
                <span class="text-slate-500">Embarkasi &amp; Kloter:</span>
                <span class="font-mono font-bold text-blue-700">{{ $inspection->embarkasi }} &bull; {{ $inspection->nomor_kloter }}</span>
            </div>
            <div class="flex justify-between items-center py-1 border-b border-slate-200/70">
                <span class="text-slate-500">Awak Kabin (Crew):</span>
                <span class="font-semibold text-slate-800">{{ $inspection->crew_nama }}</span>
            </div>
            <div class="flex justify-between items-center py-1 border-b border-slate-200/70">
                <span class="text-slate-500">Petugas / Pelapor:</span>
                <span class="font-semibold text-slate-800">{{ $inspection->pelapor_nama }}</span>
            </div>
            <div class="flex justify-between items-center py-1">
                <span class="text-slate-500">Waktu Rekam:</span>
                <span class="font-mono text-slate-600">{{ $inspection->created_at->format('d/m/Y H:i') }} WIB</span>
            </div>
        </div>

        <!-- Copyable Public Document Link Box -->
        <div class="bg-blue-50/70 border border-blue-300 rounded-2xl p-4 text-left">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-bold uppercase tracking-wider text-blue-800 flex items-center gap-1.5">
                    <span>🔗</span> Link Publik Hasil Dokumen
                </span>
                <span class="text-[10px] text-slate-500 font-medium">Bisa diakses publik tanpa login</span>
            </div>
            <div class="flex gap-2 items-center">
                <input 
                    type="text" 
                    id="pageLinkInput" 
                    readonly 
                    value="{{ url()->current() }}" 
                    class="flex-1 bg-white border border-slate-300 rounded-xl px-3 py-2 text-xs font-mono text-slate-800 select-all outline-none"
                    onclick="this.select()"
                >
                <button 
                    type="button" 
                    id="btnPageCopy" 
                    onclick="copyPageLink()" 
                    class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-bold transition-all shrink-0 cursor-pointer shadow-xs"
                >
                    Salin Link
                </button>
            </div>
            <div id="pageCopyMsg" class="hidden text-[11px] font-semibold text-blue-600 mt-1.5">
                ✓ Link berhasil disalin ke clipboard!
            </div>
        </div>

        <!-- Actions -->
        <div class="flex flex-col sm:flex-row gap-3 pt-2">
            <button 
                type="button" 
                onclick="showPublicModal({ regNo: '{{ $inspection->registration_no }}', publicUrl: window.location.href, title: 'Formulir Berhasil Disubmit!' })" 
                class="flex-1 py-3 px-4 rounded-xl font-bold text-xs bg-blue-600 hover:bg-blue-700 text-white transition-colors shadow-sm cursor-pointer"
            >
                Pop-up Link Dokumen
            </button>
            <a
                href="{{ route('travelconsent.public_alat') }}"
                class="flex-1 py-3 px-4 rounded-xl font-bold text-xs bg-slate-100 hover:bg-slate-200 text-slate-700 transition-colors"
            >
                Input Data Baru
            </a>
            <button
                type="button"
                onclick="window.print()"
                class="flex-1 py-3 px-4 rounded-xl font-bold text-xs bg-slate-900 hover:bg-slate-800 text-white transition-colors shadow-sm cursor-pointer"
            >
                Cetak Bukti
            </button>
        </div>

    </div>

    @include('travelconsent.modal_public_link')

    <script>
        function copyPageLink() {
            var input = document.getElementById('pageLinkInput');
            if (!input) return;
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(input.value).then(showPageCopyDone).catch(function() {
                    input.select();
                    document.execCommand('copy');
                    showPageCopyDone();
                });
            } else {
                input.select();
                document.execCommand('copy');
                showPageCopyDone();
            }
        }

        function showPageCopyDone() {
            var btn = document.getElementById('btnPageCopy');
            var msg = document.getElementById('pageCopyMsg');
            if (btn) {
                btn.style.backgroundColor = '#16a34a';
                btn.textContent = 'Tersalin! ✓';
            }
            if (msg) msg.classList.remove('hidden');
            setTimeout(function() {
                if (btn) {
                    btn.style.backgroundColor = '#0f172a';
                    btn.textContent = 'Salin Link';
                }
                if (msg) msg.classList.add('hidden');
            }, 3000);
        }

        document.addEventListener('DOMContentLoaded', function() {
            showPublicModal({
                regNo: '{{ $inspection->registration_no }}',
                publicUrl: window.location.href,
                title: 'Formulir Berhasil Disubmit!'
            });
        });
    </script>

</body>
</html>

