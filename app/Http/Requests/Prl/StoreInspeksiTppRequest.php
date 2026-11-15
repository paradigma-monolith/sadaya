<?php

namespace App\Http\Requests\Prl;

use Illuminate\Foundation\Http\FormRequest;

class StoreInspeksiTppRequest extends FormRequest
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
            'nama_tpp' => ['required', 'string', 'max:255'],
            'prl_tpp_id' => ['nullable', 'integer'],
            'penanggung_jawab_tpp' => ['nullable', 'string', 'max:255'],
            'jml_karyawan_tpp' => ['nullable', 'string', 'max:255'],
            'kapasitas_produksi_tpp' => ['nullable', 'string', 'max:255'],
            'nama_pemeriksa' => ['nullable', 'string', 'max:255'],
            'sec_a_satu' => ['nullable', 'string', 'max:10'],
            'sec_a_dua' => ['nullable', 'string', 'max:10'],
            'sec_a_tiga' => ['nullable', 'string', 'max:10'],
            'sec_a_skor' => ['nullable', 'integer'],
            'sec_b_satu' => ['nullable', 'string', 'max:10'],
            'sec_b_dua' => ['nullable', 'string', 'max:10'],
            'sec_b_tiga' => ['nullable', 'string', 'max:10'],
            'sec_b_skor' => ['nullable', 'integer'],
            'sec_c_skor' => ['nullable', 'integer'],
            'sec_c_keterangan' => ['nullable', 'string', 'max:255'],
            'sec_d_nilai_inspeksi_sebelumnya' => ['nullable', 'integer'],
            'sec_d_skor' => ['nullable', 'integer'],
            'hasil' => ['required', 'integer'],
            'tingkat_risiko' => ['required', 'string', 'in:Rendah,Sedang,Tinggi'],
            'kesimpulan' => ['nullable', 'string'],
            'makanan' => ['nullable', 'string'],
            'saran_rencana_tindakan' => ['nullable', 'string'],
        ];
    }
}
