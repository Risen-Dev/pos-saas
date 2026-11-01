import { defineConfig } from "@playwright/test";

const port = process.env.GROWPOS_E2E_PORT || "8000";
const baseURL = `http://127.0.0.1:${port}`;

export default defineConfig({
    testDir: "./tests/e2e",
    workers: 1,
    retries: process.env.CI ? 1 : 0,
    use: {
        channel: process.env.GROWPOS_BROWSER_CHANNEL || undefined,
        baseURL,
        viewport: { width: 390, height: 844 },
        trace: "retain-on-failure",
        screenshot: "only-on-failure",
    },
    webServer: {
        command: `php artisan serve --no-reload --host=127.0.0.1 --port=${port}`,
        url: `${baseURL}/up`,
        reuseExistingServer: false,
    },
});
