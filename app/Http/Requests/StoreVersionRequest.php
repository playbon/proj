<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreVersionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'version' => ['required', 'regex:/^\d+\.\d+\.\d+$/'],
            'changelog' => ['nullable'],
        ];
    }
}
