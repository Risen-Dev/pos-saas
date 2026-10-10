import { readFileSync } from 'node:fs';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './resources/views/vendor/notifications/**/*.blade.php',
        {
            raw: readFileSync(new URL('./resources/views/vendor/notifications/email.blade.php', import.meta.url), 'utf8'),
            extension: 'html',
        },
    ],
    corePlugins: { preflight: false },
};
