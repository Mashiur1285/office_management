<?php

namespace App\Exports;

use App\Models\CostOfSale;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class CostOfSalesExport implements FromCollection, WithHeadings, WithMapping
{
    protected $entries;

    public function __construct($entries)
    {
        $this->entries = $entries;
    }

    public function collection()
    {
        return $this->entries;
    }

    public function headings(): array
    {
        return [
            'Subcategory',
            'Client',
            'Description',
            'Amount',
            'Date',
        ];
    }

    public function map($entry): array
    {
        return [
            $entry->subcategory,
            optional($entry->client)->name ?? 'N/A',
            $entry->description,
            $entry->amount,
            $entry->created_at->format('Y-m-d H:i'),
        ];
    }
}
