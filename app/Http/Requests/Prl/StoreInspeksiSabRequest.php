<?php

namespace App\Http\Requests\Prl;

use Illuminate\Foundation\Http\FormRequest;

class StoreInspeksiSabRequest extends FormRequest
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
            'nama_sab' => ['required', 'string', 'max:255'],
            'prl_sab_id' => ['nullable', 'integer'],
            'penanggung_jawab_sab' => ['nullable', 'string', 'max:255'],
            'penanggung_jawab_no_hp_sab' => ['nullable', 'string', 'max:30'],
            'sumber_air_sab' => ['nullable', 'string', 'max:255'],
            'kapasitas_penampungan_sab' => ['nullable', 'string', 'max:255'],
            'nama_pemeriksa' => ['nullable', 'string', 'max:255'],
            'sec_b_satu' => ['nullable', 'string', 'max:10'],
            'sec_b_dua' => ['nullable', 'string', 'max:10'],
            'sec_b_tiga' => ['nullable', 'string', 'max:10'],
            'sec_b_empat' => ['nullable', 'string', 'max:10'],
            'sec_c_satu' => ['nullable', 'string', 'max:10'],
            'sec_c_dua' => ['nullable', 'string', 'max:10'],
            'sec_c_tiga' => ['nullable', 'string', 'max:10'],
            'sec_c_empat' => ['nullable', 'string', 'max:10'],
            'sec_c_lima' => ['nullable', 'string', 'max:10'],
            'sec_c_enam' => ['nullable', 'string', 'max:10'],
            'sec_c_tujuh' => ['nullable', 'string', 'max:10'],
            'sec_c_delapan' => ['nullable', 'string', 'max:10'],
            'sec_c_sembilan' => ['nullable', 'string', 'max:10'],
            'sec_c_sepuluh' => ['nullable', 'string', 'max:10'],
            'hasil' => ['required', 'integer'],
            'tingkat_risiko' => ['required', 'string'],
            'saran_rencana_tindakan' => ['nullable', 'string'],
        ];
    }
}
