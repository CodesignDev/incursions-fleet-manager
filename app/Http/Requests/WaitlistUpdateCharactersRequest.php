<?php

namespace App\Http\Requests;

use App\Enums\WaitlistUpdateCharacterActionType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class WaitlistUpdateCharactersRequest extends FormRequest
{
    use HasWaitlistCharacterRules;

    /**
     * Determine if the user is authorized to make this request.
     */
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
            'action' => [
                'required',
                Rule::enum(WaitlistUpdateCharacterActionType::class),
            ],
            ...$this->applyCharacterRules(),
        ];
    }
}
