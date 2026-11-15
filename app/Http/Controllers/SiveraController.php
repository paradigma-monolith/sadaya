<?php

namespace App\Http\Controllers;

use App\Http\Requests\Sivera\StoreSiveraRequest;
use App\Http\Requests\Sivera\UpdatePengendalianSiveraRequest;
use App\Models\PrlSiveraVektor;
use App\Services\SiveraService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SiveraController extends Controller
{
    public function __construct(protected SiveraService $siveraService) {}

    /**
     * Display the dedicated SIVERA (Surveilans Vektor dan Reservoar) portal.
     */
    public function index(Request $request): Response
    {
        $search = $request->query('search');
        $wilker = $request->query('wilker');
        $tingkatRisiko = $request->query('tingkat_risiko');
        $tglMulai = $request->query('tgl_mulai', $request->query('tanggal_mulai'));
        $tglSelesai = $request->query('tgl_selesai', $request->query('tanggal_selesai'));
        $tahun = $request->query('tahun');
        $bulan = $request->query('bulan');
        $perPageSivera = min(1000, max(5, (int) $request->input('per_page_sivera', 25)));
        $siveraVektor = $request->input('sivera_vektor', $request->input('vektor', $request->input('jenis_vektor', 'Dashboard')));

        $wilkerOptions = $this->siveraService->getWilkerOptions();

        $filters = [
            'search' => $search,
            'wilker' => $wilker,
            'tingkat_risiko' => $tingkatRisiko,
            'tgl_mulai' => $tglMulai,
            'tgl_selesai' => $tglSelesai,
            'tahun' => $tahun,
            'bulan' => $bulan,
            'sivera_vektor' => $siveraVektor,
        ];

        $qSivera = PrlSiveraVektor::query()->latest('tanggal')->latest('id');
        $this->siveraService->applyFilters($qSivera, $filters);
        $siveraList = $qSivera->paginate($perPageSivera, ['*'], 'page_sivera')->withQueryString();

        return Inertia::render('Sivera/Index', [
            'filters' => array_merge($filters, ['per_page_sivera' => $perPageSivera]),
            'wilker_options' => $wilkerOptions,
            'pemeriksa_options' => $this->siveraService->getPemeriksaOptions(),
            'sivera_dashboard_metrics' => $this->siveraService->getDashboardMetrics(),
            'sivera_dashboard_data' => $this->siveraService->computeDashboardData($wilkerOptions),
            'sivera_list' => $siveraList,
        ]);
    }

    /**
     * Export SIVERA records to Excel-compatible CSV.
     */
    public function exportExcel(): StreamedResponse
    {
        return $this->siveraService->streamCsvExport();
    }

    /**
     * Store new SIVERA Vektor record.
     */
    public function store(StoreSiveraRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $validated['created_by_name'] = $request->user()?->name;
        $sivera = PrlSiveraVektor::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Data Surveilans Vektor (SIVERA) berhasil disimpan.',
            'data' => $sivera,
        ]);
    }

    /**
     * Update an existing SIVERA record.
     */
    public function update(StoreSiveraRequest $request, int $id): JsonResponse
    {
        $sivera = PrlSiveraVektor::findOrFail($id);
        $sivera->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Data Surveilans Vektor (SIVERA) berhasil diperbarui.',
            'data' => $sivera,
        ]);
    }

    /**
     * Update tindakan pengendalian for a SIVERA record.
     */
    public function updatePengendalian(UpdatePengendalianSiveraRequest $request, int $id): JsonResponse
    {
        $sivera = PrlSiveraVektor::findOrFail($id);
        $validated = $request->validated();

        $detail = $sivera->detail_data ?? [];
        if (! empty($validated['pengendalian_detail'])) {
            $detail['pengendalian'] = $validated['pengendalian_detail'];
        }

        $sivera->update([
            'tindakan_pengendalian' => $validated['tindakan_pengendalian'],
            'detail_data' => $detail,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Tindakan pengendalian berhasil disimpan.',
            'data' => $sivera,
        ]);
    }

    /**
     * Delete a SIVERA record.
     */
    public function destroy(int $id): JsonResponse
    {
        $deleted = PrlSiveraVektor::findOrFail($id)->delete();

        return response()->json([
            'success' => (bool) $deleted,
            'message' => 'Data riwayat inspeksi berhasil dihapus.',
        ]);
    }

    /**
     * Print official SIVERA Surveillance & Control PDF report.
     */
    public function printPdf(Request $request, int $id, ?string $tipe = null)
    {
        $row = PrlSiveraVektor::findOrFail($id);
        $fallbackActor = $request->user()?->name ?: 'Petugas Pengendalian';
        $payload = $this->siveraService->buildPdfData(
            $row,
            $tipe,
            SiveraService::getKopSuratBase64(),
            $fallbackActor
        );

        $pdf = Pdf::loadView('pdf.sivera_pdf', $payload)->setPaper('a4', 'portrait');
        $cleanFilename = str_replace([' ', '/', '\\'], '_', "Laporan_{$payload['judul_kegiatan']}_{$row->id}");

        return $pdf->stream("{$cleanFilename}.pdf");
    }

    public function printJentikPdf(Request $request, int $id, ?string $tipe = 'jentik')
    {
        return $this->printPdf($request, $id, $tipe);
    }

    public function printLalatPdf(Request $request, int $id)
    {
        return $this->printPdf($request, $id, 'lalat');
    }
}
