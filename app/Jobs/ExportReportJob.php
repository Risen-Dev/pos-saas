<?php

namespace App\Jobs;

use App\Exports\OrdersReportExport;
use App\Models\ReportExport;
use App\Services\GeminiService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class ExportReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $startDate;

    protected $endDate;

    protected $reportExportId;

    public function __construct($startDate, $endDate, $reportExportId)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->reportExportId = $reportExportId;
    }

    public function handle(GeminiService $gemini)
    {
        // 1. Ambil baris rekam ekspor dan ubah status menjadi processing
        $reportExport = ReportExport::find($this->reportExportId);
        if (! $reportExport) {
            return;
        }

        $reportExport->update(['status' => 'processing']);

        if (! $reportExport->tenant_id) {
            $reportExport->update(['status' => 'failed']);

            return;
        }
        // The tenant is captured when requested, never inferred from a user's current outlet.
        $fileName = 'exports/'.$reportExport->tenant_id.'/'.Str::uuid().'.xlsx';
        $description = 'Ringkasan berdasarkan transaksi lunas. Analisis AI tidak dijalankan otomatis.';
        (new OrdersReportExport($this->startDate, $this->endDate, $description, $reportExport->tenant_id))->store($fileName);

        // 5. Perbarui status menjadi completed dan pasang path file untuk diunduh
        $reportExport->update([
            'status' => 'completed',
            'file_path' => $fileName,
        ]);
    }

    public function failed(\Throwable $exception)
    {
        ReportExport::find($this->reportExportId)?->update(['status' => 'failed']);
    }
}
