<?php

namespace App\Traits;

use App\Exports\GlobalExport;
use Maatwebsite\Excel\Facades\Excel;

trait HasGlobalExport
{
    public function exportGlobal($data, $columns, $fields, $filename = 'export.xlsx')
{
    // ⚠ Ensure rows are plain arrays
    if ($data instanceof \Illuminate\Support\Collection) {
        $data = $data->toArray();
    }

    // ⚠ If a single row is passed, wrap it
    if (!empty($data) && !is_array(reset($data))) {
        // e.g. ['1'] → [['1']]
        $data = [ $data ];
    }

    // ⚠ Ensure each row is an indexed array (for Blade looping)
    $cleanRows = [];
    foreach ($data as $row) {
        if ($row instanceof \Illuminate\Support\Collection) {
            $row = $row->toArray();
        }

        // Convert associative → numeric order based on $columns
        if (array_keys($row) !== range(0, count($row) - 1)) {
            $row = array_values($row);
        }

        $cleanRows[] = $row;
    }

    return Excel::download(
        new GlobalExport('exports.table', [
            'columns' => $columns,
            'fields'  => $fields,
            'rows'    => $cleanRows   // use cleaned rows
        ]),
        $filename
    );
}

}
