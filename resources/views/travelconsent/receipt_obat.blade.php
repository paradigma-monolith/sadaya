<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konfirmasi Submit Obat &bull; {{ $inspection->registration_no }}</title>
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
        <div class="w-20 h-20 rounded-full bg-rose-50 text-rose-600 border border-rose-200 mx-auto flex items-center justify-center text-3xl">
            ✓
        </div>

        <div>
            <span class="text-xs font-bold uppercase tracking-wider text-rose-600 block mb-1">Pengiriman Berhasil</span>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Daftar Pemakaian Obat-obatan</h1>
            <p class="text-xs text-slate-500 mt-1">Laporan pemakaian obat-obatan kotak P3K dan emergensi telah terekam secara resmi ke dalam sistem BKK Kelas I Bandung.</p>
        </div>

        <!-- Registration Code Badge -->
        <div class="inline-block px-5 py-2.5 rounded-2xl bg-rose-50 border border-rose-300 font-mono text-base font-bold text-rose-800 tracking-wider">
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
                <span class="font-mono font-bold text-rose-700">{{ $inspection->embarkasi }} &bull; {{ $inspection->nomor_kloter }}</span>
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

        <!-- Actions -->
        <div class="flex flex-col sm:flex-row gap-3 pt-2">
            <a
                href="{{ route('travelconsent.public_obat') }}"
                class="flex-1 py-3 px-4 rounded-xl font-bold text-xs bg-slate-100 hover:bg-slate-200 text-slate-700 transition-colors"
            >
                Input Data Lain
            </a>
            <button
                type="button"
                onclick="window.print()"
                class="flex-1 py-3 px-4 rounded-xl font-bold text-xs bg-rose-600 hover:bg-rose-500 text-white transition-colors shadow-sm"
            >
                Cetak Bukti Tanda Terima
            </button>
        </div>

    </div>

</body>
</html>

