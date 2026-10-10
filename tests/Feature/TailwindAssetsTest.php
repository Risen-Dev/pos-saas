<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class TailwindAssetsTest extends TestCase
{
    public function test_views_have_no_handwritten_styles_or_legacy_stylesheets(): void
    {
        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(resource_path('views'))) as $file) {
            if (! $file->isFile() || ! str_ends_with($file->getFilename(), '.blade.php')) {
                continue;
            }
            // Standalone print layouts require print-specific CSS
            // for physical paper/label dimensions.
            $printLayoutExceptions = [
                resource_path('views/orders/print.blade.php'),
                resource_path('views/products/label.blade.php'),
            ];

            if (in_array($file->getPathname(), $printLayoutExceptions, true)) {
                continue;
            }
            $this->assertDoesNotMatchRegularExpression('/<style\b|\bstyle\s*=|(?:font-awesome|select2).*\.css|\bgp-[a-z]/i', file_get_contents($file->getPathname()), $file->getPathname());
        }
        $this->assertSame("@tailwind base;\n@tailwind components;\n@tailwind utilities;\n", file_get_contents(resource_path('css/app.css')));
    }

    public function test_reset_email_inlines_compiled_tailwind_and_keeps_action_link(): void
    {
        $user = new User(['name' => 'Uji Tailwind', 'email' => 'tailwind@example.test']);
        $html = (string) (new ResetPasswordNotification('test-token'))->toMail($user)->render();
        $document = new \DOMDocument;
        @$document->loadHTML($html);
        $xpath = new \DOMXPath($document);
        $button = $xpath->query('//a[contains(text(), "Reset Password")]')->item(0);
        $this->assertNotNull($button);
        $this->assertStringContainsString('test-token', $button->getAttribute('href'));
        $normalizedButtonStyle = preg_replace('/\s+/', '', $button->getAttribute('style'));
        $this->assertStringContainsString('padding:14px32px', $normalizedButtonStyle);
        $this->assertMatchesRegularExpression('/color:\s*(?:#fff(?:fff)?|white)\s*(?:;|$)/i', $button->getAttribute('style'));
        $this->assertStringContainsString('background-color:#f3f4f6', preg_replace('/\s+/', '', $document->getElementsByTagName('body')->item(0)->getAttribute('style')));
    }

    public function test_local_icons_preserve_dynamic_color_and_spin_without_font_css(): void
    {
        $html = Blade::render('<x-icon class="fa-solid fa-spinner fa-spin text-primary-600" />');
        $this->assertStringContainsString('<svg', $html);
        $this->assertStringContainsString('<path d=', $html);
        $this->assertStringContainsString('motion-safe:animate-spin', $html);
        $this->assertStringContainsString('text-primary-600', $html);
        $this->assertStringNotContainsString('fa-solid', $html);
    }
}
