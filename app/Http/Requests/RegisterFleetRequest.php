<?php

namespace App\Http\Requests;

use App\Models\Character;
use App\Models\Fleet;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterFleetRequest extends FormRequest
{
    /**
     * The regex used to validate the fleet url and extract the fleet ID.
     */
    public const ESI_FLEET_URL_REGEX = '/^https:\/\/esi\.evetech\.net\/v1\/fleets\/(\d+)\/\?.*/i';

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
            'url' => [
                'nullable',
                'required_if:fleet_boss,null',
                'regex:'.self::ESI_FLEET_URL_REGEX,
            ],
            'fleet_boss' => [
                'nullable',
                'required_if:url,null',
                'numeric',
                Rule::exists(Character::class, 'id')->where('user_id', $this->user()->id),
                Rule::notIn(
                    Fleet::with('boss')
                        ->whereTracked()
                        ->get()
                        ->map(fn ($fleet) => $fleet->boss->id)
                ),
            ],
            'name' => 'required|string|max:150',
        ];
    }
}
