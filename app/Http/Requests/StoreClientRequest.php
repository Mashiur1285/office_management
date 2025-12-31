<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreClientRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'photo' => ['nullable', 'image', 'max:2048'],
            'nid_number' => ['required', 'string', 'max:100', 'unique:clients,nid_number'],
            'nid_file' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'passport_number' => ['required', 'string', 'max:100', 'unique:clients,passport_number'],
            'passport_file' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'date_of_birth' => ['nullable', 'date'],
            'mobile' => ['nullable', 'string', 'max:50'],
            'district' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
            'job_sector' => ['nullable', 'string', 'max:255'],
            'job_sub_sector' => ['nullable', 'string', 'max:255'],
            'foreign_company_country' => ['nullable', 'string', 'max:255'],
            'foreign_company_name' => ['nullable', 'string', 'max:255'],
            'foreign_company_email' => ['nullable', 'email', 'max:255'],
            'foreign_company_phone' => ['nullable', 'string', 'max:50'],
            'agent_mobile' => ['nullable', 'string', 'max:50'],
            'agent_address' => ['nullable', 'string'],
            'agent_id' => ['required', 'integer', 'exists:agents,id'],
            'bd_company_id' => ['nullable', 'integer', 'exists:bd_companies,id'],
            'foreign_company_id' => ['nullable', 'integer', 'exists:foreign_companies,id'],
            'medical_fee' => ['nullable', 'numeric', 'min:0'],
            'medical_report' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'fit_status' => ['nullable', 'in:pending,fit,unfit'],
            'fit_report' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'documents_submitted_to' => ['nullable', 'string', 'max:255'],
            'documents_submission_phone' => ['nullable', 'string', 'max:50'],
            'date_of_submission' => ['nullable', 'date'],
            'expected_date_to_collect' => ['nullable', 'date'],
            'documents_collected_date' => ['nullable', 'date'],
            'total_fee' => ['required', 'numeric', 'min:0'],
            'current_due' => ['required', 'numeric', 'min:0'],
            'partial_paid_amount' => ['nullable', 'numeric', 'min:0'],
            'partial_payment_date' => ['nullable', 'date'],
            'next_payment_amount' => ['nullable', 'numeric', 'min:0'],
            'next_payment_date' => ['nullable', 'date'],
            'final_payment' => ['nullable', 'numeric', 'min:0'],
            'final_payment_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
            'online_status' => ['nullable', 'in:pending,ok,rejected'],
            'calling_status' => ['nullable', 'in:pending,ok,rejected'],
            'evisa_status' => ['nullable', 'in:pending,ok,rejected'],
        ];
    }

    public function messages(): array
    {
        return [
            'photo.max' => 'Client photo must not be larger than 2MB.',
            'nid_file.max' => 'NID file must not be larger than 5MB.',
            'passport_file.max' => 'Passport file must not be larger than 5MB.',
            'medical_report.max' => 'Medical report must not be larger than 5MB.',
            'fit_report.max' => 'Fit report must not be larger than 5MB.',
            'photo.image' => 'Client photo must be an image file.',
            'nid_file.mimes' => 'NID file must be a PDF or image (jpg, jpeg, png).',
            'passport_file.mimes' => 'Passport file must be a PDF or image (jpg, jpeg, png).',
            'medical_report.mimes' => 'Medical report must be a PDF or image (jpg, jpeg, png).',
            'fit_report.mimes' => 'Fit report must be a PDF or image (jpg, jpeg, png).',
        ];
    }

    protected function prepareForValidation(): void
    {
        $nullableNumericOrDateFields = [
            'date_of_birth',
            'medical_fee',
            'date_of_submission',
            'expected_date_to_collect',
            'documents_collected_date',
            'partial_paid_amount',
            'partial_payment_date',
            'next_payment_amount',
            'next_payment_date',
            'final_payment',
            'final_payment_date',
        ];

        $transformed = [];

        foreach ($nullableNumericOrDateFields as $field) {
            $value = $this->input($field);
            if ($value === '') {
                $transformed[$field] = null;
            }
        }

        if (! empty($transformed)) {
            $this->merge($transformed);
        }
    }
}
