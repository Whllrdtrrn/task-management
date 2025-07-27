<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TaskRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'status' => ['sometimes', Rule::in(['pending', 'completed'])],
            'priority' => ['sometimes', Rule::in(['low', 'medium', 'high'])],
            'order' => ['sometimes', 'integer', 'min:0'],
        ];
    }

    protected function prepareForValidation()
    {
        // Sanitize inputs
        $this->merge([
            'title' => strip_tags($this->title),
            'description' => strip_tags($this->description),
        ]);
    }
}