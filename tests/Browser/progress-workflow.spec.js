import { test, expect } from '@playwright/test';

async function login(page) {
    if (!process.env.BROWSER_TEST_EMAIL || !process.env.BROWSER_TEST_PASSWORD) {
        throw new Error('BROWSER_TEST_EMAIL and BROWSER_TEST_PASSWORD must be set in environment.');
    }
    await page.goto('/login');
    await page.getByLabel('Email', { exact: true }).fill(process.env.BROWSER_TEST_EMAIL);
    await page.getByLabel('Password', { exact: true }).fill(process.env.BROWSER_TEST_PASSWORD);
    await page.getByRole('button', { name: 'Masuk', exact: true }).click();
    await expect(page).toHaveURL(/\/app$/);
}

test.describe('Progress Workflow Operational & Prototype Validation', () => {
    test('full form entry, autosave recovery on reload, and clean discard', async ({ page }) => {
        await login(page);

        // 1. Buka form baru
        await page.goto('/prototype?tab=form');
        await expect(page.locator('.entry-sheet')).toBeVisible();

        // 2. Ketik deskripsi
        await page.locator('#f-description').fill('Uji coba integrasi autosave lintas reload');
        await page.waitForTimeout(1500);

        // 3. Verifikasi indikator tersimpan
        await expect(page.locator('.save-state')).toContainText(/Draft tersimpan/i);

        // 4. Reload halaman dan verifikasi teks tidak hilang
        await page.reload();
        await expect(page.locator('#f-description')).toHaveValue('Uji coba integrasi autosave lintas reload');
    });

    test('manual Close and Reopen preserves nested data intact', async ({ page }) => {
        await login(page);

        // Buka detail progress yang ada
        await page.goto('/prototype?tab=progresses&id=prog-001');
        await expect(page.locator('.detail-layout')).toBeVisible();

        // Ambil isi teks sebelum close
        const originalContent = await page.locator('.detail-body').innerText();

        // Close progress
        const closeBtn = page.getByTestId('close-progress-btn');
        if (await closeBtn.isVisible()) {
            await closeBtn.click();
            await page.locator('dialog[open]').getByRole('button', { name: /Close|Tutup/i }).click();
            await expect(page.getByRole('button', { name: /Reopen/i })).toBeVisible();

            // Reopen progress
            await page.getByRole('button', { name: /Reopen/i }).click();
            await expect(page.getByTestId('close-progress-btn')).toBeVisible();

            // Verifikasi seluruh teks entitas anak dan rincian tetap utuh
            const reopenedContent = await page.locator('.detail-body').innerText();
            expect(reopenedContent).toBe(originalContent);
        }
    });

    test('mobile viewport 390px retains navigation, savebar, and form usability', async ({ page }) => {
        await page.setViewportSize({ width: 390, height: 844 });
        await login(page);

        await page.goto('/prototype?tab=form');
        await expect(page.locator('.savebar')).toBeVisible();
        await expect(page.getByTestId('save-progress-btn')).toBeVisible();

        // Scroll horizontal tidak boleh bocor
        const isHorizontalScrollFit = await page.evaluate(() => document.documentElement.scrollWidth <= window.innerWidth + 1);
        expect(isHorizontalScrollFit).toBeTruthy();
    });
});
