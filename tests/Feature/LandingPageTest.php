<?php

namespace Tests\Feature;

use App\Models\Plan;
use DOMDocument;
use DOMXPath;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LandingPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_content_is_rendered_without_waiting_for_javascript(): void
    {
        $response = $this->get('/')->assertOk()->assertSee('Bisnis jalan.')->assertSee('Paket belum tersedia.');
        $dom = new DOMDocument;
        @$dom->loadHTML($response->getContent());
        $xpath = new DOMXPath($dom);

        // A 200 response alone missed the invisible body regression.
        $this->assertSame(0, $xpath->query('/html[@x-cloak] | /html/body[@x-cloak] | //main[@x-cloak]')->length);
        $this->assertSame(1, $xpath->query('//main//h1')->length);
        foreach ($xpath->query('//a[starts-with(@href, "#")]') as $link) {
            $target = substr($link->getAttribute('href'), 1);
            $this->assertNotEmpty($target, 'Public links must have a real destination.');
            $this->assertSame(1, $xpath->query('//*[@id="'.$target.'"]')->length, 'Missing link target: '.$target);
        }
    }

    public function test_only_public_active_packages_are_advertised(): void
    {
        Plan::create(['name' => 'Paket publik', 'slug' => 'public', 'price' => 99000, 'is_active' => true, 'is_public' => true]);
        Plan::create(['name' => 'Paket privat', 'slug' => 'private', 'price' => 1, 'is_active' => true, 'is_public' => false]);
        Plan::create(['name' => 'Paket nonaktif', 'slug' => 'inactive', 'price' => 1, 'is_active' => false, 'is_public' => true]);

        $this->get('/')->assertOk()->assertSee('Paket publik')->assertSee('Rp 99.000')->assertDontSee('Paket privat')->assertDontSee('Paket nonaktif');
    }
}
