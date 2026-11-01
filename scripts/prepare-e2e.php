<?php

use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use Database\Seeders\GrowPosQaSeeder;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
$path = config('database.connections.sqlite.database');
if (! $app->environment('testing') || $app->configurationIsCached() || config('database.default') !== 'sqlite'
    || ! is_string($path) || ! str_starts_with($path, sys_get_temp_dir().'/growpos-e2e-') || ! getenv('GROWPOS_QA_PASSWORD')) {
    throw new RuntimeException('E2E requires uncached testing config, a disposable growpos-e2e-* SQLite file in the system temp directory, and an explicit fixture password.');
}
if (! is_file($path)) {
    touch($path);
}
Artisan::call('migrate:fresh', ['--force' => true]);
Artisan::call('db:seed', ['--class' => GrowPosQaSeeder::class, '--force' => true]);
$owner = User::where('email', 'qa.owner@growpos.test')->firstOrFail();
$loyaltySettings = [
    'point_mode' => 'per_investment',
    'point_rule_value' => '10000',
    'point_member_only' => '1',
    'point_redemption_points' => '100',
    'point_redemption_value' => '10000',
];
foreach ($loyaltySettings as $key => $value) {
    Setting::updateOrCreate(['tenant_id' => $owner->tenant_id, 'key' => $key], ['value' => $value]);
}
$base = Product::where('tenant_id', $owner->tenant_id)->firstOrFail();
foreach (range(1, 12) as $index) {
    Product::firstOrCreate(['tenant_id' => $owner->tenant_id, 'sku' => 'E2E-'.$index], ['category_id' => $base->category_id, 'product_name' => 'Browser fixture '.$index, 'type' => 'product', 'sell_price' => 1000, 'is_active' => true]);
}
echo "Isolated E2E fixture ready.\n";

User::firstOrCreate(['email' => 'qa.new@growpos.test'], [
    'name' => 'Pengguna Baru QA', 'role' => 'admin', 'status' => 'active',
    'email_verified_at' => now(), 'password' => Hash::make(getenv('GROWPOS_QA_PASSWORD')),
]);
