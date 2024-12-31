<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ReportExport implements FromArray, WithHeadings
{
    protected $data;
    protected $formattedColumns;

    public function __construct(array $data, array $formattedColumns)
    {
        $this->data = $data;
        $this->formattedColumns = $formattedColumns;
    }

    public function array(): array
    {
        // Ensure formatted columns are correctly structured
        $columns = collect($this->formattedColumns)->pluck('field')->toArray();
        
        return array_map(function ($row) use ($columns) {
            // Ensure correct column order and handle missing data
            $orderedRow = [];
            foreach ($columns as $column) {
                $orderedRow[] = $row[$column] ?? ''; // Ensure correct column value
            }
            return $orderedRow;
        }, $this->data);
    }

    public function headings(): array
    {
        // Ensure column headings are correctly mapped
        return collect($this->formattedColumns)->pluck('label')->toArray();
    }
}
