<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class JoinWaitlistRequest extends FormRequest
{
    use HasWaitlistCharacterRules;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // TODO: Apply filter based on permissions
    }

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
                'characters.*' => $this->characterArrayRule(),
                ...$this->applyCharacterRules('characters.*'),
            ]
        );
    }

    /**
     * Does the waitlist on this request have a doctrine attached to it.
     */
    protected function waitlistHasDoctrine(): bool
    {
        // Get the waitlist that this request is for
        /** @var \App\Models\Waitlist $waitlist */
        $waitlist = $this->route('waitlist');

        return optional($waitlist)->has_doctrine ?? false;
    }
}
