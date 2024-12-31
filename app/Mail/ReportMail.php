<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ReportMail extends Mailable
{
    use Queueable, SerializesModels;

    public $fileContent;
    public $fileFormat;
    public $reportName;

    public function __construct($fileContent, $fileFormat, $reportName)
    {
        $this->fileContent = $fileContent;
        $this->fileFormat = $fileFormat;
        $this->reportName = $reportName;
    }

    public function build()
    {
        $fileName = $this->reportName . '.' . $this->fileFormat;

        return $this->subject('Your Requested Report')
            ->view('emails.report')
            ->attachData($this->fileContent, $fileName, [
                'mime' => $this->getMimeType($this->fileFormat),
            ]);
    }

    protected function getMimeType($format)
    {
        switch ($format) {
            case 'csv':
                return 'text/csv';
            case 'xlsx':
                return 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
            default:
                return 'application/octet-stream';
        }
    }
}