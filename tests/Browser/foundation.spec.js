import { test, expect } from '@playwright/test';

async function login(page) {
    if (!process.env.BROWSER_TEST_EMAIL || !process.env.BROWSER_TEST_PASSWORD) throw new Error('Use an isolated development admin via BROWSER_TEST_EMAIL/PASSWORD.');
    await page.goto('/login');
    await page.getByLabel('Email', { exact: true }).fill(process.env.BROWSER_TEST_EMAIL);
    await page.getByLabel('Password', { exact: true }).fill(process.env.BROWSER_TEST_PASSWORD);
    await page.getByRole('button', { name: 'Masuk', exact: true }).click();
    await expect(page).toHaveURL(/\/app$/);
}

test('session auth, invalid login, logout, and anonymous data guard', async ({ page }) => {
    await page.goto('/prototype');
    await expect(page).toHaveURL(/\/login$/);
    await page.getByLabel('Email', { exact: true }).fill('invalid@example.test');
    await page.getByLabel('Password', { exact: true }).fill('wrong');
    await page.getByRole('button', { name: 'Masuk', exact: true }).click();
    await expect(page.getByRole('alert')).toContainText('Email atau password');
    await login(page);
    await page.getByRole('button', { name: 'Keluar', exact: true }).click();
    await expect(page).toHaveURL(/\/login$/);
    await page.goto('/prototype');
    await expect(page).toHaveURL(/\/login$/);
});

for (const viewport of [{ width: 1366, height: 768 }, { width: 1024, height: 768 }, { width: 911, height: 722 }, { width: 768, height: 1024 }, { width: 390, height: 844 }]) {
    test(`existing UI remains usable at ${viewport.width}px`, async ({ page }) => {
        await page.setViewportSize(viewport);
        await login(page);
        for (const query of ['dashboard', 'projects', 'form', 'progresses&id=prog-003', 'drafts']) {
            await page.goto(`/prototype?tab=${query}`);
            await expect(page.locator('main')).toBeVisible();
            expect(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth + 1)).toBeTruthy();
            if (query === 'form') {
                await expect(page.locator('.entry-section')).toHaveCount(5);
                await expect(page.getByRole('button', { name: 'Simpan progress', exact: true })).toBeVisible();
                await expect(page.locator('label').filter({ hasText: 'PIC Progress' })).toHaveCount(0);
            }
        }
        await page.goto('/prototype?tab=projects&action=new');
        await expect(page.locator('dialog[open]')).toBeVisible();
        await expect(page.locator('dialog label').filter({ hasText: /^Spec$/ })).toHaveCount(0);
    });
}

test('empty and unchanged editor navigation do not create drafts; confirmed draft recovers', async ({ page }) => {
    await login(page);
    await page.goto('/prototype?tab=drafts');
    const initial = await page.locator('.draft-card').count();
    await page.goto('/prototype?tab=form');
    await page.getByRole('button', { name: 'Kembali ke daftar', exact: true }).click();
    await expect(page).toHaveURL(/tab=dashboard/);
    await page.goto('/prototype?tab=drafts');
    await expect(page.locator('.draft-card')).toHaveCount(initial);
    await page.goto('/prototype?tab=form&id=prog-003');
    await page.getByRole('button', { name: 'Kembali ke daftar', exact: true }).click();
    await expect(page).toHaveURL(/tab=dashboard/);
    await page.goto('/prototype?tab=drafts');
    await expect(page.locator('.draft-card')).toHaveCount(initial);
    await page.goto('/prototype?tab=form&id=prog-003');
    await page.locator('#f-description').fill('Catatan uji browser fiktif');
    await expect(page.locator('#save-indicator')).toContainText('Draft tersimpan');
    await page.reload();
    await expect(page.locator('#f-description')).toHaveValue('Catatan uji browser fiktif');
});

test('project dates can be cleared and Close/Reopen keeps saved data', async ({ page }) => {
    await login(page);
    await page.goto('/prototype?tab=projects&edit=proj-001');
    await page.locator('#master-t0Target').fill('');
    await page.locator('#master-planFinish').fill('');
    await page.getByRole('button', { name: 'Simpan project', exact: true }).click();
    await expect(page.locator('dialog[open]')).toHaveCount(0);
    await page.goto('/prototype?tab=projects&edit=proj-001');
    await expect(page.locator('#master-t0Target')).toHaveValue('');
    await expect(page.locator('#master-planFinish')).toHaveValue('');
    await page.goto('/prototype?tab=progresses&id=prog-003');
    const before = await page.locator('main').innerText();
    await page.getByTestId('close-progress-btn').click();
    await page.locator('dialog').getByRole('button', { name: 'Close progress', exact: true }).click();
    await expect(page.getByRole('button', { name: 'Reopen progress', exact: true })).toBeVisible();
    await page.getByRole('button', { name: 'Reopen progress', exact: true }).click();
    await expect(page.getByTestId('close-progress-btn')).toBeVisible();
    expect(await page.locator('main').innerText()).toBe(before);
});
