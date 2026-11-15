<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GlobalSearchController extends Controller
{
    /**
     * Perform global full-text search across models using Laravel Scout.
     */
    public function index(Request $request): JsonResponse
    {
        $query = trim((string) $request->input('q', ''));

        if (strlen($query) < 2) {
            return response()->json([
                'query' => $query,
                'results' => [
                    'users' => [],
                    'navigation' => [],
                ],
            ]);
        }

        // Scout full-text search on User model
        $users = User::search($query)
            ->take(5)
            ->get()
            ->map(fn (User $user) => [
                'id' => $user->id,
                'title' => $user->name,
                'subtitle' => $user->email,
                'category' => 'Pengguna',
                'url' => '/dashboard',
            ]);

        // Built-in navigation suggestions (mapped strictly to active Sadaya routes)
        $navItems = collect([
            ['title' => 'Dashboard Utama', 'subtitle' => 'Command Center BKK Kelas I Bandung & 6 Wilker', 'category' => 'Navigasi', 'url' => '/dashboard'],
            ['title' => 'Travel Consent Hub', 'subtitle' => 'Pesawat, Kapal (SSCC/SSCEC), Alkes & Obat P3K', 'category' => 'Alat Angkut & Barang', 'url' => '/travel-consent'],
            ['title' => 'Pengawasan Kapal (TANGKAP)', 'subtitle' => 'Assessment karantina kedatangan & zona merah', 'category' => 'Alat Angkut & Barang', 'url' => '/ship-surveillance'],
            ['title' => 'Formulir Laporan SINKARKES', 'subtitle' => 'Input & ekspor laporan bulanan terpadu', 'category' => 'Alat Angkut & Barang', 'url' => '/sinkarkes'],
            ['title' => 'Sanitasi Lingkungan (TPP / SAB / HSBU)', 'subtitle' => 'Inspeksi tempat pengelolaan pangan, air & bangunan', 'category' => 'Kesehatan Lingkungan', 'url' => '/sanitasi-lingkungan'],
            ['title' => 'SIVERA (Vektor & Reservoar)', 'subtitle' => 'Surveilans jentik, lalat, kecoa, tikus & pinjal', 'category' => 'Kesehatan Lingkungan', 'url' => '/sivera'],
            ['title' => 'Audit Trail Logs', 'subtitle' => 'Riwayat audit trail aktivitas sistem', 'category' => 'Tata Kelola', 'url' => '/audit-logs'],
            ['title' => 'Pusat Import Database', 'subtitle' => 'Migrasi & sinkronisasi database Simply', 'category' => 'Tata Kelola', 'url' => '/database-import'],
            ['title' => 'Dokumentasi API', 'subtitle' => 'OpenAPI Scramble Explorer', 'category' => 'Developer', 'url' => '/docs/api'],
        ])->filter(function ($item) use ($query) {
            return str_contains(strtolower($item['title']), strtolower($query))
                || str_contains(strtolower($item['subtitle']), strtolower($query));
        })->values();

        return response()->json([
            'query' => $query,
            'results' => [
                'users' => $users,
                'navigation' => $navItems,
            ],
        ]);
    }
}
