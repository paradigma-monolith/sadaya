<?php

namespace App\Http\Requests\Prl;

use Illuminate\Foundation\Http\FormRequest;

class StoreTppRequest extends FormRequest
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
            'nama_tpp' => ['required', 'string', 'max:255'],
            'penanggung_jawab' => ['nullable', 'string', 'max:255'],
            'jml_karyawan' => ['nullable', 'string', 'max:255'],
            'kapasitas_produksi' => ['nullable', 'string', 'max:255'],
        ];
    }
}
