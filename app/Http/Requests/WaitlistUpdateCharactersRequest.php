<?php

namespace App\Http\Requests;

use App\Enums\WaitlistUpdateCharacterActionType;
use Illuminate\Validation\Rule;

class WaitlistUpdateCharactersRequest extends JoinWaitlistRequest
{
    use HasWaitlistCharacterRules;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return $this->includeDoctrineBasedRules(
            $this->waitlistHasDoctrine(),
            fn () => [
                'action' => [
                    'required',
                    Rule::enum(WaitlistUpdateCharacterActionType::class),
                ],
                ...$this->applyCharacterRules(),
            ]
        );
    }
}
