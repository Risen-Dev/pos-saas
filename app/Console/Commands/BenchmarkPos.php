<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class BenchmarkPos extends Command
{
    protected $signature = 'growpos:benchmark-pos';

    protected $description = 'Measure 100/1000/5000-SKU POS rendering on a disposable SQLite database, never the business database';

    public function handle(Kernel $kernel): int
    {
        if (! app()->environment(['local', 'testing'])) {
            $this->error('Run this synthetic benchmark in local/testing only.');

            return self::FAILURE;
        }
        $original = DB::getDefaultConnection();
        $path = tempnam(sys_get_temp_dir(), 'growpos-benchmark-');
        config(['database.connections.benchmark' => ['driver' => 'sqlite', 'database' => $path, 'prefix' => '', 'foreign_key_constraints' => true], 'session.driver' => 'array', 'cache.default' => 'array']);
        try {
            DB::setDefaultConnection('benchmark');
            Schema::clearResolvedInstance('db.schema');
            if (DB::connection()->getDatabaseName() !== $path) {
                throw new \RuntimeException('Isolation guard failed.');
            }
            Artisan::call('migrate', ['--database' => 'benchmark', '--force' => true]);
            $user = User::create(['name' => 'Benchmark fixture', 'email' => 'benchmark@example.test', 'password' => bin2hex(random_bytes(32)), 'email_verified_at' => now(), 'role' => 'admin']);
            $tenant = Tenant::create(['user_id' => $user->id, 'name' => 'Benchmark', 'slug' => 'benchmark', 'business_type' => 'retail', 'email' => $user->email, 'phone' => '000', 'address' => 'Synthetic fixture']);
            $user->update(['tenant_id' => $tenant->id]);
            $plan = Plan::create(['name' => 'Scale', 'slug' => 'scale', 'price' => 1, 'max_products' => 5000]);
            Subscription::create(['tenant_id' => $tenant->id, 'plan_id' => $plan->id, 'status' => 'active', 'start_date' => today(), 'end_date' => today()->addYear()]);
            $category = Category::create(['tenant_id' => $tenant->id, 'name' => 'Fixture', 'slug' => 'fixture']);
            Auth::guard('web')->setUser($user->fresh());
            $results = [];
            $previous = 0;
            foreach ([100, 1000, 5000] as $size) {
                foreach (array_chunk(range($previous + 1, $size), 500) as $batch) {
                    DB::table('products')->insert(array_map(fn ($i) => ['tenant_id' => $tenant->id, 'category_id' => $category->id, 'sku' => 'BENCH-'.$i, 'product_name' => 'Fixture '.$i, 'sell_price' => 10000, 'cost_price' => 5000, 'stock' => 100, 'type' => 'product', 'is_active' => true, 'manage_stock' => true, 'created_at' => now(), 'updated_at' => now()], $batch));
                }
                $previous = $size;
                $connection = DB::connection();
                $connection->enableQueryLog();
                $connection->flushQueryLog();
                $start = hrtime(true);
                $response = $kernel->handle(Request::create('/pos', 'GET'));
                $results[] = ['sku_count' => $size, 'status' => $response->getStatusCode(), 'handler_ms' => round((hrtime(true) - $start) / 1e6, 2), 'queries' => count($connection->getQueryLog()), 'html_bytes' => strlen($response->getContent()), 'process_peak_bytes' => memory_get_peak_usage(true)];
                $connection->disableQueryLog();
                if ($response->getStatusCode() !== 200) {
                    throw new \RuntimeException('Fixture POS did not render.');
                }
            }
            $this->line(json_encode(['scope' => 'Synthetic SQLite single pass, includes in-process render; not production p95 or Core Web Vitals.', 'results' => $results], JSON_PRETTY_PRINT));

            return self::SUCCESS;
        } finally {
            Auth::forgetGuards();
            DB::purge('benchmark');
            DB::setDefaultConnection($original);
            Schema::clearResolvedInstance('db.schema');
            foreach ([$path, $path.'-wal', $path.'-shm'] as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
        }
    }
}
