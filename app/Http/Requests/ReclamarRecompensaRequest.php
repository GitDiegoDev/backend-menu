<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReclamarRecompensaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'telefono' => 'required|string|max:20',
        ];
    }
}
