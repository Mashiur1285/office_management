<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization handled by middleware
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'app_name' => ['required', 'string', 'max:255'],
            'logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,svg', 'max:2048'], // 2MB
            'company_address' => ['nullable', 'string', 'max:1000'],
            'company_phone' => ['nullable', 'string', 'max:255'],
            'company_email' => ['nullable', 'string', 'max:255'],
            'company_tagline' => ['nullable', 'string', 'max:255'],
            'footer_note' => ['nullable', 'string', 'max:1000'],
            'letterhead_enabled' => ['boolean'],
            'letterhead_top_gap' => ['nullable', 'numeric', 'min:0', 'max:10'],
            'header_image' => ['nullable', 'image', 'mimes:png,jpg,jpeg,svg', 'max:3072'], // 3MB
            'remove_header_image' => ['boolean'],
            'watermark_enabled' => ['boolean'],
            'watermark_text' => ['nullable', 'string', 'max:255'],
            'watermark_type' => ['nullable', 'in:text,image'],
            'watermark_image' => ['nullable', 'image', 'mimes:png,jpg,jpeg,svg', 'max:3072'], // 3MB
            'remove_watermark_image' => ['boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'letterhead_enabled' => $this->boolean('letterhead_enabled'),
            'watermark_enabled' => $this->boolean('watermark_enabled'),
            'remove_header_image' => $this->boolean('remove_header_image'),
            'remove_watermark_image' => $this->boolean('remove_watermark_image'),
        ]);
    }

    /**
     * Get custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'app_name.required' => 'Application name is required.',
            'logo.image' => 'Logo must be an image file.',
            'logo.max' => 'Logo must not exceed 2MB.',
        ];
    }
}
