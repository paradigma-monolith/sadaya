<?php

namespace App\Http\Requests\Prl;

use Illuminate\Foundation\Http\FormRequest;

class StoreSabRequest extends FormRequest
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
            'nama_sab' => ['required', 'string', 'max:255'],
            'penanggung_jawab' => ['nullable', 'string', 'max:255'],
            'penanggung_jawab_no_hp' => ['nullable', 'string', 'max:30'],
            'sumber_air' => ['nullable', 'string', 'max:255'],
            'kapasitas_penampungan' => ['nullable', 'string', 'max:255'],
        ];
    }
}
