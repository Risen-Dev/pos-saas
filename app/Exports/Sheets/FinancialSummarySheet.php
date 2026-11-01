<?php

namespace App\Exports\Sheets;

use App\Services\SalesDataset;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class FinancialSummarySheet
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

    public function title(): string
    {
        return 'Ringkasan Finansial';
    }

    public function array(): array
    {
        // 1. Ambil data finansial dari database
        $dataData = app(SalesDataset::class)->between($this->tenantId, $this->startDate.' 00:00:00', $this->endDate.' 23:59:59')
            ->select(
                DB::raw('COUNT(id) as total_tx'),
                DB::raw('SUM(subtotal) as total_gross'),
                DB::raw('SUM(discount + loyalty_discount) as total_discount'),
                DB::raw('SUM(tax) as total_tax'),
                DB::raw('SUM(grand_total) as total_net')
            )->first();

        $output = [];

        // ==========================================
        // BAGIAN A: STRUKTUR NARASI SMART AI REPORT
        // ==========================================
        $output[] = ['RINGKASAN FINANSIAL'];
        $output[] = ['Transaksi lunas sesuai periode, sebelum retur dan biaya operasional.'];
        $output[] = ['']; // Baris kosong

        $cleanText = str_replace(['**', '###', '`', '*'], '', $this->aiAnalysisText);
        $aiLines = explode("\n", $cleanText);

        foreach ($aiLines as $line) {
            $trimmed = trim($line);
            if (! empty($trimmed)) {
                $output[] = [$trimmed];
            }
        }

        // Memberi jarak pemisah yang cukup antara laporan AI dan Tabel Angka
        $output[] = [''];
        $output[] = [''];

        // ==========================================
        // BAGIAN B: TABEL ANGKA FINANSIAL ASLI
        // ==========================================
        $output[] = ['DATA OPERASIONAL & RINGKASAN FINANSIAL'];
        $output[] = [
            'Total Transaksi (Nota)',
            'Omzet Kotor (Subtotal)',
            'Total Potongan Diskon',
            'Pajak Outlet Terkumpul',
            'Penjualan Lunas Sebelum Retur (termasuk pajak)',
        ];

        if ($dataData) {
            $output[] = [
                $dataData->total_tx.' Transaksi',
                'Rp '.number_format($dataData->total_gross, 0, ',', '.'),
                'Rp '.number_format($dataData->total_discount, 0, ',', '.'),
                'Rp '.number_format($dataData->total_tax, 0, ',', '.'),
                'Rp '.number_format($dataData->total_net, 0, ',', '.'),
            ];
        } else {
            $output[] = ['0 Transaksi', 'Rp 0', 'Rp 0', 'Rp 0', 'Rp 0'];
        }

        return $output;
    }

    public function styles(Worksheet $sheet)
    {
        // --- Styling Bagian AI (Atas) ---
        $sheet->mergeCells('A1:E1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(12)->getColor()->setARGB('FFFFFF');
        $sheet->getStyle('A1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('1E3A8A'); // Navy

        $sheet->mergeCells('A2:E2');
        $sheet->getStyle('A2')->getFont()->setItalic(true)->setSize(9)->getColor()->setARGB('6B7280'); // Abu-abu

        // Wrap text untuk narasi AI dari baris 4 sampai baris 10 agar paragrafnya rapi melar ke bawah
        $sheet->getStyle('A4:A12')->getAlignment()->setWrapText(true);
        $sheet->getStyle('A4:A12')->getFont()->setSize(10.5)->getColor()->setARGB('374151');

        // --- Styling Tabel Data Finansial (Bawah) ---
        // Kita cari dinamis baris tempat tabel finansial berada (dua baris sebelum akhir)
        $highestRow = $sheet->getHighestRow();
        $headerTabelRow = $highestRow - 1;
        $dataTabelRow = $highestRow;
        $judulTabelRow = $highestRow - 2;

        // Gaya Judul Tabel Operasional
        $sheet->getStyle('A'.$judulTabelRow)->getFont()->setBold(true)->setSize(11)->getColor()->setARGB('111827');

        // Gaya Header Tabel (Hijau Emerald Mewah)
        $sheet->getStyle('A'.$headerTabelRow.':E'.$headerTabelRow)->getFont()->setBold(true)->getColor()->setARGB('FFFFFF');
        $sheet->getStyle('A'.$headerTabelRow.':E'.$headerTabelRow)->getFill()
            ->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('047857');

        // Gaya Baris Angka Keuangan
        $sheet->getStyle('A'.$dataTabelRow.':E'.$dataTabelRow)->getFont()->setBold(true)->getColor()->setARGB('111827');

        // Lebarkan Kolom A agar teks narasi AI memiliki ruang baca yang sangat luas dan nyaman
        $sheet->getColumnDimension('A')->setWidth(85);

        return [];
    }
}
