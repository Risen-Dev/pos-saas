<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Historical production migrations recorded `create_plants_table` but created `plans`.
        // Never replace or truncate an existing subscription plans table.
        if (! Schema::hasTable('plans')) {
            throw new RuntimeException('Missing plans table; verify the original create_plants_table migration.');
        }

        foreach (['id', 'name', 'price', 'duration_days', 'features'] as $column) {
            if (! Schema::hasColumn('plans', $column)) {
                throw new RuntimeException("Unexpected legacy plans schema: {$column} is missing.");
            }
        }

        // Each alteration is conditional to allow recovery if MySQL DDL was only partly applied.
        $columns = [
            'slug' => fn (Blueprint $table) => $table->string('slug')->nullable(),
            'description' => fn (Blueprint $table) => $table->text('description')->nullable(),
            'max_users' => fn (Blueprint $table) => $table->integer('max_users')->default(1),
            'max_products' => fn (Blueprint $table) => $table->integer('max_products')->default(100),
            'max_outlets' => fn (Blueprint $table) => $table->integer('max_outlets')->default(1),
            'is_active' => fn (Blueprint $table) => $table->boolean('is_active')->default(true),
            'is_public' => fn (Blueprint $table) => $table->boolean('is_public')->default(true),
        ];

        foreach ($columns as $name => $definition) {
            if (! Schema::hasColumn('plans', $name)) {
                Schema::table('plans', $definition);
            }
        }

        // Preserve existing records and subscription IDs. Assign unique, stable identifiers.
        // Never rewrite pre-existing nonempty slugs.
        DB::table('plans')->select(['id', 'slug'])->orderBy('id')->chunk(100, function ($plans) {
            foreach ($plans as $plan) {
                if ($plan->slug !== null && trim((string) $plan->slug) !== '') {
                    continue;
                }

                $slug = 'legacy-plan-' . $plan->id;
                if (DB::table('plans')->where('slug', $slug)->where('id', '!=', $plan->id)->exists()) {
                    throw new RuntimeException("Slug {$slug} already in use; review plan slugs manually.");
                }
                DB::table('plans')->where('id', $plan->id)->update(['slug' => $slug]);
            }
        });

        if (DB::table('plans')->whereNull('slug')->orWhere('slug', '')->exists()) {
            throw new RuntimeException('Some plan slugs are still empty.');
        }
        if (DB::table('plans')->select('slug')->groupBy('slug')->havingRaw('COUNT(*) > 1')->exists()) {
            throw new RuntimeException('Duplicate plan slugs found; resolve before adding unique index.');
        }

        // The new-install schema declares slug NOT NULL UNIQUE.
        Schema::table('plans', fn (Blueprint $table) => $table->string('slug')->nullable(false)->change());

        $hasUniqueSlug = collect(Schema::getIndexes('plans'))->contains(function (array $index) {
            return ($index['unique'] ?? false) && ($index['columns'] ?? []) === ['slug'];
        });
        if (! $hasUniqueSlug) {
            Schema::table('plans', fn (Blueprint $table) => $table->unique('slug'));
        }
    }

    public function down(): void
    {
        // Intentionally non-destructive: reverting an application release must not
        // remove subscription metadata or invalidate existing plan records.
    }
};
