<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePlayerRequest extends FormRequest
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
        $playerId = $this->route('player');

        return [
            'external_id' => [
                'nullable',
                'integer',
                Rule::unique('players', 'external_id')->ignore($playerId),
            ],
            'first_name' => 'sometimes|required|string|max:100',
            'last_name' => 'sometimes|required|string|max:100',
            'position' => 'nullable|string|max:10',
            'height' => 'nullable|string|max:10',
            'weight' => 'nullable|string|max:10',
            'jersey_number' => 'nullable|string|max:10',
            'college' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'draft_year' => 'nullable|integer|min:1900|max:' . (date('Y') + 1),
            'draft_round' => 'nullable|integer|min:1|max:10',
            'draft_number' => 'nullable|integer|min:1|max:100',
            'team_id' => 'nullable|exists:teams,id',
        ];
    }

    public function messages(): array
    {
        return [
            'team_id.exists' => 'The selected team does not exist.',
            'draft_year.min' => 'Draft year must be after 1900.',
            'draft_year.max' => 'Draft year cannot be in the future.',
        ];
    }
}
