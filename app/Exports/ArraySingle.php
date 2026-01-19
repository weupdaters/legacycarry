<?php
namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ArraySingle implements FromArray, WithHeadings
{
    protected $columns;
    protected $rows;

    public function __construct(array $columns, array $rows)
    {
        $this->columns = $columns;
        $this->rows    = $rows;
    }

    // Excel headings
    public function headings(): array
    {
        return $this->columns;
    }

    // Excel rows
    public function array(): array
    {
        return $this->rows;
    }
}



