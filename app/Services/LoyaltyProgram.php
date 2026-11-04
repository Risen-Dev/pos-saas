<?php

namespace App\Services;

class LoyaltyProgram
{
    public function type(array $settings): string
    {
        if (array_key_exists('loyalty_program_type', $settings)) {
            return in_array($settings['loyalty_program_type'], ['disabled', 'points', 'stamps'], true)
                ? $settings['loyalty_program_type']
                : 'disabled';
        }

        // Preserve the existing point program for tenants created before this setting.
        return ($settings['point_mode'] ?? 'disabled') === 'disabled' ? 'disabled' : 'points';
    }
}
