<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;

class CustomersExport implements FromArray
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function array(): array
    {
        $criteria = array_map(function ($item) {
            return (array) $item;
        }, $this->data['criteria']);

        $grouping = array_map(function ($item) {
            return (array) $item;
        }, $this->data['grouping']);

        $selectColumns = array_map(function ($item) {
            return (array) $item;
        }, $this->data['select_columns']);

        return [
            ['Criteria', '', '', ''],
            ...$criteria,
            ['Grouping', '', '', ''],
            ...$grouping,
            ['Select Columns', '', '', ''],
            ...$selectColumns,
        ];
    }
}
