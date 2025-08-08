<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;

class SummaryReportExport implements FromView
{
    protected $startDate;
    protected $endDate;
    protected $combined_data;
    protected $floorFlag;
    protected $floorId;

    public function __construct($startDate, $endDate, $combined_data, $floorFlag, $floorId)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->combined_data = $combined_data;
        $this->floorFlag = $floorFlag;
        $this->floorId = $floorId;
    }

    public function view(): View
    {
        $data = $this->combined_data;
        $floorId = $this->floorId;
        $floorFlag = $this->floorFlag;

        return view('export.summary_report_export', compact('data', 'floorId', 'floorFlag'));
    }
}
