<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTeamRequest extends FormRequest
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
        $teamId = $this->route('team');

        return [
            'external_id' => [
                'nullable',
                'integer',
                Rule::unique('teams', 'external_id')->ignore($teamId),
            ],
            'abbreviation' => [
                'sometimes',
                'required',
                'string',
                'max:10',
                Rule::unique('teams', 'abbreviation')->ignore($teamId),
            ],
            'city' => 'nullable|string|max:100',
            'conference' => 'nullable|string|in:East,West',
            'division' => 'nullable|string|max:50',
            'full_name' => 'sometimes|required|string|max:150',
            'name' => 'sometimes|required|string|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'abbreviation.unique' => 'This team abbreviation already exists.',
            'conference.in' => 'Conference must be either East or West.',
        ];
    }
}
