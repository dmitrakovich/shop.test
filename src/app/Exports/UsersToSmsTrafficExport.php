<?php

namespace App\Exports;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Contracts\Support\Responsable;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class UsersToSmsTrafficExport implements FromQuery, WithHeadings, WithColumnFormatting, WithMapping, ShouldAutoSize, Responsable
{
    use Exportable;

    private string $fileName;

    public function __construct(private readonly Builder $query)
    {
        $this->setFileName();
    }

    public function headings(): array
    {
        return [
            'phone',
            'name',
            'birthday',
            'comment',
        ];
    }

    public function query(): Builder
    {
        return $this->query->select([
            'phone',
            'first_name',
            'last_name',
            'patronymic_name',
            'birth_date',
        ]);
    }

    /**
     * @param \App\Models\User\User $user
     */
    public function map($user): array
    {
        return [
            ltrim($user->phone, '+'),
            $user->first_name,
            $user->birth_date,
            $user->getFullName(),
        ];
    }

    public function columnFormats(): array
    {
        return [
            'A' => \PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER,
        ];
    }

    private function setFileName(): void
    {
        $this->fileName = 'users_to_sms_traffic_' . now()->format('Ymd') . '.xlsx';
    }
}
