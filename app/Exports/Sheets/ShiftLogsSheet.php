<?php

namespace App\Exports\Sheets;

use App\Models\Shift;

class ShiftLogsSheet
{
    protected int $tenantId;

    protected $startDate;

    protected $endDate;

    public function __construct($startDate, $endDate, int $tenantId)
    {
        $this->tenantId = $tenantId;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function title(): string
    {
        return 'Audit Shift Kasir';
    }

    public function collection()
    {
        return Shift::withoutGlobalScopes()->where('tenant_id', $this->tenantId)->whereBetween('start_time', [$this->startDate.' 00:00:00', $this->endDate.' 23:59:59'])
            ->with('user')
            ->orderBy('id', 'desc')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Nama Kasir',
            'Waktu Buka Shift',
            'Waktu Tutup Shift',
            'Uang Modal Awal',
            'Uang Fisik di Laci',
            'Status',
            'Catatan Audit',
        ];
    }

    public function map($row): array
    {
        return [
            $row->user->name ?? 'Tidak Diketahui',
            $row->start_time,
            $row->end_time ?? 'Sedang Aktif',
            'Rp '.number_format($row->cash_start, 0, ',', '.'),
            $row->cash_actual ? 'Rp '.number_format($row->cash_actual, 0, ',', '.') : '-',
            strtoupper($row->status),
            $row->notes ?? '-',
        ];
    }
}
