<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Illuminate\Support\Collection;

class LateExport implements FromCollection, WithHeadings, WithTitle, WithStyles, ShouldAutoSize
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
        
        foreach ($this->data['employeeSummary'] as $employeeId => $data) {
            $totalMinutesLate = 0;
            $validCount = 0;
            
            foreach ($data['dates'] as $date) {
                // Add each late occurrence as a row
                $rows[] = [
                    'Employee ID' => $data['employee']->employee_id,
                    'Employee Name' => $data['employee']->full_name,
                    'Department' => $data['employee']->department->name,
                    'Position' => $data['employee']->position ?? 'N/A',
                    'Date' => $date['date'],
                    'Clock In' => $date['clock_in'],
                    'Expected Time' => $data['employee']->shift->start_time,
                    'Minutes Late' => $date['minutes_late'],
                ];
                
                // Calculate total minutes late for summary
                if ($date['minutes_late'] !== '-') {
                    $totalMinutesLate += $date['minutes_late'];
                    $validCount++;
                }
            }
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
            'Position',
            'Date',
            'Clock In',
            'Expected Time',
            'Minutes Late',
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