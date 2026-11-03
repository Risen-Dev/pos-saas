<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('plans') && Schema::hasColumn('plans', 'duration_days')) {
            Schema::table('plans', fn (Blueprint $table) => $table->integer('duration_days')->nullable()->default(null)->change());
            DB::table('plans')->where('slug', 'starter')->update(['duration_days' => null]);
        }

        if (Schema::hasTable('subscriptions') && Schema::hasColumn('subscriptions', 'end_date')) {
            Schema::table('subscriptions', fn (Blueprint $table) => $table->date('end_date')->nullable()->change());

            if (Schema::hasTable('plans')) {
                DB::table('subscriptions')
                    ->where('status', 'active')
                    ->whereIn('plan_id', DB::table('plans')->select('id')->where('slug', 'starter'))
                    ->update(['end_date' => null]);
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('subscriptions') && Schema::hasColumn('subscriptions', 'end_date')) {
            DB::table('subscriptions')->whereNull('end_date')->update(['end_date' => DB::raw('start_date')]);
            Schema::table('subscriptions', fn (Blueprint $table) => $table->date('end_date')->nullable(false)->change());
        }

        if (Schema::hasTable('plans') && Schema::hasColumn('plans', 'duration_days')) {
            DB::table('plans')->whereNull('duration_days')->update(['duration_days' => 30]);
            Schema::table('plans', fn (Blueprint $table) => $table->integer('duration_days')->nullable(false)->default(30)->change());
        }
    }
};
