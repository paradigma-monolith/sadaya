<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal Layanan Pengawasan Faktor Risiko Kesehatan &bull; BKK Kelas I Bandung</title>

    <!-- Google Fonts & Tailwind CDN -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">
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
                            50: '#f0fdf4',
                            100: '#dcfce7',
                            500: '#22c55e',
                            600: '#16a34a',
                            700: '#15803d',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        .glass-header {
            background: rgba(255, 255, 255, 0.94);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(226, 232, 240, 0.85);
        }
        .service-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 24px;
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
            position: relative;
            overflow: hidden;
        }
        .service-card:hover {
            transform: translateY(-4px);
            border-color: #cbd5e1;
            box-shadow: 0 20px 30px -10px rgba(15, 23, 42, 0.08), 0 10px 15px -5px rgba(15, 23, 42, 0.04);
        }
        .hero-pattern {
            background-color: #f8fafc;
            background-image: radial-gradient(#e2e8f0 1.2px, transparent 1.2px), radial-gradient(#e2e8f0 1.2px, #f8fafc 1.2px);
            background-size: 40px 40px;
            background-position: 0 0, 20px 20px;
        }
        .badge-pulse {
            position: relative;
        }
        .badge-pulse::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            transform: translateY(-50%);
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background-color: currentColor;
            animation: pulse-ring 2s infinite;
        }
        @keyframes pulse-ring {
            0% { transform: translateY(-50%) scale(0.9); opacity: 0.8; }
            50% { transform: translateY(-50%) scale(1.6); opacity: 0; }
            100% { transform: translateY(-50%) scale(0.9); opacity: 0; }
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 antialiased font-sans min-h-screen flex flex-col selection:bg-emerald-500 selection:text-white">

    <!-- Top Announcement Bar -->
    <div class="bg-slate-900 text-slate-300 text-[11px] py-2 px-4 border-b border-slate-800">
        <div class="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-2 text-center sm:text-left">
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center px-2 py-0.5 rounded-full font-mono font-bold text-[9px] bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">
                    RESMI
                </span>
                <span>Kementerian Kesehatan RI &bull; Balai Kekarantinaan Kesehatan (BKK) Kelas I Bandung</span>
            </div>
            <div class="flex items-center gap-4 text-slate-400 font-mono">
                <span>Induk &bull; 6 Wilayah Kerja Jawa Barat</span>
                <span class="hidden md:inline">&bull;</span>
                <span class="hidden md:inline text-emerald-400">Siaga 24 Jam</span>
            </div>
        </div>
    </div>

    <!-- Official Header & Navigation Bar -->
    <header class="glass-header sticky top-0 z-40 transition-all">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between gap-4">
            <!-- Brand / Logo Section -->
            <a href="{{ route('travelconsent.home') }}" class="flex items-center gap-3.5 group">
                <div class="w-12 h-12 rounded-2xl bg-slate-900 text-white flex items-center justify-center font-black text-sm tracking-wider shadow-md group-hover:bg-emerald-600 transition-colors shrink-0">
                    BKK
                </div>
                <div>
                    <span class="text-[10px] font-bold tracking-wider uppercase text-emerald-600 block leading-tight font-mono">
                        TIM KERJA PENGAWASAN ALAT ANGKUT &amp; BARANG
                    </span>
                    <span class="text-base sm:text-lg font-extrabold text-slate-900 tracking-tight block leading-tight group-hover:text-emerald-700 transition-colors">
                        BKK Kelas I Bandung
                    </span>
                    <span class="text-[11px] text-slate-500 block leading-tight">
                        Portal Layanan Kekarantinaan Kesehatan Terpadu
                    </span>
                </div>
            </a>

            <!-- Navigation Links & Action Buttons -->
            <nav class="hidden md:flex items-center gap-6 text-xs font-semibold text-slate-600">
                <a href="#layanan" class="hover:text-emerald-600 transition-colors">Formulir Layanan</a>
                <a href="#tracking" class="hover:text-emerald-600 transition-colors">Lacak Registrasi</a>
                <a href="#wilker" class="hover:text-emerald-600 transition-colors">Wilayah Kerja</a>
                <a href="#regulasi" class="hover:text-emerald-600 transition-colors">Regulasi IHR</a>
            </nav>

            <div class="flex items-center gap-3">
                <a href="#tracking" class="hidden sm:inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold text-slate-700 bg-slate-100 hover:bg-slate-200 transition-colors border border-slate-200">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                    <span>Lacak Dokumen</span>
                </a>

                <a href="{{ route('home') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold text-white bg-slate-900 hover:bg-emerald-600 transition-all shadow-sm">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
                    <span>Portal Petugas</span>
                </a>
            </div>
        </div>
    </header>

    <!-- Main Content Body -->
    <main class="flex-grow">

        <!-- Hero Section -->
        <section class="hero-pattern py-12 sm:py-20 border-b border-slate-200/80 relative overflow-hidden">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative z-10">
                
                <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200 mb-6 shadow-xs">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span>Pelayanan Publik Standar WHO - International Health Regulations (IHR 2005)</span>
                </div>

                <h1 class="text-3xl sm:text-5xl lg:text-6xl font-black text-slate-900 tracking-tight leading-tight max-w-4xl mx-auto">
                    Sistem Pelayanan Terpadu <span class="text-transparent bg-clip-text bg-gradient-to-r from-emerald-600 via-teal-600 to-indigo-600">Kekarantinaan Kesehatan</span>
                </h1>

                <p class="text-sm sm:text-base text-slate-600 max-w-2xl mx-auto mt-4 font-normal leading-relaxed">
                    Akses terpadu pengisian formulir inspeksi sanitasi pesawat udara, penerbitan sertifikat sanitasi kapal laut (SSCC/SSCEC), serta verifikasi pemakaian alkes dan obat-obatan emergensi secara digital dengan stempel geotagging GPS real-time.
                </p>

                <div class="flex flex-col sm:flex-row items-center justify-center gap-3.5 mt-8">
                    <a href="#layanan" class="w-full sm:w-auto px-7 py-3.5 rounded-2xl font-extrabold text-sm text-white bg-emerald-600 hover:bg-emerald-500 active:scale-[0.99] shadow-lg shadow-emerald-600/25 transition-all flex items-center justify-center gap-2 cursor-pointer">
                        <span>Pilih Formulir Layanan</span>
                        <span>↓</span>
                    </a>
                    <a href="#tracking" class="w-full sm:w-auto px-7 py-3.5 rounded-2xl font-bold text-sm text-slate-700 bg-white hover:bg-slate-100 border border-slate-300 transition-all shadow-xs flex items-center justify-center gap-2">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                        <span>Cek Status / Cetak Tanda Terima</span>
                    </a>
                </div>

                <!-- Statistics Strip -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 max-w-4xl mx-auto mt-12 pt-8 border-t border-slate-200/80">
                    <div class="p-3 bg-white/70 backdrop-blur-xs rounded-2xl border border-slate-200/70">
                        <span class="block text-xl sm:text-2xl font-black text-slate-900 font-mono">4 Layanan</span>
                        <span class="text-xs text-slate-500 font-medium">Formulir Digital Terbuka</span>
                    </div>
                    <div class="p-3 bg-white/70 backdrop-blur-xs rounded-2xl border border-slate-200/70">
                        <span class="block text-xl sm:text-2xl font-black text-emerald-700 font-mono">6 Wilker</span>
                        <span class="text-xs text-slate-500 font-medium">Bandara &amp; Pelabuhan Laut</span>
                    </div>
                    <div class="p-3 bg-white/70 backdrop-blur-xs rounded-2xl border border-slate-200/70">
                        <span class="block text-xl sm:text-2xl font-black text-indigo-700 font-mono">WGS84 GPS</span>
                        <span class="text-xs text-slate-500 font-medium">Geotagging Real-Time</span>
                    </div>
                    <div class="p-3 bg-white/70 backdrop-blur-xs rounded-2xl border border-slate-200/70">
                        <span class="block text-xl sm:text-2xl font-black text-teal-700 font-mono">IHR 2005</span>
                        <span class="text-xs text-slate-500 font-medium">Standar WHO &amp; Kemenkes</span>
                    </div>
                </div>

            </div>
        </section>

        <!-- Document Status Tracking Section -->
        <section id="tracking" class="py-12 bg-white border-b border-slate-200">
            <div class="max-w-4xl mx-auto px-4 sm:px-6">
                <div class="text-center max-w-xl mx-auto mb-6">
                    <span class="text-xs font-bold uppercase tracking-wider text-emerald-600 block mb-1 font-mono">
                        PELACAKAN &amp; VERIFIKASI DOKUMEN
                    </span>
                    <h2 class="text-2xl font-black text-slate-900 tracking-tight">
                        Cek Status Registrasi &amp; Unduh Tanda Terima
                    </h2>
                    <p class="text-xs sm:text-sm text-slate-500 mt-1">
                        Masukkan nomor registrasi resmi yang Anda peroleh saat submit formulir (contoh: <code class="bg-slate-100 px-1 py-0.5 rounded text-slate-800 font-mono">AIR-...</code>, <code class="bg-slate-100 px-1 py-0.5 rounded text-slate-800 font-mono">SSCC-...</code>, <code class="bg-slate-100 px-1 py-0.5 rounded text-slate-800 font-mono">ALKES-...</code>, <code class="bg-slate-100 px-1 py-0.5 rounded text-slate-800 font-mono">OBAT-...</code>).
                    </p>
                </div>

                <!-- Search Input Form -->
                <form action="{{ route('travelconsent.home') }}" method="GET" class="flex flex-col sm:flex-row items-center gap-3">
                    <div class="relative flex-grow w-full">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                        </div>
                        <input
                            type="text"
                            name="reg"
                            value="{{ $searchQuery }}"
                            placeholder="Ketik Nomor Registrasi Dokumen di sini..."
                            class="w-full pl-11 pr-4 py-3.5 text-sm font-mono font-medium rounded-2xl border-2 border-slate-200 bg-slate-50/70 focus:bg-white focus:border-emerald-500 focus:outline-none transition-all placeholder:text-slate-400 placeholder:font-sans uppercase"
                            required
                        >
                    </div>
                    <button
                        type="submit"
                        class="w-full sm:w-auto px-7 py-3.5 rounded-2xl font-bold text-sm text-white bg-slate-900 hover:bg-slate-800 transition-all shadow-md cursor-pointer shrink-0 flex items-center justify-center gap-2"
                    >
                        <span>Cari Dokumen</span>
                        <span>→</span>
                    </button>
                    @if(!empty($searchQuery))
                        <a href="{{ route('travelconsent.home') }}" class="px-4 py-3 text-xs font-semibold text-slate-500 hover:text-slate-800 transition-colors">
                            Reset
                        </a>
                    @endif
                </form>

                <!-- Search Result Card -->
                @if($searchResult)
                    <div class="mt-6">
                        @if(isset($searchResult['not_found']))
                            <div class="p-5 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-sm flex items-start gap-3">
                                <span class="text-xl">⚠️</span>
                                <div>
                                    <strong class="block font-bold">Nomor Registrasi Tidak Ditemukan</strong>
                                    <p class="text-xs text-rose-600 mt-0.5">
                                        Nomor registrasi <code class="font-mono font-bold bg-white px-1.5 py-0.5 rounded border border-rose-200">{{ $searchResult['query'] }}</code> tidak terdaftar di pangkalan data BKK Kelas I Bandung. Pastikan Anda mengetik nomor registrasi dengan lengkap dan benar.
                                    </p>
                                </div>
                            </div>
                        @else
                            <div class="p-6 rounded-3xl bg-slate-900 text-white border border-slate-800 shadow-xl relative overflow-hidden">
                                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 border-b border-slate-800 pb-4">
                                    <div>
                                        <div class="flex items-center gap-2 mb-1">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-[10px] font-mono font-bold uppercase tracking-wider bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">
                                                {{ $searchResult['badge'] }}
                                            </span>
                                            <span class="text-xs text-slate-400">&bull;</span>
                                            <span class="text-xs font-mono text-slate-300">Tercatat: {{ $searchResult['date'] }}</span>
                                        </div>
                                        <h3 class="text-lg font-extrabold text-white">
                                            {{ $searchResult['title'] }}
                                        </h3>
                                        <span class="font-mono text-sm text-emerald-400 font-bold block mt-0.5">
                                            No. Reg: {{ $searchResult['registration_no'] }}
                                        </span>
                                    </div>

                                    <div class="text-right">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-emerald-400/10 text-emerald-400 border border-emerald-400/30">
                                            ✓ {{ $searchResult['status_label'] }}
                                        </span>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 py-4 text-xs">
                                    <div>
                                        <span class="text-slate-400 block text-[11px]">Subjek / Armada:</span>
                                        <strong class="text-slate-200 text-sm font-semibold">{{ $searchResult['subject'] }}</strong>
                                    </div>
                                    <div>
                                        <span class="text-slate-400 block text-[11px]">Petugas Pemeriksa:</span>
                                        <strong class="text-slate-200 text-sm font-semibold">{{ $searchResult['inspector'] }}</strong>
                                    </div>
                                    <div>
                                        <span class="text-slate-400 block text-[11px]">Evaluasi / Kesimpulan:</span>
                                        <strong class="text-emerald-400 text-sm font-semibold">{{ $searchResult['conclusion'] }}</strong>
                                    </div>
                                </div>

                                <div class="pt-4 border-t border-slate-800 flex flex-wrap items-center gap-3">
                                    <a
                                        href="{{ $searchResult['receipt_url'] }}"
                                        target="_blank"
                                        class="px-5 py-2.5 rounded-xl font-bold text-xs bg-emerald-600 hover:bg-emerald-500 text-white transition-all shadow-sm flex items-center gap-1.5"
                                    >
                                        <span>Buka Tanda Terima Resmi</span>
                                        <span>↗</span>
                                    </a>

                                    @if(!empty($searchResult['pdf_url']))
                                        <a
                                            href="{{ $searchResult['pdf_url'] }}"
                                            target="_blank"
                                            class="px-4 py-2.5 rounded-xl font-semibold text-xs bg-slate-800 hover:bg-slate-700 text-slate-200 transition-colors border border-slate-700 flex items-center gap-1.5"
                                        >
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                                            <span>Unduh Dokumen PDF</span>
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>
                @endif

            </div>
        </section>

        <!-- Core Services Catalog Section -->
        <section id="layanan" class="py-16 sm:py-24">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <div class="flex flex-col md:flex-row md:items-end justify-between gap-4 mb-12">
                    <div>
                        <span class="text-xs font-bold uppercase tracking-wider text-emerald-600 block mb-1 font-mono">
                            KATALOG FORMULIR ELEKTRONIK
                        </span>
                        <h2 class="text-2xl sm:text-4xl font-black text-slate-900 tracking-tight">
                            Pilih Formulir Pengawasan &amp; Inspeksi
                        </h2>
                        <p class="text-xs sm:text-sm text-slate-500 mt-1 max-w-2xl">
                            Silakan pilih formulir inspeksi sesuai dengan moda alat angkut atau kebutuhan kekarantinaan kesehatan di wilayah kerja Anda.
                        </p>
                    </div>

                    <div class="text-right shrink-0">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-200 font-mono">
                            🛡️ Standar Kemenkes RI
                        </span>
                    </div>
                </div>

                <!-- 4 Main Service Cards Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 lg:gap-8">
                    
                    <!-- CARD 1: Sanitasi Pesawat Udara -->
                    <div class="service-card p-6 sm:p-8 flex flex-col justify-between border-t-4 border-t-indigo-600 group">
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-mono font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                    <span>✈️</span>
                                    <span>KARANTINA KESEHATAN UDARA</span>
                                </span>
                                <span class="text-xs text-slate-400 font-mono">Form Publik 01</span>
                            </div>

                            <div class="flex items-start gap-4 pt-1">
                                <div class="w-14 h-14 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-2xl shrink-0 group-hover:bg-indigo-600 group-hover:text-white transition-colors">
                                    ✈️
                                </div>
                                <div>
                                    <h3 class="text-xl font-extrabold text-slate-900 tracking-tight group-hover:text-indigo-600 transition-colors">
                                        Inspeksi Sanitasi Pesawat Udara
                                    </h3>
                                    <p class="text-xs text-slate-500 font-medium italic mt-0.5">
                                        Aircraft Sanitation Inspection &bull; General Declaration
                                    </p>
                                </div>
                            </div>

                            <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                                Formulir pemeriksaan kelaikan sanitasi kompartemen pesawat udara (kabin penumpang, toilet/lavatory, dapur/galley, penyediaan air, kualitas makanan, limbah) serta evaluasi penerbitan Sertifikat Bebas Serangga / Tindakan Disinseksi.
                            </p>

                            <!-- Features List -->
                            <div class="pt-2 border-t border-slate-100 space-y-1.5 text-xs text-slate-500 font-medium">
                                <div class="flex items-center gap-2">
                                    <span class="text-emerald-500 font-bold">✓</span>
                                    <span>Live Camera Geotagging &bull; Stempel Koordinat Otomatis WGS84</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-emerald-500 font-bold">✓</span>
                                    <span>Tanda Tangan Digital Ganda Petugas Kabin &amp; Petugas BKK</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-emerald-500 font-bold">✓</span>
                                    <span>Penerbitan Nomor Registrasi &bull; Unduh Dokumen PDF Resmi</span>
                                </div>
                            </div>
                        </div>

                        <div class="pt-6 mt-6 border-t border-slate-100 flex items-center justify-between">
                            <span class="text-xs font-bold text-indigo-600 font-mono">Bandara BDO &amp; KJT</span>
                            <a
                                href="{{ route('travelconsent.public_aircraft') }}"
                                class="px-6 py-2.5 rounded-xl font-bold text-xs text-white bg-indigo-600 hover:bg-indigo-500 transition-all shadow-md shadow-indigo-600/20 flex items-center gap-2 cursor-pointer"
                            >
                                <span>Buka Formulir Pesawat</span>
                                <span>→</span>
                            </a>
                        </div>
                    </div>

                    <!-- CARD 2: Sanitasi Kapal Laut (SSCC / SSCEC) -->
                    <div class="service-card p-6 sm:p-8 flex flex-col justify-between border-t-4 border-t-emerald-600 group">
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-mono font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    <span>🚢</span>
                                    <span>KARANTINA KESEHATAN LAUT</span>
                                </span>
                                <span class="text-xs text-slate-400 font-mono">Form Publik 02</span>
                            </div>

                            <div class="flex items-start gap-4 pt-1">
                                <div class="w-14 h-14 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-2xl shrink-0 group-hover:bg-emerald-600 group-hover:text-white transition-colors">
                                    🚢
                                </div>
                                <div>
                                    <h3 class="text-xl font-extrabold text-slate-900 tracking-tight group-hover:text-emerald-600 transition-colors">
                                        Sertifikat Sanitasi Kapal (SSCC / SSCEC)
                                    </h3>
                                    <p class="text-xs text-slate-500 font-medium italic mt-0.5">
                                        Ship Sanitation Control / Exemption Certificate &bull; WHO IHR 2005
                                    </p>
                                </div>
                            </div>

                            <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                                Formulir evaluasi sanitasi 32 area fasilitas kapal laut untuk penerbitan Sertifikat Pengendalian Sanitasi Kapal (SSCC) atau Sertifikat Pembebasan Pengendalian Sanitasi Kapal (SSCEC) di dermaga pelabuhan wilayah Jawa Barat.
                            </p>

                            <!-- Features List -->
                            <div class="pt-2 border-t border-slate-100 space-y-1.5 text-xs text-slate-500 font-medium">
                                <div class="flex items-center gap-2">
                                    <span class="text-emerald-500 font-bold">✓</span>
                                    <span>Evaluasi Lengkap 32 Fasilitas Kapal Standar Lampiran 3 IHR (2005)</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-emerald-500 font-bold">✓</span>
                                    <span>Preset Dermaga Patimban, Cirebon, Indramayu, Pelabuhan Ratu</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-emerald-500 font-bold">✓</span>
                                    <span>Multi-Foto Lapangan Geotagging GPS &bull; TTD Digital Pemeriksa</span>
                                </div>
                            </div>
                        </div>

                        <div class="pt-6 mt-6 border-t border-slate-100 flex items-center justify-between">
                            <span class="text-xs font-bold text-emerald-600 font-mono">4 Wilker Pelabuhan Laut</span>
                            <a
                                href="{{ route('travelconsent.public_sanitasi') }}"
                                class="px-6 py-2.5 rounded-xl font-bold text-xs text-white bg-emerald-600 hover:bg-emerald-500 transition-all shadow-md shadow-emerald-600/20 flex items-center gap-2 cursor-pointer"
                            >
                                <span>Buka Formulir Kapal</span>
                                <span>→</span>
                            </a>
                        </div>
                    </div>

                    <!-- CARD 3: Pemakaian Alkes Kotak P3K & Emergensi -->
                    <div class="service-card p-6 sm:p-8 flex flex-col justify-between border-t-4 border-t-blue-600 group">
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-mono font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                    <span>🩺</span>
                                    <span>PENGAWASAN ALAT KESEHATAN</span>
                                </span>
                                <span class="text-xs text-slate-400 font-mono">Form Publik 03</span>
                            </div>

                            <div class="flex items-start gap-4 pt-1">
                                <div class="w-14 h-14 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-2xl shrink-0 group-hover:bg-blue-600 group-hover:text-white transition-colors">
                                    🩺
                                </div>
                                <div>
                                    <h3 class="text-xl font-extrabold text-slate-900 tracking-tight group-hover:text-blue-600 transition-colors">
                                        Pemakaian Alkes Kotak P3K &amp; Emergensi
                                    </h3>
                                    <p class="text-xs text-slate-500 font-medium italic mt-0.5">
                                        First Aid Kit (17 Alkes) &amp; Emergency Medical Kit (21 Alkes)
                                    </p>
                                </div>
                            </div>

                            <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                                Rekam dan validasi pemakaian alat kesehatan kotak P3K dan kit medis darurat pada armada penerbangan komersial dan kloter haji Indonesia di Bandara Internasional Kertajati dan Asrama Haji Bekasi.
                            </p>

                            <!-- Features List -->
                            <div class="pt-2 border-t border-slate-100 space-y-1.5 text-xs text-slate-500 font-medium">
                                <div class="flex items-center gap-2">
                                    <span class="text-emerald-500 font-bold">✓</span>
                                    <span>Checklist Terstruktur 17 Item P3K &bull; 21 Item Medis Darurat</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-emerald-500 font-bold">✓</span>
                                    <span>Pengesahan Tanda Tangan Digital Awak Kabin &amp; Petugas BKK</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-emerald-500 font-bold">✓</span>
                                    <span>Geotagging Live Viewfinder &bull; Pelacakan Kloter &amp; Maskapai</span>
                                </div>
                            </div>
                        </div>

                        <div class="pt-6 mt-6 border-t border-slate-100 flex items-center justify-between">
                            <span class="text-xs font-bold text-blue-600 font-mono">Kloter Haji &amp; Maskapai</span>
                            <a
                                href="{{ route('travelconsent.public_alat') }}"
                                class="px-6 py-2.5 rounded-xl font-bold text-xs text-white bg-blue-600 hover:bg-blue-500 transition-all shadow-md shadow-blue-600/20 flex items-center gap-2 cursor-pointer"
                            >
                                <span>Buka Formulir Alkes</span>
                                <span>→</span>
                            </a>
                        </div>
                    </div>

                    <!-- CARD 4: Pemakaian Obat-obatan Kotak P3K & Emergensi -->
                    <div class="service-card p-6 sm:p-8 flex flex-col justify-between border-t-4 border-t-rose-600 group">
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-mono font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                    <span>💊</span>
                                    <span>PENGAWASAN OBAT-OBATAN</span>
                                </span>
                                <span class="text-xs text-slate-400 font-mono">Form Publik 04</span>
                            </div>

                            <div class="flex items-start gap-4 pt-1">
                                <div class="w-14 h-14 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center text-2xl shrink-0 group-hover:bg-rose-600 group-hover:text-white transition-colors">
                                    💊
                                </div>
                                <div>
                                    <h3 class="text-xl font-extrabold text-slate-900 tracking-tight group-hover:text-rose-600 transition-colors">
                                        Pemakaian Obat Kotak P3K &amp; Emergensi
                                    </h3>
                                    <p class="text-xs text-slate-500 font-medium italic mt-0.5">
                                        Emergency Medications &bull; Analgesik, Antiemetik &amp; Injeksi
                                    </p>
                                </div>
                            </div>

                            <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                                Pengawasan dan pencatatan sediaan obat-obatan P3K (6 sediaan) dan obat emergensi (15 sediaan) termasuk obat injeksi, bronkodilator, serta cairan resusitasi pada penerbangan armada udara.
                            </p>

                            <!-- Features List -->
                            <div class="pt-2 border-t border-slate-100 space-y-1.5 text-xs text-slate-500 font-medium">
                                <div class="flex items-center gap-2">
                                    <span class="text-emerald-500 font-bold">✓</span>
                                    <span>Pengawasan 6 Sediaan P3K Oral &bull; 15 Sediaan Emergensi Ampul</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-emerald-500 font-bold">✓</span>
                                    <span>Monitoring Kadaluarsa (ED) &amp; Nomor Registrasi Penggunaan</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-emerald-500 font-bold">✓</span>
                                    <span>Perekaman Stempel Watermark Geotagging &bull; TTD Digital Kru</span>
                                </div>
                            </div>
                        </div>

                        <div class="pt-6 mt-6 border-t border-slate-100 flex items-center justify-between">
                            <span class="text-xs font-bold text-rose-600 font-mono">Kloter Haji &amp; Maskapai</span>
                            <a
                                href="{{ route('travelconsent.public_obat') }}"
                                class="px-6 py-2.5 rounded-xl font-bold text-xs text-white bg-rose-600 hover:bg-rose-500 transition-all shadow-md shadow-rose-600/20 flex items-center gap-2 cursor-pointer"
                            >
                                <span>Buka Formulir Obat</span>
                                <span>→</span>
                            </a>
                        </div>
                    </div>

                </div>

            </div>
        </section>

        <!-- Operational Work Units / 6 Wilker Section -->
        <section id="wilker" class="py-16 bg-slate-100/70 border-t border-b border-slate-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <div class="text-center max-w-2xl mx-auto mb-12">
                    <span class="text-xs font-bold uppercase tracking-wider text-emerald-700 block mb-1 font-mono">
                        JARINGAN WILAYAH KERJA
                    </span>
                    <h2 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">
                        Induk &amp; 6 Wilayah Kerja Jawa Barat
                    </h2>
                    <p class="text-xs sm:text-sm text-slate-500 mt-1">
                        Balai Kekarantinaan Kesehatan Kelas I Bandung mengawal keamanan pintu masuk negara di bandara internasional dan pelabuhan laut strategis di Jawa Barat.
                    </p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                    
                    <!-- 1. Wilker Bandara KJT -->
                    <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-2xs">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-[10px] font-mono font-bold px-2 py-0.5 rounded bg-blue-50 text-blue-700 border border-blue-200">
                                BANDARA UDARA
                            </span>
                            <span class="text-xs font-bold text-slate-400 font-mono">KJT / WICA</span>
                        </div>
                        <h4 class="font-extrabold text-sm text-slate-900">Wilker Bandara Internasional Kertajati (BIJB)</h4>
                        <p class="text-xs text-slate-500 mt-1">Jl. Raya Kertajati - Majalengka, Kab. Majalengka</p>
                        <span class="inline-block mt-3 text-[11px] font-semibold text-emerald-600">Pelayanan Embarkasi Haji &amp; Pesawat Komersial</span>
                    </div>

                    <!-- 2. Wilker Bandara BDO -->
                    <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-2xs">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-[10px] font-mono font-bold px-2 py-0.5 rounded bg-blue-50 text-blue-700 border border-blue-200">
                                BANDARA UDARA
                            </span>
                            <span class="text-xs font-bold text-slate-400 font-mono">BDO / WICC</span>
                        </div>
                        <h4 class="font-extrabold text-sm text-slate-900">Wilker Bandara Husein Sastranegara</h4>
                        <p class="text-xs text-slate-500 mt-1">Jl. Pajajaran No. 156, Kec. Cicendo, Kota Bandung</p>
                        <span class="inline-block mt-3 text-[11px] font-semibold text-emerald-600">Pelayanan Penerbangan Domestik &amp; Charter</span>
                    </div>

                    <!-- 3. Wilker Pelabuhan Patimban -->
                    <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-2xs">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-[10px] font-mono font-bold px-2 py-0.5 rounded bg-teal-50 text-teal-700 border border-teal-200">
                                PELABUHAN LAUT
                            </span>
                            <span class="text-xs font-bold text-slate-400 font-mono">IDPTB</span>
                        </div>
                        <h4 class="font-extrabold text-sm text-slate-900">Wilker Pelabuhan Laut Patimban</h4>
                        <p class="text-xs text-slate-500 mt-1">Dermaga Petikemas Patimban, Pusakanagara, Kab. Subang</p>
                        <span class="inline-block mt-3 text-[11px] font-semibold text-emerald-600">Sertifikasi Sanitasi Kapal Internasional (SSCC)</span>
                    </div>

                    <!-- 4. Wilker Pelabuhan Cirebon -->
                    <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-2xs">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-[10px] font-mono font-bold px-2 py-0.5 rounded bg-teal-50 text-teal-700 border border-teal-200">
                                PELABUHAN LAUT
                            </span>
                            <span class="text-xs font-bold text-slate-400 font-mono">IDCRB</span>
                        </div>
                        <h4 class="font-extrabold text-sm text-slate-900">Wilker Pelabuhan Laut Cirebon</h4>
                        <p class="text-xs text-slate-500 mt-1">Jl. Pelabuhan No. 1, Lemahwungkuk, Kota Cirebon</p>
                        <span class="inline-block mt-3 text-[11px] font-semibold text-emerald-600">Pemeriksaan Kapal Niaga &amp; Sertifikat SSCEC</span>
                    </div>

                    <!-- 5. Wilker Pelabuhan Indramayu -->
                    <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-2xs">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-[10px] font-mono font-bold px-2 py-0.5 rounded bg-teal-50 text-teal-700 border border-teal-200">
                                PELABUHAN LAUT
                            </span>
                            <span class="text-xs font-bold text-slate-400 font-mono">IDIND</span>
                        </div>
                        <h4 class="font-extrabold text-sm text-slate-900">Wilker Pelabuhan Laut Indramayu</h4>
                        <p class="text-xs text-slate-500 mt-1">Komplek Pelabuhan Karangsong, Kab. Indramayu</p>
                        <span class="inline-block mt-3 text-[11px] font-semibold text-emerald-600">Pengawasan Kapal Perikanan &amp; Cargo Laut</span>
                    </div>

                    <!-- 6. Wilker Pelabuhan Pelabuhan Ratu -->
                    <div class="p-5 rounded-2xl bg-white border border-slate-200 shadow-2xs">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-[10px] font-mono font-bold px-2 py-0.5 rounded bg-teal-50 text-teal-700 border border-teal-200">
                                PELABUHAN LAUT
                            </span>
                            <span class="text-xs font-bold text-slate-400 font-mono">IDPLR</span>
                        </div>
                        <h4 class="font-extrabold text-sm text-slate-900">Wilker Pelabuhan Pelabuhan Ratu</h4>
                        <p class="text-xs text-slate-500 mt-1">Jl. Sirgalih, Pelabuhanratu, Kab. Sukabumi</p>
                        <span class="inline-block mt-3 text-[11px] font-semibold text-emerald-600">Kekarantinaan Pesisir Selatan Jawa Barat</span>
                    </div>

                </div>

            </div>
        </section>

        <!-- Legal Basis & IHR 2005 Standards Section -->
        <section id="regulasi" class="py-14 bg-white border-b border-slate-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="p-8 sm:p-10 rounded-3xl bg-slate-900 text-white relative overflow-hidden">
                    <div class="absolute -right-10 -bottom-10 w-72 h-72 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>

                    <div class="relative z-10 max-w-3xl">
                        <span class="text-xs font-mono font-bold uppercase tracking-wider text-emerald-400 block mb-2">
                            DASAR HUKUM &amp; KESIAPSIAGAAN KESEHATAN MASYARAKAT
                        </span>
                        <h3 class="text-2xl sm:text-3xl font-black text-white tracking-tight">
                            Pencegahan Penyakit Menular &amp; Kedaruratan Kesehatan Internasional
                        </h3>
                        <p class="text-xs sm:text-sm text-slate-300 mt-3 leading-relaxed">
                            Seluruh inspeksi sanitasi pada portal ini diselenggarakan berdasarkan <strong>Undang-Undang No. 17 Tahun 2023 tentang Kesehatan</strong>, <strong>Peraturan Pemerintah No. 40 Tahun 1991</strong>, serta regulasi global <strong>International Health Regulations (WHO IHR 2005)</strong> dan standar <strong>ICAO Annex 9</strong> guna mencegah masuk dan keluarnya kedaruratan kesehatan masyarakat yang meresahkan dunia (KKMMD).
                        </p>

                        <div class="mt-6 flex flex-wrap gap-2 text-[11px] font-mono">
                            <span class="px-3 py-1 rounded-lg bg-slate-800 text-slate-300 border border-slate-700">UU No. 17/2023</span>
                            <span class="px-3 py-1 rounded-lg bg-slate-800 text-slate-300 border border-slate-700">WHO IHR (2005)</span>
                            <span class="px-3 py-1 rounded-lg bg-slate-800 text-slate-300 border border-slate-700">ICAO Facilitation</span>
                            <span class="px-3 py-1 rounded-lg bg-slate-800 text-slate-300 border border-slate-700">Permenkes No. 2/2023</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

    </main>

    <!-- Footer Section -->
    <footer class="bg-white border-t border-slate-200/90 py-12 text-xs text-slate-500">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8 pb-8 border-b border-slate-100">
                
                <div class="md:col-span-2 space-y-3">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-slate-900 text-white flex items-center justify-center font-black text-xs">
                            BKK
                        </div>
                        <div>
                            <span class="font-extrabold text-slate-900 text-sm block">Balai Kekarantinaan Kesehatan Kelas I Bandung</span>
                            <span class="text-[11px] text-slate-400 block">Direktorat Jenderal Pencegahan dan Pengendalian Penyakit &bull; Kemenkes RI</span>
                        </div>
                    </div>
                    <p class="text-xs text-slate-500 max-w-md leading-relaxed">
                        Kantor Induk: Jl. Ciumbuleuit No. 209, Cidadap, Kota Bandung, Jawa Barat 40142.<br>
                        Telepon/Hotline Operasional: (022) 2032484 &bull; Email: bkkbandung@kemkes.go.id
                    </p>
                </div>

                <div>
                    <h5 class="font-bold text-slate-900 uppercase text-[11px] tracking-wider mb-3">Layanan Formulir</h5>
                    <ul class="space-y-2">
                        <li><a href="{{ route('travelconsent.public_aircraft') }}" class="hover:text-emerald-600 transition-colors">Sanitasi Pesawat Udara</a></li>
                        <li><a href="{{ route('travelconsent.public_sanitasi') }}" class="hover:text-emerald-600 transition-colors">Sertifikat Sanitasi Kapal (SSCC)</a></li>
                        <li><a href="{{ route('travelconsent.public_alat') }}" class="hover:text-emerald-600 transition-colors">Pemakaian Alkes P3K Pesawat</a></li>
                        <li><a href="{{ route('travelconsent.public_obat') }}" class="hover:text-emerald-600 transition-colors">Pemakaian Obat P3K Pesawat</a></li>
                    </ul>
                </div>

                <div>
                    <h5 class="font-bold text-slate-900 uppercase text-[11px] tracking-wider mb-3">Tautan Terkait</h5>
                    <ul class="space-y-2">
                        <li><a href="#tracking" class="hover:text-emerald-600 transition-colors">Lacak Status Dokumen</a></li>
                        <li><a href="https://kemkes.go.id" target="_blank" class="hover:text-emerald-600 transition-colors">Kementerian Kesehatan RI ↗</a></li>
                        <li><a href="https://who.int/ihr" target="_blank" class="hover:text-emerald-600 transition-colors">WHO IHR (2005) ↗</a></li>
                        <li><a href="{{ route('home') }}" class="font-semibold text-slate-900 hover:text-emerald-600 transition-colors">Login Sistem Internal ↗</a></li>
                    </ul>
                </div>

            </div>

            <div class="pt-6 flex flex-col sm:flex-row items-center justify-between gap-4 text-center sm:text-left text-[11px] text-slate-400">
                <div>
                    &copy; {{ date('Y') }} Balai Kekarantinaan Kesehatan Kelas I Bandung. Hak Cipta Dilindungi Undang-Undang.
                </div>
                <div class="font-mono">
                    Sistem Manajemen Pelayanan Terpadu &bull; SADAYA v2.4
                </div>
            </div>
        </div>
    </footer>

</body>
</html>

