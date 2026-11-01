<?php

namespace Tests\Feature\Pos;

use App\Exports\Sheets\ShiftLogsSheet;
use App\Exports\Sheets\TopProductsSheet;
use App\Jobs\ExportReportJob;
use App\Models\ReportExport;
use App\Services\GeminiService;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ReportIsolationTest extends PosTestCase
{
    public function test_reports_page_builds_cost_queries_without_limited_order_subqueries(): void
    {
        $user = $this->shop();
        $product = $this->product($user, ['cost_price' => 6000, 'sell_price' => 10000]);
        $this->shift($user);
        $this->actingAs($user)->postJson('/pos', $this->checkout($product))->assertOk();

        $this->get('/reports')->assertOk()
            ->assertSee('Penjualan lunas')
            ->assertSee('Rp 10.000');
    }

    public function test_queued_export_keeps_original_tenant_and_is_private_without_auth(): void
    {
        Storage::fake('local');
        Storage::fake('public');
        $user = $this->shop();
        $other = $this->shop();
        $product = $this->product($user, ['product_name' => '=HYPERLINK("https://example.invalid","Barang toko A")']);
        $otherProduct = $this->product($other, ['product_name' => 'Rahasia toko B']);
        $this->shift($user);
        $this->shift($other);
        $this->actingAs($user)->postJson('/pos', $this->checkout($product))->assertOk();
        $this->actingAs($other)->postJson('/pos', $this->checkout($otherProduct))->assertOk();
        $export = ReportExport::create(['tenant_id' => $user->tenant_id, 'user_id' => $user->id, 'report_type' => 'Test', 'start_date' => today(), 'end_date' => today(), 'status' => 'pending']);
        auth()->forgetGuards();
        $sheet = new TopProductsSheet(today()->toDateString(), today()->toDateString(), $user->tenant_id);
        $this->assertEquals([$product->product_name], $sheet->collection()->pluck('product_name')->all());
        $this->assertCount(1, (new ShiftLogsSheet(today()->toDateString(), today()->toDateString(), $user->tenant_id))->collection());
        (new ExportReportJob(today()->toDateString(), today()->toDateString(), $export->id))->handle(app(GeminiService::class));
        $this->assertEquals('completed', $export->fresh()->status);
        Storage::disk('local')->assertExists($export->fresh()->file_path);
        Storage::disk('public')->assertMissing($export->fresh()->file_path);
        $book = IOFactory::load(Storage::disk('local')->path($export->fresh()->file_path));
        $this->assertSame(3, $book->getSheetCount());
        $cell = $book->getSheet(1)->getCell('A2');
        $this->assertSame($product->product_name, $cell->getValue());
        $this->assertSame(DataType::TYPE_STRING, $cell->getDataType());
        $book->disconnectWorksheets();
        $this->actingAs($other)->get('/reports/exports/download/'.$export->id)->assertNotFound();
        $this->actingAs($user)->get('/reports/exports/download/'.$export->id)->assertOk();
    }
}
