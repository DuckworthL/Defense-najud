<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Illuminate\Support\Collection;

class AttendanceExport implements FromCollection, WithHeadings, WithTitle, WithStyles, ShouldAutoSize
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
        if (isset($this->data['attendanceData'])) {
            // Daily attendance report
            $rows = [];
            
            foreach ($this->data['attendanceData'] as $employeeId => $data) {
                $employee = $data['employee'];
                
                foreach ($this->data['period'] as $date) {
                    $dateString = $date->format('Y-m-d');
                    $attendance = $data['attendance'][$dateString];
                    
                    $rows[] = [
                        'Date' => $dateString,
                        'Employee ID' => $employee->employee_id,
                        'Employee Name' => $employee->full_name,
                        'Department' => $employee->department->name,
                        'Status' => $attendance['status'],
                        'Clock In' => $attendance['clock_in'],
                        'Clock Out' => $attendance['clock_out'],
                        'Work Hours' => $attendance['work_hours'],
                        'Remarks' => $attendance['remarks'],
                    ];
                }
            }
            
            return new Collection($rows);
        } else {
            // Summary attendance report
            $rows = [];
            
            foreach ($this->data['summaryData'] as $employeeId => $data) {
                $rows[] = [
                    'Employee ID' => $data['employee']->employee_id,
                    'Employee Name' => $data['employee']->full_name,
                    'Department' => $data['employee']->department->name,
                    'Present Days' => $data['present'],
                    'Late Days' => $data['late'],
                    'Early Departure Days' => $data['early_departure'] ?? 0,
                    'Absent Days' => $data['absent'],
                    'Leave Days' => $data['leave'],
                    'Total Days' => $data['total_days'],
                    'Attendance Rate (%)' => $data['attendance_rate'],
                ];
            }
            
            return new Collection($rows);
        }
    }
    
    /**
     * @return array
     */
    public function headings(): array
    {
        if (isset($this->data['attendanceData'])) {
            // Daily attendance report
            return [
                'Date',
                'Employee ID',
                'Employee Name',
                'Department',
                'Status',
                'Clock In',
                'Clock Out',
                'Work Hours',
                'Remarks',
            ];
        } else {
            // Summary attendance report
            return [
                'Employee ID',
                'Employee Name',
                'Department',
                'Present Days',
                'Late Days',
                'Early Departure Days',
                'Absent Days',
                'Leave Days',
                'Total Days',
                'Attendance Rate (%)',
            ];
        }
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