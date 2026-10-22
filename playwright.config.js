import { defineConfig } from '@playwright/test';
export default defineConfig({
    testDir: './tests/Browser',
    workers: 1,
    timeout: 30000,
    use: {
        baseURL: process.env.BROWSER_BASE_URL || 'http://127.0.0.1:8000',
        browserName: 'chromium',
        launchOptions: process.env.CHROMIUM_PATH ? { executablePath: process.env.CHROMIUM_PATH, args: ['--no-sandbox'] } : {},
        trace: 'retain-on-failure',
    },
    reporter: [['list'], ['json', { outputFile: 'test-results/browser.json' }]],
});
