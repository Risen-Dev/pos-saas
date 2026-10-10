<?php

namespace Tests\Feature\Pos;

class ManagerAccessTest extends PosTestCase
{
    public function test_manager_can_access_operational_backoffice_but_not_owner_controls(): void
    {
        $manager = $this->shop('manager');
        $this->product($manager);
        $this->actingAs($manager);

        foreach (['/dashboard', '/products', '/categories', '/inventory', '/suppliers', '/discounts', '/reports'] as $url) {
            $this->get($url)->assertOk();
        }

        foreach (['/employees', '/settings', '/business', '/billing', '/tenants', '/getting-started', '/cash'] as $url) {
            $this->get($url)->assertRedirect('/pos');
        }
    }
}
