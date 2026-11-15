<?php

namespace App\Http\Requests\Sivera;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePengendalianSiveraRequest extends FormRequest
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
            'tindakan_pengendalian' => ['required', 'string'],
            'pengendalian_detail' => ['nullable', 'array'],
        ];
    }
}
