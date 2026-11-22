<?php

namespace Tests\Feature\Pos;

use App\Models\Plan;
use DOMDocument;
use DOMXPath;

class WorkspaceNavigationTest extends PosTestCase
{
    public function test_owner_sidebar_groups_links_by_work_area_and_marks_only_the_exact_page_current(): void
    {
        $user = $this->shop();
        $user->tenant->update(['business_modules' => ['goods', 'services', 'food']]);
        $this->actingAs($user);

        $response = $this->get('/dashboard')->assertOk();
        $dom = new DOMDocument;
        @$dom->loadHTML($response->getContent());
        $xpath = new DOMXPath($dom);
        $nav = $xpath->query('//nav[@aria-label="Navigasi usaha"]');

        $this->assertSame(1, $nav->length);
        $this->assertSame(1, $xpath->query('//header//button[@aria-label="Buka navigasi"][@aria-controls="business-sidebar"]')->length);
        foreach (['Penjualan', 'Kelola usaha', 'Analisis', 'Pengaturan usaha', 'Bantuan', 'Akun'] as $label) {
            $this->assertStringContainsString($label, $nav->item(0)->textContent);
        }
        foreach (['Transaksi', 'Promo', 'Shift', 'Katalog', 'Persediaan', 'Pelanggan', 'Dapur', 'Pengerjaan jasa', 'Laporan'] as $label) {
            $this->assertStringContainsString($label, $nav->item(0)->textContent);
        }
        $this->assertSame(1, $xpath->query('//nav[@aria-label="Navigasi usaha"]//a[contains(@href, "/dashboard")][@aria-current="page"]')->length);
        $this->assertSame(0, $xpath->query('//nav[@aria-label="Navigasi usaha"]//a[contains(@href, "/products")][@aria-current="page"]')->length);

        $productPage = $this->get('/products')->assertOk();
        $dom = new DOMDocument;
        @$dom->loadHTML($productPage->getContent());
        $xpath = new DOMXPath($dom);
        $this->assertSame(1, $xpath->query('//nav[@aria-label="Navigasi usaha"]//a[contains(@href, "/products")][@aria-current="page"]')->length);

        $productDetailPage = $this->get('/products/create')->assertOk();
        $dom = new DOMDocument;
        @$dom->loadHTML($productDetailPage->getContent());
        $xpath = new DOMXPath($dom);
        $this->assertSame(0, $xpath->query('//nav[@aria-label="Navigasi usaha"]//a[contains(@href, "/products")][@aria-current="page"]')->length);
    }

    public function test_platform_actions_are_separate_and_visible_only_to_platform_admins(): void
    {
        $owner = $this->shop();
        $this->actingAs($owner)->get('/dashboard')->assertOk()->assertDontSee('Konsol platform');

        $owner->forceFill(['is_platform_admin' => true])->save();
        $this->actingAs($owner->fresh())->get('/dashboard')->assertOk()->assertSee('Konsol platform')->assertSee('Pencairan');
    }

    public function test_owner_has_one_kasir_entry_and_business_settings_are_not_a_catch_all(): void
    {
        $user = $this->shop();
        $this->actingAs($user);

        $dashboard = $this->get('/dashboard')->assertOk();
        $dom = new DOMDocument;
        @$dom->loadHTML($dashboard->getContent());
        $xpath = new DOMXPath($dom);
        $this->assertSame(1, $xpath->query('//a[contains(@href, "/pos")]')->length);
        $this->assertSame(0, $xpath->query('//header//a[contains(@href, "/profile")]')->length);

        $settings = $this->get('/workspace/settings')->assertOk();
        $dom = new DOMDocument;
        @$dom->loadHTML($settings->getContent());
        $xpath = new DOMXPath($dom);
        $content = $xpath->query('//main[@id="main-content"]')->item(0)->textContent;
        foreach (['Bisnis & akses', 'Aturan penjualan & paket', 'Paket & langganan', 'Aktivitas usaha'] as $label) {
            $this->assertStringContainsString($label, $content);
        }
        foreach (['Panduan singkat', 'Akun saya', 'Persetujuan pencairan'] as $label) {
            $this->assertStringNotContainsString($label, $content);
        }
        $this->assertSame(0, $xpath->query('//main[@id="main-content"]//a[contains(@href, "/withdrawals")]')->length);
    }

