<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

class AiReportController extends Controller
{
    /**
     * Ambang batas jumlah transaksi 30 hari supaya insight dianggap "cukup data".
     * Di bawah ini, AI diinstruksikan untuk jujur bilang datanya masih tipis
     * daripada sok pasti (menghindari overconfident insight dari data receh).
     */
    private const MIN_TRANSAKSI_UNTUK_INSIGHT_KUAT = 20;

    /** Batas chat per tenant per hari, supaya satu toko tidak menghabiskan kuota API sendirian. */
    private const MAX_CHAT_PER_TENANT_PER_HARI = 40;

    public function index()
    {
        if (Gate::denies('feature-ai-analytics')) {
            return redirect()->route('billing.index')
                ->with('warning', 'Fitur Analitik AI & Prediksi Bisnis hanya tersedia pada Paket Scale.');
        }

        $tenantId = auth()->user()->tenant_id;

        if (! $tenantId) {
            $aiAnalysis = "<p class='italic text-center text-gray-400'>Silakan pilih atau daftarkan bisnis Anda terlebih dahulu untuk memulai analisis AI.</p>";

            return view('reports.ai-analysis', compact('aiAnalysis'));
        }

        // 1. DATA PRODUK TERLARIS (TOP 5)
        $topProducts = DB::table('order_items')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.tenant_id', $tenantId)
            ->where('orders.created_at', '>=', now()->subDays(30))
            ->select('products.product_name', DB::raw('SUM(order_items.quantity) as total_terjual'))
            ->groupBy('products.product_name')
            ->orderBy('total_terjual', 'desc')
            ->take(5)
            ->get();

        // 2. DATA PRODUK KURANG LAKU (SLOW MOVING - BOTTOM 3)
        $bottomProducts = DB::table('order_items')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.tenant_id', $tenantId)
            ->where('orders.created_at', '>=', now()->subDays(30))
            ->select('products.product_name', DB::raw('SUM(order_items.quantity) as total_terjual'))
            ->groupBy('products.product_name')
            ->orderBy('total_terjual', 'asc')
            ->take(3)
            ->get();

        // 3. TREN OMSET HARIAN (30 hari)
        $salesTrend = Order::where('tenant_id', $tenantId)
            ->where('created_at', '>=', now()->subDays(30))
            ->select(
                DB::raw('DATE(created_at) as tanggal'),
                DB::raw('SUM(grand_total) as total_omset'),
                DB::raw('COUNT(id) as total_nota'),
                DB::raw('DAYNAME(created_at) as nama_hari')
            )
            ->groupBy('tanggal', 'nama_hari')
            ->get();

        // 4. KALKULASI METRIK UTAMA
        $totalOmset = $salesTrend->sum('total_omset');
        $totalTransaksi = $salesTrend->sum('total_nota');
        $jumlahHariAktif = $salesTrend->count() > 0 ? $salesTrend->count() : 1;

        $rataRataOmsetHarian = $totalOmset / $jumlahHariAktif;
        $rataRataNilaiPerNota = $totalTransaksi > 0 ? ($totalOmset / $totalTransaksi) : 0;

        $dayAnalysis = Order::where('tenant_id', $tenantId)
            ->where('created_at', '>=', now()->subDays(30))
            ->select(DB::raw('DAYNAME(created_at) as hari'), DB::raw('SUM(grand_total) as omset_hari'))
            ->groupBy('hari')
            ->orderBy('omset_hari', 'desc')
            ->get();

        $hariTeramai = $dayAnalysis->first()->hari ?? 'Tidak Terdeteksi';
        $hariTersepi = $dayAnalysis->last()->hari ?? 'Tidak Terdeteksi';

        // 5. TREN PERTUMBUHAN: 7 hari terakhir vs 7 hari sebelumnya
        // Ini yang bikin laporan terasa "hidup" -> bisa deteksi naik/turun tanpa
        // pemilik toko harus baca grafik sendiri.
        $omset7HariIni = Order::where('tenant_id', $tenantId)
            ->where('created_at', '>=', now()->subDays(7))
            ->sum('grand_total');

        $omset7HariSebelumnya = Order::where('tenant_id', $tenantId)
            ->whereBetween('created_at', [now()->subDays(14), now()->subDays(7)])
            ->sum('grand_total');

        $growthPercent = $omset7HariSebelumnya > 0
            ? round((($omset7HariIni - $omset7HariSebelumnya) / $omset7HariSebelumnya) * 100, 1)
            : null;

        // 6. ANALISIS KOMBO PRODUK (MARKET BASKET ANALYSIS)
        // Ini fitur pembeda GrowPOS: bundling yang direkomendasikan bukan cuma
        // "produk laris + produk sepi" secara asal, tapi berdasarkan produk yang
        // BENAR-BENAR sering dibeli bersamaan dalam satu struk oleh pelanggan asli.
        $comboPairs = DB::table('order_items as oi1')
            ->join('order_items as oi2', function ($join) {
                $join->on('oi1.order_id', '=', 'oi2.order_id')
                    ->whereColumn('oi1.product_id', '<', 'oi2.product_id');
            })
            ->join('products as p1', 'oi1.product_id', '=', 'p1.id')
            ->join('products as p2', 'oi2.product_id', '=', 'p2.id')
            ->join('orders', 'oi1.order_id', '=', 'orders.id')
            ->where('orders.tenant_id', $tenantId)
            ->where('orders.created_at', '>=', now()->subDays(30))
            ->select('p1.product_name as produk_a', 'p2.product_name as produk_b', DB::raw('COUNT(*) as frekuensi'))
            ->groupBy('p1.product_name', 'p2.product_name')
            ->orderBy('frekuensi', 'desc')
            ->take(3)
            ->get();

        $dataCukup = $totalTransaksi >= self::MIN_TRANSAKSI_UNTUK_INSIGHT_KUAT;

        // Ringkasan lebih ringkas -> hemat token, lebih hemat kuota Gemini
        $dataBisnis = [
            'nama_toko' => auth()->user()->tenant->name ?? 'Toko',
            'tipe_bisnis' => auth()->user()->tenant->business_type ?? 'Ritel/F&B',
            'omset_30_hari' => $totalOmset,
            'total_transaksi' => $totalTransaksi,
            'omset_rata_rata_harian' => round($rataRataOmsetHarian),
            'rata_rata_per_struk' => round($rataRataNilaiPerNota),
            'hari_teramai' => $hariTeramai,
            'hari_tersepi' => $hariTersepi,
            'top_5_produk' => $topProducts->pluck('total_terjual', 'product_name'),
            'bottom_3_produk' => $bottomProducts->pluck('total_terjual', 'product_name'),
            'tren_7_hari_vs_7_hari_sebelumnya_persen' => $growthPercent,
            'kombinasi_produk_sering_dibeli_bersamaan' => $comboPairs->map(
                fn ($p) => "{$p->produk_a} + {$p->produk_b} ({$p->frekuensi}x transaksi)"
            ),
            'data_memadai_untuk_insight_kuat' => $dataCukup,
        ];

        $prompt = 'Anda adalah Chief Business Analyst SaaS POS bernama GrowPOS. Analisis data bisnis berikut: '.json_encode($dataBisnis).'

Berikan laporan analisis profesional langsung dalam format HTML bersih tanpa Markdown (jangan gunakan ***, ###, atau ```html).
Gunakan tag HTML: <h3> untuk judul bagian, <p> untuk narasi, <strong> untuk penekanan angka, <ul><li> untuk poin-poin, dan <table> statis untuk perbandingan metrik.

Struktur Laporan:
1. <h3>Rangkuman Eksekutif Finansial</h3> (Gunakan <table>, sebutkan juga tren 7 hari terakhir naik/turun berapa persen jika datanya ada)
2. <h3>Analisis Produk & Inventaris</h3>
3. <h3>Kombo Cerdas & Peluang Bundling</h3> (Jika ada data kombinasi_produk_sering_dibeli_bersamaan, jadikan itu dasar utama rekomendasi bundling karena berbasis perilaku beli riil pelanggan, BUKAN sekadar menyandingkan produk terlaris dengan produk sepi. Jika kombinasi_produk_sering_dibeli_bersamaan kosong, baru gunakan pendekatan produk terlaris + produk sepi, dan JANGAN merekomendasikan bundling sebuah produk dengan dirinya sendiri.)
4. <h3>Rekomendasi Aksi</h3> (Minimal 2 strategi konkret)

Jika data_memadai_untuk_insight_kuat bernilai false, WAJIB sertakan satu kalimat jujur di awal laporan bahwa insight ini masih indikatif karena volume transaksi tercatat masih sedikit, dan akan semakin akurat seiring bertambahnya data.';

        // ── CACHE FIX ──
        // Ambil dulu dari cache. Kalau kosong ATAU sebelumnya gagal (null),
        // baru panggil Gemini. Hasil hanya disimpan ke cache kalau BERHASIL,
        // supaya kegagalan sementara (429/timeout) tidak "mengunci" fallback
        // selama 6 jam penuh.
        $cacheKey = "ai_report_{$tenantId}_".now()->format('Y-m-d');
        $aiAnalysis = Cache::get($cacheKey);

        if (! $aiAnalysis) {
            $aiAnalysis = $this->callGeminiApi([
                ['role' => 'user', 'parts' => [['text' => $prompt]]],
            ]);

            if ($aiAnalysis) {
                Cache::put($cacheKey, $aiAnalysis, now()->addHours(6));
            }
        }

        // Jika API Gagal, Gunakan Fallback Lokal
        if (! $aiAnalysis) {
            $kamusHari = ['Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu', 'Sunday' => 'Minggu'];
            $hariIndoRamai = $kamusHari[$hariTeramai] ?? $hariTeramai;
            $hariIndoSepi = $kamusHari[$hariTersepi] ?? $hariTersepi;
            $produkJuaraStr = $topProducts->first()->product_name ?? 'Produk Utama';

            // FIX BUG: pastikan produk pasangan bundling BEDA dari produk juara.
            // Sebelumnya kode lama bisa menyarankan "gabungkan Es Teh dengan Es Teh"
            // kalau kebetulan cuma ada 1 produk yang terjual di periode itu.
            $produkSepiObj = $bottomProducts->first(fn ($p) => $p->product_name !== $produkJuaraStr);
            $produkSepiStr = $produkSepiObj->product_name ?? null;

            $disclaimerDataTipis = ! $dataCukup
                ? "<div class='flex items-center gap-2 p-3 mb-4 text-xs text-blue-800 border border-blue-200 rounded-lg bg-blue-50'>
                        <span aria-hidden='true' class='text-blue-500'>ℹ</span>
                        <span>Data transaksi Anda masih terbatas (".$totalTransaksi.' transaksi dalam 30 hari). Insight di bawah ini bersifat indikatif dan akan semakin akurat seiring bertambahnya transaksi.</span>
                   </div>'
                : '';

            $trenHtml = '';
            if ($growthPercent !== null) {
                $arahTren = $growthPercent >= 0 ? 'naik' : 'turun';
                $warnaTren = $growthPercent >= 0 ? 'text-green-600' : 'text-red-600';
                $trenHtml = "<tr>
                        <td>Tren Omset 7 Hari Terakhir</td>
                        <td><strong class='{$warnaTren}'>".($growthPercent >= 0 ? '+' : '').$growthPercent."%</strong> ({$arahTren} dibanding 7 hari sebelumnya)</td>
                    </tr>";
            }

            $komboHtml = '<p>Belum ada pola kombinasi produk yang cukup jelas terdeteksi dari transaksi 30 hari terakhir. Pola ini akan mulai terlihat seiring bertambahnya jumlah struk.</p>';
            if ($comboPairs->isNotEmpty()) {
                $itemsKombo = $comboPairs->map(function ($p) {
                    return "<li><strong>{$p->produk_a}</strong> + <strong>{$p->produk_b}</strong> — dibeli bersamaan sebanyak <strong>{$p->frekuensi}x</strong>. Pertimbangkan paket bundling resmi untuk kombinasi ini.</li>";
                })->implode('');
                $komboHtml = "<ul>{$itemsKombo}</ul>";
            } elseif ($produkSepiStr) {
                $komboHtml = "<p>Belum ada data kombinasi pembelian yang cukup kuat. Sebagai gantinya, coba bundling <strong>{$produkJuaraStr}</strong> (produk terlaris) dengan <strong>{$produkSepiStr}</strong> (produk slow-moving) untuk mempercepat perputaran stok.</p>";
            }

            $aiAnalysis = "
                {$disclaimerDataTipis}
                <div class='flex items-center gap-2 p-3 mb-4 text-xs border rounded-lg text-amber-800 bg-amber-50 border-amber-200'>
                    <span aria-hidden='true' class='text-amber-500'>⚠</span>
                    <span>Koneksi AI sedang menggunakan mode analisis komputasi lokal.</span>
                </div>

                <h3>Rangkuman Eksekutif Finansial</h3>
                <p>Berdasarkan performa 30 hari terakhir, <strong>".(auth()->user()->tenant->name ?? 'Toko Anda').'</strong> berhasil membukukan total omset sebesar <strong>Rp '.number_format($totalOmset, 0, ',', '.').'</strong> dari <strong>'.$totalTransaksi.' kali transaksi</strong>.</p>

                <table>
                    <thead>
                        <tr>
                            <th>Metrik Operasional</th>
                            <th>Nilai Statistik</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Rata-rata Omset Harian</td>
                            <td><strong>Rp '.number_format($rataRataOmsetHarian, 0, ',', '.').'</strong> /hari</td>
                        </tr>
                        <tr>
                            <td>Rata-rata Basket Size (Per Struk)</td>
                            <td><strong>Rp '.number_format($rataRataNilaiPerNota, 0, ',', '.').'</strong> /transaksi</td>
                        </tr>
                        <tr>
                            <td>Hari Penjualan Tertinggi</td>
                            <td>Hari '.$hariIndoRamai.'</td>
                        </tr>
                        <tr>
                            <td>Hari Penjualan Terendah</td>
                            <td>Hari '.$hariIndoSepi."</td>
                        </tr>
                        {$trenHtml}
                    </tbody>
                </table>

                <h3>Analisis Produk & Inventaris</h3>
                <p>Produk utama penggerak omset Anda adalah <strong>".$produkJuaraStr.'</strong>'.($produkSepiStr ? ", sedangkan produk dengan perputaran stok paling lambat adalah <strong>{$produkSepiStr}</strong>." : '.')."</p>

                <h3>Kombo Cerdas & Peluang Bundling</h3>
                {$komboHtml}

                <h3>Rekomendasi Aksi</h3>
                <ul>
                    ".($produkSepiStr ? "<li><strong>Paket Bundling Cross-Selling:</strong> Gabungkan <em>{$produkJuaraStr}</em> dengan <em>{$produkSepiStr}</em> dalam satu harga paket hemat untuk mempercepat perputaran produk slow-moving.</li>" : '<li><strong>Diversifikasi Menu:</strong> Tambahkan varian menu baru agar tidak terlalu bergantung pada satu produk andalan.</li>').'
                    <li><strong>Promo Hari '.$hariIndoSepi.':</strong> Berikan diskon khusus Happy Hour pada hari '.$hariIndoSepi.' untuk memicu kunjungan pelanggan di hari sepi.</li>
                </ul>
            ';
        }

        $kamusHari = ['Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu', 'Sunday' => 'Minggu'];
        $hariTeramaiIndo = $kamusHari[$hariTeramai] ?? $hariTeramai;
        $produkJuara = $topProducts->first()->product_name ?? 'Belum Ada';

        return view('reports.ai-analysis', compact(
            'aiAnalysis',
            'rataRataOmsetHarian',
            'rataRataNilaiPerNota',
            'hariTeramaiIndo',
            'produkJuara',
            'growthPercent',
            'comboPairs',
            'dataCukup'
        ));
    }

    public function chat(Request $request)
    {

        if (Gate::denies('feature-ai-analytics')) {
            return response()->json([
                'reply' => 'Fitur AI Chat Advisor hanya tersedia pada Paket Scale. Silakan upgrade paket Anda di menu Billing.',
                'suggestions' => [],
            ], 403);
        }
        // Validasi (cukup sekali) — sekarang termasuk histori percakapan untuk multi-turn context
        $request->validate([
            'message' => 'required|string|max:1000',
            'history' => 'nullable|array|max:6',
            'history.*.sender' => 'required_with:history|string|in:user,ai',
            'history.*.text' => 'required_with:history|string|max:1000',
        ]);

        // Rate limit per menit: maksimal 8 pesan per menit per user
        $minuteKey = 'ai-chat:'.auth()->id();

        if (RateLimiter::tooManyAttempts($minuteKey, 8)) {
            return response()->json([
                'reply' => 'Anda mengirim pesan terlalu cepat. Mohon tunggu sebentar sebelum bertanya lagi.',
                'suggestions' => [],
            ], 429);
        }
        RateLimiter::hit($minuteKey, 60);

        $tenantId = auth()->user()->tenant_id;

        // Kuota harian per TENANT (bukan per user) — supaya satu toko tidak
        // menghabiskan jatah request harian API hanya karena banyak staf chat sekaligus.
        $dailyKey = "ai-chat-daily:{$tenantId}:".now()->format('Y-m-d');
        $dailyCount = Cache::get($dailyKey, 0);

        if ($dailyCount >= self::MAX_CHAT_PER_TENANT_PER_HARI) {
            return response()->json([
                'reply' => 'Kuota tanya AI untuk toko Anda hari ini sudah tercapai. Silakan coba lagi besok, atau lihat Laporan Analisis AI di panel kiri.',
                'suggestions' => [],
            ], 429);
        }

        $userMessage = $request->input('message');
        $history = $request->input('history', []);

        $totalOmsetHariIni = Order::where('tenant_id', $tenantId)
            ->whereDate('created_at', today())
            ->where('payment_status', 'paid')
            ->sum('grand_total');

        $totalNotaHariIni = Order::where('tenant_id', $tenantId)
            ->whereDate('created_at', today())
            ->where('payment_status', 'paid')
            ->count();

        $top5 = DB::table('order_items')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.tenant_id', $tenantId)
            ->where('orders.created_at', '>=', now()->subDays(30))
            ->select('products.product_name', DB::raw('SUM(order_items.quantity) as qty'))
            ->groupBy('products.product_name')
            ->orderBy('qty', 'desc')
            ->take(5)
            ->pluck('products.product_name')
            ->toArray();

        $bottom3 = DB::table('order_items')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.tenant_id', $tenantId)
            ->where('orders.created_at', '>=', now()->subDays(30))
            ->select('products.product_name', DB::raw('SUM(order_items.quantity) as qty'))
            ->groupBy('products.product_name')
            ->orderBy('qty', 'asc')
            ->take(3)
            ->pluck('products.product_name')
            ->toArray();

        $bahanKritis = Material::where('tenant_id', $tenantId)
            ->whereColumn('stock', '<=', 'min_stock')
            ->get(['name', 'stock', 'unit'])
            ->map(fn ($m) => "{$m->name} ({$m->stock} {$m->unit})")
            ->toArray();

        $namaToko = auth()->user()->tenant->name ?? 'Toko';
        $omsetFormatted = 'Rp '.number_format($totalOmsetHariIni, 0, ',', '.');
        $topProdukStr = empty($top5) ? 'Belum Ada Data' : implode(', ', $top5);
        $bottomProdukStr = empty($bottom3) ? 'Belum Ada Data' : implode(', ', $bottom3);
        $bahanKritisStr = empty($bahanKritis) ? 'Semua Stok Bahan Baku Aman' : implode(', ', $bahanKritis);

        $prompt = "Anda adalah Asisten Konsultan AI Bisnis resmi GrowPOS untuk toko {$namaToko}. Anda BUKAN chatbot generik — Anda punya akses ke data operasional toko ini secara real-time dan mengingat konteks percakapan sebelumnya.

Data operasional toko saat ini (semua angka omzet di bawah ini adalah data HARI INI, bukan rata-rata 30 hari):
- Omzet Hari Ini: {$omsetFormatted} ({$totalNotaHariIni} nota)
- Produk Terlaris (30 Hari Terakhir): {$topProdukStr}
- Produk Kurang Laku / Slow Moving (30 Hari Terakhir): {$bottomProdukStr}
- Bahan Baku Menipis/Kritis: {$bahanKritisStr}

Pertanyaan Pemilik Toko: \"{$userMessage}\"

Petunjuk Jawab:
1. Jawablah langsung secara relevan dengan pertanyaan spesifik pengguna, dan perhatikan histori percakapan sebelumnya (jika ada) supaya jawaban nyambung dengan konteks, misalnya kalau user bertanya \"yang mana?\" merujuk ke jawaban Anda sebelumnya.
2. Jika pengguna bertanya tentang PROMO / DISKON / MENU MANA YANG DIDISKON, rekomendasikan diskon/bundling untuk produk slow moving ({$bottomProdukStr}) atau bundling dengan produk terlaris ({$topProdukStr}). JANGAN sarankan bundling sebuah produk dengan dirinya sendiri.
3. Jika pengguna bertanya tentang OMZET, jelaskan bahwa angka yang disebut adalah omzet HARI INI ({$omsetFormatted}), bukan rata-rata harian 30 hari, lalu berikan trik menaikkannya.
4. Jika bertanya tentang STOK / BAHAN, bahas bahan baku kritis ({$bahanKritisStr}).
5. Jawab dalam 2-4 kalimat singkat, padat, profesional, tanpa format markdown/bintang.";

        // Susun contents multi-turn: histori percakapan dulu, baru pertanyaan+konteks data terbaru.
        // Ini penting supaya AI benar-benar "ingat" alur obrolan, bukan cuma jawab 1 pertanyaan lepas.
        $contents = [];
        foreach ($history as $turn) {
            $contents[] = [
                'role' => $turn['sender'] === 'user' ? 'user' : 'model',
                'parts' => [['text' => $turn['text']]],
            ];
        }
        $contents[] = ['role' => 'user', 'parts' => [['text' => $prompt]]];

        // PANGGIL API GEMINI
        $reply = $this->callGeminiApi($contents);
        $msgLower = strtolower($userMessage);

        if ($reply) {
            $reply = strip_tags($reply);
            $reply = str_replace(['**', '```', '###', '`'], '', $reply);
            Cache::put($dailyKey, $dailyCount + 1, now()->endOfDay());
        } else {
            // FALLBACK CERDAS BERBASIS KEYWORD SPESIFIK
            if (str_contains($msgLower, 'diskon') || str_contains($msgLower, 'promo') || str_contains($msgLower, 'menu mana')) {
                $reply = "Untuk program promo, sebaiknya berikan diskon atau paket bundling pada produk slow-moving seperti {$bottomProdukStr}, disandingkan dengan produk favorit pelanggan ({$topProdukStr}). Ini efektif melikuidasi stok lama tanpa menggerus margin.";
            } elseif (str_contains($msgLower, 'stok') || str_contains($msgLower, 'habis') || str_contains($msgLower, 'restock') || str_contains($msgLower, 'bahan')) {
                $reply = "Status stok bahan baku Anda saat ini: {$bahanKritisStr}. Disarankan segera melakukan order pembelian (PO) ke supplier.";
            } elseif (str_contains($msgLower, 'omzet') || str_contains($msgLower, 'naik') || str_contains($msgLower, 'sepi')) {
                $reply = "Omzet hari ini di {$namaToko} mencapai {$omsetFormatted} dari {$totalNotaHariIni} nota. Untuk mendongkrak penjualan, terapkan strategi 'Happy Hour' di jam sepi atau paket kombo hemat.";
            } elseif (str_contains($msgLower, 'hai') || str_contains($msgLower, 'halo')) {
                $reply = "Halo Boss! Ada yang ingin didiskusikan mengenai strategi menu terlaris ({$topProdukStr}), diskon, atau analisis stok bahan baku hari ini?";
            } else {
                $reply = "Berdasarkan data GrowPOS hari ini, {$namaToko} mencatatkan omzet {$omsetFormatted} dari {$totalNotaHariIni} nota. Produk terlaris Anda saat ini adalah {$topProdukStr}.";
            }
        }

        return response()->json([
            'reply' => trim($reply),
            // Suggested follow-up: bikin chat terasa seperti konsultan proaktif,
            // bukan sekadar tanya-jawab pasif. Ini yang bikin GrowPOS beda dari
            // POS lain yang paling banter cuma nampilin dashboard statis.
            'suggestions' => $this->suggestedFollowUps($msgLower),
        ]);
    }

    /**
     * Rekomendasi pertanyaan lanjutan berdasarkan topik yang baru dibahas.
     * Ditampilkan sebagai chip yang bisa langsung diklik di UI chat.
     */
    private function suggestedFollowUps(string $msgLower): array
    {
        if (str_contains($msgLower, 'diskon') || str_contains($msgLower, 'promo') || str_contains($msgLower, 'menu mana')) {
            return ['Buatkan jadwal promo untuk minggu ini', 'Berapa target basket size setelah promo ini?'];
        }
        if (str_contains($msgLower, 'stok') || str_contains($msgLower, 'habis') || str_contains($msgLower, 'bahan')) {
            return ['Bahan apa yang paling sering kritis?', 'Menu mana yang paling terdampak jika bahan ini habis?'];
        }
        if (str_contains($msgLower, 'omzet') || str_contains($msgLower, 'naik') || str_contains($msgLower, 'sepi')) {
            return ['Menu mana yang sebaiknya didiskon?', 'Apa penyebab hari sepi kami?'];
        }

        return ['Bagaimana cara meningkatkan omzet toko saya?', 'Menu mana yang sebaiknya didiskon?'];
    }

    /**
     * Helper privat panggilan HTTP ke Gemini API dengan multi-model fallback.
     * Menerima $contents sebagai array turn (role + parts) supaya mendukung
     * percakapan multi-turn, bukan cuma satu prompt string sekali tembak.
     */
    private function callGeminiApi(array $contents): ?string
    {
        $apiKey = config('services.gemini.api_key') ?? env('GEMINI_API_KEY');

        if (! $apiKey) {
            Log::warning('GEMINI_API_KEY tidak ditemukan.');

            return null;
        }

        $models = ['gemini-2.5-flash-lite', 'gemini-2.0-flash', 'gemini-1.5-flash'];

        foreach ($models as $model) {
            try {
                $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";

                $response = Http::withHeaders([
                    'Content-Type' => 'application/json',
                ])->timeout(10)->post($url, [
                    'contents' => $contents,
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;
                    if ($text) {
                        return $text;
                    }
                } else {
                    Log::error("Gemini API {$model} Error Status: {$response->status()} Body: ".$response->body());
                }
            } catch (\Exception $e) {
                Log::error("Gemini API {$model} Exception: ".$e->getMessage());
            }
        }

        return null;
    }
}
