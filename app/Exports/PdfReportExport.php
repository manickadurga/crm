<?php

// app/Exports/PdfReportExport.php

namespace App\Exports;

use Barryvdh\DomPDF\Facade\Pdf;

class PdfReportExport
{
    protected $data;
    protected $filename;

    public function __construct($data, $filename)
    {
        $this->data = $data;
        $this->filename = $filename;
    }

    public function generatePdf()
    {
        $pdf = PDF::loadView('reports.pdf', ['data' => $this->data]);
        return $pdf->download($this->filename);
    }
}
