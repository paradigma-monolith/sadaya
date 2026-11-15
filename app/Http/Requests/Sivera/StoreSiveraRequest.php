<?php

namespace App\Http\Requests\Sivera;

use Illuminate\Foundation\Http\FormRequest;

class StoreSiveraRequest extends FormRequest
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
            'jenis_vektor' => ['required', 'string', 'max:50'],
            'wilker' => ['required', 'string', 'max:255'],
            'lokasi' => ['required', 'string', 'max:255'],
            'tanggal' => ['required', 'date'],
            'parameter_utama' => ['required', 'string', 'max:255'],
            'nilai_indeks' => ['required', 'numeric'],
            'baku_mutu_status' => ['required', 'string', 'max:50'],
            'tindakan_pengendalian' => ['nullable', 'string'],
            'detail_data' => ['nullable', 'array'],
        ];
    }
}
