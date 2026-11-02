<?php

namespace App\Exports;

use App\Models\ShipSanitationInspection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ShipInspectionsExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    protected ?string $pelabuhan;
    protected ?string $sertifikat;
    protected ?string $status;

    public function __construct(?string $pelabuhan = null, ?string $sertifikat = null, ?string $status = null)
    {
        $this->pelabuhan = $pelabuhan;
        $this->sertifikat = $sertifikat;
        $this->status = $status;
    }

    public function collection()
    {
        $query = ShipSanitationInspection::with('verifier')->latest('tanggal')->latest('id');

        if ($this->pelabuhan) {
            $query->filterPelabuhan($this->pelabuhan);
        }

        if ($this->sertifikat) {
            $query->filterSertifikat($this->sertifikat);
        }

        if ($this->status) {
            $query->filterStatus($this->status);
        }

        return $query->get()->map(function ($item) {
            return [
                'No. Registrasi' => $item->registration_no,
                'Pelabuhan' => $item->pelabuhan ?? '-',
                'Tanggal' => $item->tanggal ? $item->tanggal->format('Y-m-d') : '-',
                'Jenis Sertifikat' => $item->sertifikat_mencatat === 'Pembebasan' ? 'SSCEC (Pembebasan)' : 'SSCC (Pengendalian)',
                'Nama Kapal' => $item->nama_kapal,
                'IMO / No. Resmi' => $item->imo_no,
                'Bendera' => $item->bendera ?? '-',
                'Jenis Kapal' => $item->jenis_kapal ?? '-',
                'Berat Kotor (GT)' => number_format($item->berat_gt, 0, ',', '.'),
                'Kondisi Palka' => $item->kondisi_palka ?? '-',
                'Pemeriksa Sanitasi' => $item->nama_pemeriksa,
                'Pejabat Penerbit' => $item->pejabat_penerbit ?? '-',
                'Status' => strtoupper($item->status),
                'Diverifikasi Oleh' => $item->verifier?->name ?? '-',
                'Waktu Verifikasi' => $item->verified_at ? $item->verified_at->format('Y-m-d H:i') : '-',
                'Koordinat GPS' => ($item->latitude && $item->longitude) ? "{$item->latitude}, {$item->longitude}" : '-',
                'Waktu Input' => $item->created_at->format('Y-m-d H:i'),
            ];
        });
    }

    public function headings(): array
    {
        return [
            'No. Registrasi',
            'Pelabuhan',
            'Tanggal',
            'Jenis Sertifikat',
            'Nama Kapal',
            'IMO / No. Resmi',
            'Bendera',
            'Jenis Kapal',
            'Berat Kotor (GT)',
            'Kondisi Palka',
            'Pemeriksa Sanitasi',
            'Pejabat Penerbit',
            'Status',
            'Diverifikasi Oleh',
            'Waktu Verifikasi',
            'Koordinat GPS',
            'Waktu Input',
        ];
    }
}

