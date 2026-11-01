<?php

namespace App\Exports;

use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class OrdersReportExport
{
    protected int $tenantId;

    protected $startDate;

    protected $endDate;

    protected $aiAnalysisText;

    public function __construct($startDate, $endDate, $aiAnalysisText, int $tenantId)
    {
        $this->tenantId = $tenantId;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->aiAnalysisText = $aiAnalysisText;
    }

    public function sheets(): array
    {
        return [
            // Kirim teks AI ke dalam FinancialSummarySheet
            new Sheets\FinancialSummarySheet($this->startDate, $this->endDate, $this->aiAnalysisText, $this->tenantId),
            new Sheets\TopProductsSheet($this->startDate, $this->endDate, $this->tenantId),
            new Sheets\ShiftLogsSheet($this->startDate, $this->endDate, $this->tenantId),
        ];
    }

    public function store(string $path): void
    {
        $book = new Spreadsheet;
        $book->removeSheetByIndex(0);
        $temporary = tempnam(sys_get_temp_dir(), 'growpos-report-');
        try {
            foreach ($this->sheets() as $source) {
                $sheet = $book->createSheet();
                $sheet->setTitle($source->title());
                $rows = method_exists($source, 'array') ? $source->array()
                    : array_merge([$source->headings()], $source->collection()->map(fn ($row) => $source->map($row))->all());
                foreach ($rows as $rowIndex => $row) {
                    foreach ($row as $column => $value) {
                        // User-controlled names/notes must never become spreadsheet formulas.
                        $sheet->getCell([$column + 1, $rowIndex + 1])->setValueExplicit(
                            (string) ($value ?? ''), DataType::TYPE_STRING
                        );
                    }
                }
                if (method_exists($source, 'styles')) {
                    $source->styles($sheet);
                }
                foreach ($sheet->getColumnIterator() as $column) {
                    $sheet->getColumnDimension($column->getColumnIndex())->setAutoSize(true);
                }
            }
            (new Xlsx($book))->save($temporary);
            $stream = fopen($temporary, 'rb');
            try {
                if (! Storage::disk('local')->put($path, $stream)) {
                    throw new \RuntimeException('Laporan belum dapat disimpan.');
                }
            } finally {
                fclose($stream);
            }
        } finally {
            $book->disconnectWorksheets();
            if (is_file($temporary)) {
                unlink($temporary);
            }
        }
    }
}
