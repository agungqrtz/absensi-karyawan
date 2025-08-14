<?php

namespace App\Exports;

use App\Models\Attendance;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class RecapExport implements FromView, WithTitle, ShouldAutoSize
{
    protected $month;
    protected $year;

    public function __construct(int $month, int $year)
    {
        $this->month = $month;
        $this->year = $year;
    }

    public function view(): View
    {
        $detailData = Attendance::with('employee')
            ->whereYear('date', $this->year)
            ->whereMonth('date', $this->month)
            ->orderBy('date', 'asc')
            ->orderBy('employee_id', 'asc')
            ->get();

        $recapSummary = $detailData->groupBy('employee.name')->map(function ($items) {
            return $items->countBy('status');
        });

        $monthNames = ["Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];
        $monthName = $monthNames[$this->month - 1];

        return view('exports.recap', [
            'recap' => $recapSummary,
            'detail' => $detailData,
            'monthName' => $monthName,
            'year' => $this->year
        ]);
    }

    /**
     * @return string
     */
    public function title(): string
    {
        $monthNames = ["Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];
        return 'Rekap ' . $monthNames[$this->month - 1] . ' ' . $this->year;
    }
}
