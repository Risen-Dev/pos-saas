<?php

namespace Tests\Feature\Pos;

use DOMDocument;
use DOMXPath;

class WorkspaceNavigationTest extends PosTestCase
{
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