    public function test_context_navigation_uses_a_single_scrollable_row(): void
    {
        $response = $this->actingAs($this->shop())->get('/inventory')->assertOk();
        $dom = new DOMDocument;
        @$dom->loadHTML($response->getContent());
        $xpath = new DOMXPath($dom);
        $nav = $xpath->query('//nav[@aria-label="Menu stock"]')->item(0);

        $this->assertNotNull($nav);
        $this->assertStringContainsString('flex-nowrap', $nav->getAttribute('class'));
        $this->assertStringContainsString('overflow-x-auto', $nav->getAttribute('class'));
    }

    public function test_account_quota_copy_explains_separate_businesses_not_consolidated_outlets(): void
    {
        $owner = $this->shop();
        $owner->tenant->currentPlan()->update(['max_outlets' => 2]);
        $this->actingAs($owner);

        $this->get('/tenants')->assertOk()
            ->assertSee('Bisnis yang saya kelola')
            ->assertSee('Data penjualan, katalog, stok, dan laporan tetap terpisah')
            ->assertDontSee('cabang/toko')
            ->assertDontSee('Upgrade Cabang');

        $this->get('/tenants/create')->assertOk()
            ->assertSee('bisnis lain dari akun yang sama')
            ->assertSee('terpisah');

        $this->get('/profile/edit')->assertOk()
            ->assertSee('Beberapa bisnis dalam satu akun')
            ->assertSee('tetap terpisah')
            ->assertDontSee('Dukungan Kelola Multi-Outlet');

        $this->get('/profile')->assertOk()
            ->assertSee('Bisnis aktif')
            ->assertSee('Bisnis dalam akun');

        $this->get('/tenants/'.$owner->tenant_id.'/edit')->assertOk()
            ->assertSee('Edit profil bisnis')
            ->assertDontSee('Tenant');

        $this->get('/billing')->assertOk()
            ->assertSee('bisnis per akun')
            ->assertDontSee('bisnis / outlet')
            ->assertDontSee('Outlet');

        $this->get('/settings')->assertOk()
            ->assertSee('bisnis aktif')
            ->assertDontSee('outlet');

        $this->get('/')->assertOk()->assertSee('bisnis per akun');

        $scalePlan = Plan::make(['slug' => 'scale', 'features' => ['Hingga 10 Outlet' => true]]);
        $this->assertStringContainsString('beberapa bisnis', $scalePlan->description);
        $this->assertArrayNotHasKey('Hingga 10 Outlet', $scalePlan->advertisedFeatures());
    }

    public function test_submenus_render_once_and_never_inside_floating_notifications(): void
    {
        $user = $this->shop();
        $user->tenant->update(['business_modules' => ['goods', 'services', 'food']]);
        $this->actingAs($user);

        $pages = [
            'transactions' => ['/orders', '/customers', '/discounts', '/shifts', '/payments/review'],
            'catalog' => ['/products', '/products/create', '/categories', '/menu/configure'],
            'stock' => ['/inventory', '/inventory/history', '/materials', '/suppliers', '/purchases', '/purchases/create', '/stocktakes'],
            'reports' => ['/reports', '/reports/exports', '/cash', '/finance'],
        ];

        foreach ($pages as $section => $urls) {
            foreach ($urls as $url) {
                $response = $this->get($url)->assertOk();
                $dom = new DOMDocument;
                @$dom->loadHTML($response->getContent());
                $xpath = new DOMXPath($dom);
                $this->assertSame(1, $xpath->query('//nav[@aria-label="Menu '.$section.'"]')->length, $url.' must render one submenu');
                $this->assertSame(0, $xpath->query('//*[@data-toast-region]//nav')->length, $url.' must not float navigation inside notifications');
            }
        }
    }

    public function test_notifications_do_not_duplicate_submenu_when_messages_are_present(): void
    {
        $this->actingAs($this->shop())->withSession(['success' => 'Tersimpan', 'error' => 'Pesan pengujian']);
        $response = $this->get('/orders')->assertOk();
        $dom = new DOMDocument;
        @$dom->loadHTML($response->getContent());
        $xpath = new DOMXPath($dom);
        $this->assertSame(1, $xpath->query('//nav[@aria-label="Menu transactions"]')->length);
        $this->assertSame(0, $xpath->query('//*[@data-toast-region]//nav')->length);
        $this->assertStringContainsString('Tersimpan', $xpath->query('//*[@data-toast-region]')->item(0)->textContent);
        $this->assertStringContainsString('Pesan pengujian', $xpath->query('//*[@data-toast-region]')->item(0)->textContent);
    }
}
