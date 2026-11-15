<?php

namespace App\Http\Requests\Prl;

use Illuminate\Foundation\Http\FormRequest;

class StoreInspeksiHsbuRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'wilker' => ['required', 'string', 'max:255'],
            'tanggal_inspeksi' => ['required', 'date'],
            'nama_gedung' => ['required', 'string', 'max:255'],
            'prl_hsbu_id' => ['nullable', 'integer'],
            'penanggung_jawab_hsbu' => ['nullable', 'string', 'max:255'],
            'jml_karyawan_hsbu' => ['nullable', 'string', 'max:255'],
            'nama_pemeriksa' => ['nullable', 'string', 'max:255'],
            'komponen_penilaian' => ['nullable', 'array'],
            'total' => ['required', 'numeric'],
            'tingkat_risiko' => ['required', 'string', 'in:Rendah,Sedang,Tinggi'],
            'saran_rencana_tindakan' => ['nullable', 'string'],
            'catatan' => ['nullable', 'string'],
        ];
    }
}
