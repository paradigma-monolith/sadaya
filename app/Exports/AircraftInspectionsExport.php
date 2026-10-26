<?php

namespace App\Exports;

use App\Models\AircraftSanitationInspection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class AircraftInspectionsExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    protected $kesimpulan;
    protected $status;

    public function __construct(?string $kesimpulan = null, ?string $status = null)
    {
        $this->kesimpulan = $kesimpulan;
        $this->status = $status;
    }

    public function collection()
    {
        $query = AircraftSanitationInspection::latest('tanggal');

        if ($this->kesimpulan) {
            $query->filterKesimpulan($this->kesimpulan);
        }

        if ($this->status) {
            $query->filterStatus($this->status);
        }

        return $query->get()->map(function ($item) {
            return [
                'No. Registrasi' => $item->registration_no,
                'Pemilik / Maskapai' => $item->pemilik,
                'Tanda Kebangsaan & Reg' => $item->tanda_kebangsaan,
                'Tanggal' => $item->tanggal ? $item->tanggal->format('Y-m-d') : '-',
                'Pukul' => $item->waktu,
                'Kelaikan' => strtoupper($item->kesimpulan),
                'Tindakan Penyehatan' => $item->tindakan ? ucwords(str_replace('_', ' ', $item->tindakan)) : 'Tidak Perlu',
                'Status' => strtoupper($item->status),
                'Petugas Kabin' => $item->petugas_kabin_nama,
                'Petugas BKK' => $item->petugas_bkk_nama,
                'Koordinat GPS' => ($item->latitude && $item->longitude) ? "{$item->latitude}, {$item->longitude}" : '-',
                'Jumlah Foto' => is_array($item->foto_dokumentasi) ? count($item->foto_dokumentasi) : 0,
                'Waktu Submit' => $item->created_at->format('Y-m-d H:i'),
            ];
        });
    }

    public function headings(): array
    {
        return [
            'No. Registrasi',
            'Pemilik / Maskapai',
            'Tanda Kebangsaan & Reg',
            'Tanggal',
            'Pukul',
            'Kelaikan Sanitasi',
            'Tindakan Penyehatan',
            'Status Approval',
            'Petugas Kabin (Crew)',
            'Petugas BKK Bandung',
            'Koordinat GPS',
            'Jumlah Foto Geotag',
            'Waktu Input Sistem',
        ];
    }
}
