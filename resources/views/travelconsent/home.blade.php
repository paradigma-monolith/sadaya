<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Travel Consent Form &bull; BKK Kelas I Bandung</title>

    <!-- Google Font: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;600&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'system-ui', '-apple-system', 'sans-serif'],
                        mono: ['"JetBrains Mono"', 'monospace'],
                    }
                }
            }
        }
    </script>
    <style>
        body {
            background-color: #f8fafc;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            margin: 0;
            padding: 30px 16px;
        }
        .card-menu {
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
            text-decoration: none;
            color: inherit;
        }
        .card-menu:hover {
            transform: translateY(-6px);
            box-shadow: 0 16px 28px -6px rgba(15, 23, 42, 0.08), 0 8px 12px -4px rgba(15, 23, 42, 0.04);
            border-color: #cbd5e1;
        }
    </style>
</head>
<body class="text-slate-800 antialiased font-sans">

    <div class="w-full max-w-5xl mx-auto my-auto py-6 flex flex-col items-center">
        
        <!-- Official Logo Header -->
        <div class="mb-6 flex justify-center items-center">
            <img 
                src="{{ asset('storage/images/Kop_BKK_bandung.png') }}" 
                alt="Logo BKK Bandung" 
                class="max-h-20 sm:max-h-24 w-auto object-contain"
                onerror="this.onerror=null;this.src='{{ asset('images/Kop_BKK_bandung.png') }}';"
            >
        </div>

        <!-- Header Title & Subtitle -->
        <div class="text-center mb-8 sm:mb-10 max-w-2xl px-2">
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                Travel Consent Form
            </h1>
            <p class="text-slate-500 text-xs sm:text-sm font-medium mt-1">
                Balai Kekarantinaan Kesehatan Kelas I Bandung
            </p>
            <div class="mt-2.5 inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                <span>Tim Kerja Pengawasan Faktor Risiko Kesehatan Alat Angkut dan Barang</span>
            </div>
        </div>

        <!-- 4 Card Menu Grid (Clean & Direct) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 sm:gap-6 w-full">
            
            <!-- 1. Aircraft Sanitation Inspection -->
            <a href="{{ route('travelconsent.public_aircraft') }}" class="card-menu bg-white rounded-2xl border border-slate-200/90 p-6 flex flex-col items-center text-center shadow-xs group">
                <div class="w-16 h-16 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center mb-4 transition-colors group-hover:bg-indigo-600 group-hover:text-white shrink-0">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <!-- Plane departure icon -->
                        <path d="M17.8 19.2 16 11l3.5-3.5C21 6 21.5 4 21 3c-1-.5-3 0-4.5 1.5L13 8 4.8 6.2c-.5-.1-.9.1-1.1.5l-.3.5c-.2.5-.1 1 .3 1.3L9 12l-2 3H4l-1 1 3 2 2 3 1-1v-3l3-2 3.5 5.3c.3.4.8.5 1.3.3l.5-.2c.4-.3.6-.7.5-1.2z"/>
                    </svg>
                </div>
                <h2 class="text-sm sm:text-base font-bold text-slate-900 leading-snug group-hover:text-indigo-600 transition-colors">
                    Aircraft Sanitation Inspection
                </h2>
                <span class="text-[11px] text-slate-400 mt-1 block">Sanitasi Pesawat Udara</span>
            </a>

            <!-- 2. Sanitasi Kapal (Ship Sanitation) -->
            <a href="{{ route('travelconsent.public_sanitasi') }}" class="card-menu bg-white rounded-2xl border border-slate-200/90 p-6 flex flex-col items-center text-center shadow-xs group">
                <div class="w-16 h-16 rounded-2xl bg-teal-50 text-teal-600 flex items-center justify-center mb-4 transition-colors group-hover:bg-teal-600 group-hover:text-white shrink-0">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <!-- Ship icon -->
                        <path d="M2 21c.6.5 1.2 1 2.5 1 2.5 0 2.5-2 5-2 1.3 0 1.9.5 2.5 1 .6.5 1.2 1 2.5 1 2.5 0 2.5-2 5-2 1.3 0 1.9.5 2.5 1"/>
                        <path d="M19.38 20A11.6 11.6 0 0 0 21 14l-9-4-9 4c0 2.9.94 5.34 2.81 7.76"/>
                        <path d="M19 13V7a2 2 0 0 0-2-2H7a2 2 0 0 0-2 2v6"/>
                        <path d="M12 10V2"/>
                        <path d="M12 2h4"/>
                    </svg>
                </div>
                <h2 class="text-sm sm:text-base font-bold text-slate-900 leading-snug group-hover:text-teal-600 transition-colors">
                    Sanitasi Kapal (Ship Sanitation)
                </h2>
                <span class="text-[11px] text-slate-400 mt-1 block">Sertifikat SSCC / SSCEC</span>
            </a>

            <!-- 3. Alat Kesehatan Kotak P3K & Emergensi -->
            <a href="{{ route('travelconsent.public_alat') }}" class="card-menu bg-white rounded-2xl border border-slate-200/90 p-6 flex flex-col items-center text-center shadow-xs group">
                <div class="w-16 h-16 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center mb-4 transition-colors group-hover:bg-blue-600 group-hover:text-white shrink-0">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <!-- Medkit icon -->
                        <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/>
                        <path d="M8 2h8a2 2 0 0 1 2 2v2H6V4a2 2 0 0 1 2-2z"/>
                        <path d="M12 11v6"/>
                        <path d="M9 14h6"/>
                    </svg>
                </div>
                <h2 class="text-sm sm:text-base font-bold text-slate-900 leading-snug group-hover:text-blue-600 transition-colors">
                    Alat Kesehatan Kotak P3K &amp; Emergensi
                </h2>
                <span class="text-[11px] text-slate-400 mt-1 block">First Aid &amp; Emergency Kit</span>
            </a>

            <!-- 4. Obat Kotak P3K & Emergensi -->
            <a href="{{ route('travelconsent.public_obat') }}" class="card-menu bg-white rounded-2xl border border-slate-200/90 p-6 flex flex-col items-center text-center shadow-xs group">
                <div class="w-16 h-16 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center mb-4 transition-colors group-hover:bg-rose-600 group-hover:text-white shrink-0">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <!-- Pills icon -->
                        <path d="m10.5 20.5 10-10a4.95 4.95 0 1 0-7-7l-10 10a4.95 4.95 0 1 0 7 7Z"/>
                        <path d="m8.5 8.5 7 7"/>
                    </svg>
                </div>
                <h2 class="text-sm sm:text-base font-bold text-slate-900 leading-snug group-hover:text-rose-600 transition-colors">
                    Obat Kotak P3K &amp; Emergensi
                </h2>
                <span class="text-[11px] text-slate-400 mt-1 block">Sediaan Farmasi Pesawat</span>
            </a>

        </div>

        <!-- Footer Note -->
        <div class="text-center mt-12 sm:mt-14 space-y-2">
            <p class="text-xs text-slate-400 font-medium">
                Copyright &copy; {{ date('Y') }} IT Support Balai Kekarantinaan Kesehatan Kelas I Bandung
            </p>
            <div class="text-[11px] text-slate-400">
                <a href="{{ route('home') }}" class="hover:text-slate-700 transition-colors">
                    Portal Masuk Petugas Internal &rarr;
                </a>
            </div>
        </div>

    </div>

</body>
</html>
