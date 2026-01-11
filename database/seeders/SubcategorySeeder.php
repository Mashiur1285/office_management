<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SubcategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $incomeSubcategories = [
            'travel_tourism' => [
                ['name' => 'Air Ticket Service Charge', 'vat_rate' => 15],
                ['name' => 'Airline Commission', 'vat_rate' => 0],
                ['name' => 'Tour Package Sale - Inbound', 'vat_rate' => 15],
                ['name' => 'Tour Package Sale - Outbound', 'vat_rate' => 15],
                ['name' => 'Umrah/Hajj Service Costs', 'vat_rate' => 0],
                ['name' => 'Hotel Booking Commission', 'vat_rate' => 0],
                ['name' => 'Visa Processing Fee', 'vat_rate' => 15],
                ['name' => 'Travel Insurance Commission', 'vat_rate' => 0],
                ['name' => 'Re-issue & Cancellation Charge', 'vat_rate' => 15],
            ],
            'manpower_exporting' => [
                ['name' => 'Manpower Training Service Charge', 'vat_rate' => 15],
                ['name' => 'Recruitment Service Fee', 'vat_rate' => 15],
                ['name' => 'Processing Fees', 'vat_rate' => 0],
                ['name' => 'Medical Application Fee', 'vat_rate' => 0],
                ['name' => 'Employer/Foreign Agent Commission', 'vat_rate' => 15],
                ['name' => 'Document/Foreign Agent Commission', 'vat_rate' => 15],
                ['name' => 'Visa Endorsement Service Charge', 'vat_rate' => 15],
                ['name' => 'Other Income Service Charge', 'vat_rate' => 15],
            ],
            'student_package' => [
                ['name' => 'Student File Opening Fee', 'vat_rate' => 15],
                ['name' => 'Admission & Application Fees', 'vat_rate' => 15],
                ['name' => 'University/College Commission', 'vat_rate' => 0],
                ['name' => 'Document Visa Processing Fees', 'vat_rate' => 15],
                ['name' => 'Student Visa Fee', 'vat_rate' => 15],
                ['name' => 'IELTS Reference Commission', 'vat_rate' => 0],
                ['name' => 'Other Reference Commission', 'vat_rate' => 15],
                ['name' => 'Other Commission', 'vat_rate' => 15],
            ],
            'other_income' => [
                ['name' => 'Courier or Documentation Charges', 'vat_rate' => 15],
                ['name' => 'Forex Gain/Loss', 'vat_rate' => 0],
                ['name' => 'Miscellaneous Service Income', 'vat_rate' => 15],
            ],
        ];

        foreach ($incomeSubcategories as $category => $subcategories) {
            foreach ($subcategories as $subcategory) {
                \App\Models\Subcategory::updateOrCreate(
                    [
                        'type' => 'income',
                        'category' => $category,
                        'name' => $subcategory['name'],
                    ],
                    [
                        'vat_rate' => $subcategory['vat_rate'],
                    ]
                );
            }
        }

        // Cost of Sales Subcategories
        $costOfSalesSubcategories = [
            'travel_tourism' => [
                ['name' => 'Cost of Air Tickets Purchased', 'vat_rate' => 0],
                ['name' => 'Hotel & Transportation Costs (Package Tours)', 'vat_rate' => 0],
                ['name' => 'Umrah/Hajj Service Costs', 'vat_rate' => 0],
                ['name' => 'Supplier/GSA Charges', 'vat_rate' => 0],
                ['name' => 'Refund & Chargebacks', 'vat_rate' => 0],
            ],
            'manpower_exporting' => [
                ['name' => 'Medical Examination Costs', 'vat_rate' => 0],
                ['name' => 'BMET Registration & Smart Card Fees', 'vat_rate' => 0],
                ['name' => 'Training & Orientation Costs', 'vat_rate' => 0],
                ['name' => 'Air Ticket Costs for Workers', 'vat_rate' => 0],
                ['name' => 'Agent/Sub-Agent Commission', 'vat_rate' => 0],
                ['name' => 'Visa Processing Fee', 'vat_rate' => 0],
                ['name' => 'Bangladesh Company Commission', 'vat_rate' => 0],
                ['name' => 'Foreign Company Commission', 'vat_rate' => 0],
                ['name' => 'Visa & Attestation Fees', 'vat_rate' => 0],
                ['name' => 'Re-issue & Cancellation Charge', 'vat_rate' => 0],
            ],
            'student_package' => [
                ['name' => 'University Admission Fees', 'vat_rate' => 0],
                ['name' => 'Embassy/Visa Fees', 'vat_rate' => 0],
                ['name' => 'Medical & Health Check Up Costs', 'vat_rate' => 0],
                ['name' => 'Translation & Documentation Costs', 'vat_rate' => 0],
                ['name' => 'Partner Commission Costs', 'vat_rate' => 0],
            ],
        ];

        foreach ($costOfSalesSubcategories as $category => $subcategories) {
            foreach ($subcategories as $subcategory) {
                \App\Models\Subcategory::updateOrCreate(
                    [
                        'type' => 'cost_of_sales',
                        'category' => $category,
                        'name' => $subcategory['name'],
                    ],
                    [
                        'vat_rate' => $subcategory['vat_rate'],
                    ]
                );
            }
        }

        // Operating Expenses Subcategories
        $operatingExpensesSubcategories = [
            'employee_manpower' => [
                ['name' => 'Salaries & Wages', 'vat_rate' => 0],
                ['name' => 'Staff Commission & Incentives', 'vat_rate' => 0],
            ],
            'administrative' => [
                ['name' => 'Office Rent', 'vat_rate' => 0],
                ['name' => 'Utilities & Internet Stationery', 'vat_rate' => 15],
                ['name' => 'Software & Online Account Subscription', 'vat_rate' => 15],
                ['name' => 'Communication Expenses', 'vat_rate' => 15],
            ],
            'selling_marketing' => [
                ['name' => 'Advertising/Sub-Agent Marketing', 'vat_rate' => 15],
                ['name' => 'Travel Fair & Education Expo Costs', 'vat_rate' => 15],
            ],
            'general' => [
                ['name' => 'Bank Charges', 'vat_rate' => 0],
                ['name' => 'Repair & Maintenance', 'vat_rate' => 15],
                ['name' => 'Fuel & Conveyance', 'vat_rate' => 0],
                ['name' => 'Printing & Photocopier', 'vat_rate' => 15],
            ],
        ];

        foreach ($operatingExpensesSubcategories as $category => $subcategories) {
            foreach ($subcategories as $subcategory) {
                \App\Models\Subcategory::updateOrCreate(
                    [
                        'type' => 'operating_expenses',
                        'category' => $category,
                        'name' => $subcategory['name'],
                    ],
                    [
                        'vat_rate' => $subcategory['vat_rate'],
                    ]
                );
            }
        }

        // Non-Operating Subcategories
        $nonOperatingSubcategories = [
            'income' => [
                ['name' => 'Interest Income', 'vat_rate' => 0],
                ['name' => 'Foreign Exchange Gain', 'vat_rate' => 0],
            ],
            'expense' => [
                ['name' => 'Interest on Bank Loan', 'vat_rate' => 0],
                ['name' => 'Foreign Exchange Loss', 'vat_rate' => 0],
                ['name' => 'Penalties & Legal Fees', 'vat_rate' => 0],
            ],
        ];

        foreach ($nonOperatingSubcategories as $category => $subcategories) {
            foreach ($subcategories as $subcategory) {
                \App\Models\Subcategory::updateOrCreate(
                    [
                        'type' => 'non_operating',
                        'category' => $category,
                        'name' => $subcategory['name'],
                    ],
                    [
                        'vat_rate' => $subcategory['vat_rate'],
                    ]
                );
            }
        }
    }
}
