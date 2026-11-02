<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konfirmasi Submit &bull; {{ $inspection->registration_no }}</title>
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
        <div class="w-20 h-20 rounded-full bg-emerald-50 text-emerald-600 border border-emerald-200 mx-auto flex items-center justify-center text-3xl">
            ✓
        </div>

        <div>
            <span class="text-xs font-bold uppercase tracking-wider text-emerald-600 block mb-1">Penerbitan Berhasil</span>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Sertifikat Sanitasi Kapal</h1>
            <p class="text-xs text-slate-500 mt-1">Formulir pemeriksaan sanitasi kapal telah berhasil direkam secara resmi ke dalam sistem BKK Kelas I Bandung.</p>
        </div>

        <!-- Registration Code Badge -->
        <div class="inline-block px-5 py-2.5 rounded-2xl bg-emerald-50 border border-emerald-300 font-mono text-base font-bold text-emerald-800 tracking-wider">
            {{ $inspection->registration_no }}
        </div>

        <!-- Details Summary Card -->
        <div class="bg-slate-50 rounded-2xl p-5 border border-slate-200 text-left text-xs space-y-2.5">
            <div class="flex justify-between items-center py-1 border-b border-slate-200/70">
                <span class="text-slate-500">Nama Kapal:</span>
                <span class="font-bold text-slate-900">{{ $inspection->nama_kapal }}</span>
            </div>
            <div class="flex justify-between items-center py-1 border-b border-slate-200/70">
                <span class="text-slate-500">Nomor IMO / Registrasi:</span>
                <span class="font-mono font-bold text-slate-900">{{ $inspection->imo_no }}</span>
            </div>
            <div class="flex justify-between items-center py-1 border-b border-slate-200/70">
                <span class="text-slate-500">Pelabuhan / Wilker:</span>
                <span class="font-semibold text-slate-800">{{ $inspection->pelabuhan }}</span>
            </div>
            <div class="flex justify-between items-center py-1 border-b border-slate-200/70">
                <span class="text-slate-500">Sertifikat Mencatat:</span>
                <span class="font-bold text-emerald-700 bg-emerald-100/70 px-2 py-0.5 rounded-md">
                    {{ $inspection->sertifikat_mencatat === 'Pembebasan' ? 'Pembebasan (SSCEC)' : 'Pengendalian (SSCC)' }}
                </span>
            </div>
            <div class="flex justify-between items-center py-1 border-b border-slate-200/70">
                <span class="text-slate-500">Tanggal Pemeriksaan:</span>
                <span class="font-medium text-slate-800">{{ $inspection->tanggal->format('d F Y') }}</span>
            </div>
            <div class="flex justify-between items-center py-1">
                <span class="text-slate-500">Petugas Pemeriksa:</span>
                <span class="font-semibold text-slate-900">{{ $inspection->nama_pemeriksa }}</span>
            </div>
        </div>

        <!-- Actions -->
        <div class="pt-2 flex flex-col sm:flex-row items-center gap-3">
            <a href="{{ route('travelconsent.public_sanitasi') }}" class="w-full sm:flex-1 py-3 px-4 rounded-xl font-bold text-xs bg-slate-900 hover:bg-slate-800 text-white transition-all shadow-sm">
                + Input Formulir Baru
            </a>
            <a href="{{ route('home') }}" class="w-full sm:flex-1 py-3 px-4 rounded-xl font-semibold text-xs bg-slate-100 hover:bg-slate-200 text-slate-700 transition-all border border-slate-200">
                Ke Dashboard Utama
            </a>
        </div>

    </div>

</body>
</html>
