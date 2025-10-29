<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTeamRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'external_id' => 'nullable|integer|unique:teams,external_id',
            'abbreviation' => 'required|string|max:10|unique:teams,abbreviation',
            'city' => 'nullable|string|max:100',
            'conference' => 'nullable|string|in:East,West',
            'division' => 'nullable|string|max:50',
            'full_name' => 'required|string|max:150',
            'name' => 'required|string|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'abbreviation.required' => 'The team abbreviation is required.',
            'abbreviation.unique' => 'This team abbreviation already exists.',
            'conference.in' => 'Conference must be either East or West.',
            'full_name.required' => 'The full team name is required.',
            'name.required' => 'The team name is required.',
        ];
    }
}
