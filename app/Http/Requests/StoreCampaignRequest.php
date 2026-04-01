<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreCampaignRequest extends FormRequest
{
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'integration_mode' => ['required', 'string', 'in:front_controller,reverse_integration,block_integration'],
            'target_mode' => ['required', 'string', 'in:redirect,content'],
            'target_redirect_url' => ['nullable', 'url', 'max:2048', 'required_if:target_mode,redirect'],
            'target_content_file' => ['nullable', 'string', 'max:255', 'required_if:target_mode,content'],
            'bot_content_file' => ['nullable', 'string', 'max:255', 'required_if:integration_mode,front_controller'],
            'all_countries' => ['required', 'boolean'],
            'is_active' => ['required', 'boolean'],
            'settings_json' => ['nullable', 'array'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['string', 'max:100'],
            'target_geos' => ['nullable', 'array'],
            'target_geos.*.country_code' => ['required', 'string', 'regex:/^(ALL|[A-Z]{2})$/'],
            'target_geos.*.country_name' => ['required', 'string', 'max:120'],
        ];
    }
}
