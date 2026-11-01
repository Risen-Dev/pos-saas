<?php

namespace App\Services;

use HTMLPurifier;
use HTMLPurifier_Config;

class SafeReportHtml
{
    public function clean(string $html): string
    {
        $config = HTMLPurifier_Config::createDefault();
        $config->set('HTML.Allowed', 'h2,h3,h4,p,strong,em,ul,ol,li,table,thead,tbody,tr,th,td,br,div,span');
        $config->set('Cache.DefinitionImpl', null);
        $config->set('URI.DisableExternalResources', true);
        $config->set('HTML.ForbiddenElements', ['script', 'style', 'iframe', 'object', 'svg', 'form']);

        return (new HTMLPurifier($config))->purify($html);
    }
}
