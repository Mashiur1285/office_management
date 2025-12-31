<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    protected $fillable = [
        'name',
        'photo_path',
        'nid_number',
        'nid_file_path',
        'passport_number',
        'passport_file_path',
        'date_of_birth',
        'mobile',
        'district',
        'address',
        'agent_id',
        'job_sector',
        'job_sub_sector',
        'foreign_company_country',
        'foreign_company_name',
        'foreign_company_email',
        'foreign_company_phone',
        'agent_name',
        'agent_reference_number',
        'agent_mobile',
        'bd_company_id',
        'foreign_company_id',
        'medical_fee',
        'medical_report_path',
        'fit_status',
        'fit_report_path',
        'documents_submitted_to',
        'documents_submission_phone',
        'date_of_submission',
        'expected_date_to_collect',
        'documents_collected_date',
        'total_fee',
        'current_due',
        'partial_paid_amount',
        'partial_payment_date',
        'next_payment_amount',
        'next_payment_date',
        'final_payment',
        'final_payment_date',
        'online_status',
        'calling_status',
        'evisa_status',
        'visa_status',
        'visa_stage',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'date_of_submission' => 'date',
            'expected_date_to_collect' => 'date',
            'documents_collected_date' => 'date',
            'partial_payment_date' => 'date',
            'next_payment_date' => 'date',
            'final_payment_date' => 'date',
            'medical_fee' => 'decimal:2',
            'total_fee' => 'decimal:2',
            'current_due' => 'decimal:2',
            'partial_paid_amount' => 'decimal:2',
            'next_payment_amount' => 'decimal:2',
            'final_payment' => 'decimal:2',
        ];
    }

    public function getDurationLeftDaysAttribute(): ?int
    {
        if (! $this->expected_date_to_collect) {
            return null;
        }

        return now()->diffInDays($this->expected_date_to_collect, false);
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }

    public function bdCompany(): BelongsTo
    {
        return $this->belongsTo(BdCompany::class, 'bd_company_id');
    }

    public function foreignCompany(): BelongsTo
    {
        return $this->belongsTo(ForeignCompany::class, 'foreign_company_id');
    }

    public function documentLocation(): HasOne
    {
        return $this->hasOne(DocumentLocation::class);
    }

    public function documentLocationHistories(): HasMany
    {
        return $this->hasMany(DocumentLocationHistory::class)->latest('moved_at');
    }

    public function incomeEntries(): HasMany
    {
        return $this->hasMany(IncomeEntry::class);
    }

    public function costOfSales(): HasMany
    {
        return $this->hasMany(CostOfSale::class);
    }

    public function operatingExpenses(): HasMany
    {
        return $this->hasMany(OperatingExpense::class);
    }

    public function nonOperatingEntries(): HasMany
    {
        return $this->hasMany(NonOperatingEntry::class);
    }

    public function taxEntries(): HasMany
    {
        return $this->hasMany(TaxEntry::class);
    }

    /**
     * Get human-readable visa stage label
     */
    public function getVisaStageLabel(): string
    {
        return match($this->visa_stage) {
            'document_collection' => 'Document Collection',
            'medical_processing' => 'Medical Processing',
            'bd_company_processing' => 'BD Company Processing',
            'online_submission' => 'Online Submission',
            'calling_stage' => 'Calling Stage',
            'evisa_stage' => 'E-Visa Processing',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            default => 'Unknown',
        };
    }

    /**
     * Get visa stage badge color
     */
    public function getVisaStageBadgeClass(): string
    {
        return match($this->visa_stage) {
            'document_collection' => 'bg-gray-100 text-gray-700 ring-gray-200',
            'medical_processing' => 'bg-blue-50 text-blue-700 ring-blue-200',
            'bd_company_processing' => 'bg-purple-50 text-purple-700 ring-purple-200',
            'online_submission' => 'bg-indigo-50 text-indigo-700 ring-indigo-200',
            'calling_stage' => 'bg-cyan-50 text-cyan-700 ring-cyan-200',
            'evisa_stage' => 'bg-amber-50 text-amber-700 ring-amber-200',
            'approved' => 'bg-green-50 text-green-700 ring-green-200',
            'rejected' => 'bg-red-50 text-red-700 ring-red-200',
            default => 'bg-gray-100 text-gray-700 ring-gray-200',
        };
    }

    /**
     * Get visa status badge type for Flowbite
     */
    public function getVisaStatusType(): string
    {
        return match($this->visa_status) {
            'pending' => 'warning',
            'processing' => 'info',
            'completed' => 'success',
            'rejected' => 'danger',
            default => 'default',
        };
    }
}
