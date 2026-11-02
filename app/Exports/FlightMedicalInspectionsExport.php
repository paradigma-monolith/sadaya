<?php

namespace App\Exports;

use App\Models\FlightMedicalInspection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class FlightMedicalInspectionsExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    protected string $type; // 'alat' | 'obat'
    protected ?string $embarkasi;
    protected ?string $status;

    public function __construct(string $type = 'alat', ?string $embarkasi = null, ?string $status = null)
    {
        $this->type = $type;
        $this->embarkasi = $embarkasi;
        $this->status = $status;
    }

    public function collection()
    {
        $query = FlightMedicalInspection::with('verifier')
            ->where('type', $this->type)
            ->latest('id');

        if ($this->embarkasi) {
            $query->filterEmbarkasi($this->embarkasi);
        }

        if ($this->status) {
            $query->filterStatus($this->status);
        }

        return $query->get()->map(function ($item) {
            $totalP3kUsed = 0;
            if (is_array($item->items_p3k)) {
                foreach ($item->items_p3k as $p) {
                    $totalP3kUsed += (int)($p['terpakai'] ?? 0);
                }
            }

            $totalEmUsed = 0;
            if (is_array($item->items_emergensi)) {
                foreach ($item->items_emergensi as $e) {
                    $totalEmUsed += (int)($e['terpakai'] ?? 0);
                }
            }

            return [
                'No. Registrasi' => $item->registration_no,
                'Kategori' => $item->type === 'alat' ? 'Alat Kesehatan P3K & Emergensi' : 'Obat P3K & Emergensi',
                'Penerbangan Haji' => "{$item->penerbangan_haji_h} H / {$item->penerbangan_haji_m} M",
                'Embarkasi' => $item->embarkasi,
                'Nomor Kloter' => $item->nomor_kloter,
                'No. Kontak' => $item->no_kontak ?? '-',
                'Total P3K Terpakai' => $totalP3kUsed,
                'Total Emergensi Terpakai' => $totalEmUsed,
                'Nama Awak Kabin' => $item->crew_nama,
                'Nama Pelapor / TKHI' => $item->pelapor_nama,
                'Status' => strtoupper($item->status),
                'Diverifikasi Oleh' => $item->verifier?->name ?? '-',
                'Waktu Verifikasi' => $item->verified_at ? $item->verified_at->format('Y-m-d H:i') : '-',
                'Waktu Input' => $item->created_at->format('Y-m-d H:i'),
            ];
        });
    }

    public function headings(): array
    {
        return [
            'No. Registrasi',
            'Kategori',
            'Penerbangan Haji',
            'Embarkasi',
            'Nomor Kloter',
            'No. Kontak',
            'Total P3K Terpakai',
            'Total Emergensi Terpakai',
            'Nama Awak Kabin',
            'Nama Pelapor / TKHI',
            'Status',
            'Diverifikasi Oleh',
            'Waktu Verifikasi',
            'Waktu Input',
        ];
    }
}
