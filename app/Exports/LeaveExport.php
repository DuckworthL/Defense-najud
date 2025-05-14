<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Illuminate\Support\Collection;

class LeaveExport implements FromCollection, WithHeadings, WithTitle, WithStyles, ShouldAutoSize
{
    protected $data;
    
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * @return Collection
     */
    public function collection()
    {
        $rows = [];
        
        foreach ($this->data['leaves'] as $leave) {
            $rows[] = [
                'Employee ID' => $leave->employee->employee_id,
                'Employee Name' => $leave->employee->full_name,
                'Department' => $leave->employee->department->name,
                'Leave Type' => $leave->leave_type,
                'Start Date' => $leave->start_date->format('Y-m-d'),
                'End Date' => $leave->end_date->format('Y-m-d'),
                'Duration (Days)' => $leave->duration,
                'Status' => ucfirst($leave->status),
                'With Pay (Days)' => $leave->with_pay_days ?? '-',
                'Without Pay (Days)' => $leave->without_pay_days ?? '-',
                'Approved By' => $leave->approver ? $leave->approver->name : '-',
                'Reason' => $leave->reason,
            ];
        }
        
        return new Collection($rows);
    }
    
    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Employee ID',
            'Employee Name',
            'Department',
            'Leave Type',
            'Start Date',
            'End Date',
            'Duration (Days)',
            'Status',
            'With Pay (Days)',
            'Without Pay (Days)',
            'Approved By',
            'Reason',
        ];
    }
    
    /**
     * @return string
     */
    public function title(): string
    {
        return $this->data['title'];
    }
    
    /**
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row (headings)
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E2EFDA'],
                ],
            ],
        ];
    }
}