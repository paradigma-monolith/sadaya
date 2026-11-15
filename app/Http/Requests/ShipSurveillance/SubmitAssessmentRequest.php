<?php

namespace App\Http\Requests\ShipSurveillance;

use Illuminate\Foundation\Http\FormRequest;

class SubmitAssessmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'nama_kapal' => ['required', 'string', 'max:255'],
            'datang_dari' => ['required', 'string', 'max:255'],
            'origin_country' => ['nullable', 'string', 'max:100'],
            'pelabuhan' => ['required', 'string', 'max:255'],
            'tanggal_kedatangan' => ['required', 'date'],
            'gross_tonnage' => ['required', 'numeric', 'min:0'],
            'jml_crew' => ['required', 'integer', 'min:1'],
            'kategori' => ['required', 'string', 'in:Dalam Negeri,Luar Negeri'],
            'skoring' => ['nullable', 'integer'],
            'kresiko' => ['nullable', 'string', 'max:50'],
            'hasil_status' => ['nullable', 'string', 'max:50'],
            'surat_pernyataan_nahkoda' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx', 'max:5120'],
            'checklist_dokumen' => ['nullable', 'array'],
        ];
    }
}
