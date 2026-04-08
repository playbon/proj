<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePackageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['nullable', 'max:255'],
            'description' => ['nullable'],
            'channel_id' => ['nullable', 'exists:channels,id'],
        ];
    }
}
