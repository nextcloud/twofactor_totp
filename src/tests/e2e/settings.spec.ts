/**
 * SPDX-FileCopyrightText: 2025 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

import { test, expect, type Page } from '@playwright/test'
import { login } from './login'
import { TOTP } from 'totp-generator'
import { disableTwofactorAuth, enforceTwofactorAuth } from './occ'
import { extractTotpKey, waitForNextToken } from './totp'

test.beforeAll(async () => {
	await enforceTwofactorAuth(false)
})

test.beforeEach(async ({ page }) => {
	await disableTwofactorAuth(['admin'])
	await login(page)
})

export async function enableTotp(page: Page) {
    // Go to personal settings and enable TOTP
    await page.getByRole('button', { name: 'Settings menu' }).click()
    await page.getByRole('link', { name: 'Personal settings' }).click()
    await page.getByLabel('Personal').getByRole('link', { name: 'Security' }).click()
    await page.getByText('Enable TOTP').click()

    // Assert setup instructions to be shown
    await expect(page.locator('#twofactor-totp-settings')).toContainText('Your new TOTP secret is:')
    await expect(page.locator('#twofactor-totp-settings')).toContainText('For quick setup, scan this QR code with your TOTP app:')
    await expect(page.locator('canvas')).toBeVisible();
    await expect(page.locator('#twofactor-totp-settings')).toContainText('After you configured your app, enter a test code below to ensure everything works correctly:')

    // Generate a fake TOTP and enter it
    const key = await extractTotpKey(page)
    const { otp, expires } = await TOTP.generate(key)
    await page.getByRole('textbox', { name: 'Authentication code' }).click()
    await page.getByRole('textbox', { name: 'Authentication code' }).fill(otp)
    await page.getByRole('button', { name: 'Verify' }).click()

    // Assert that TOTP was successfully enabled
    await expect(page.locator('#totp-enabled')).toBeChecked()

    return {
        key,
        tokenExpires: expires,
    }
}

test('enable TOTP and log in', async ({ page }) => {
    test.slow()

    const { key, tokenExpires } = await enableTotp(page)

    // Log out
    await page.getByRole('button', { name: 'Settings menu' }).click();
    await page.getByRole('link', { name: 'Log out' }).click();
    await page.waitForURL('./index.php/login*')

    // Log back in using a code
    await waitForNextToken(page, tokenExpires)
    await page.locator('#user').fill('admin')
	await page.locator('#password').fill('admin')
	await page.locator('#password').press('Enter')
    const { otp } = await TOTP.generate(key)
    await page.getByRole('textbox', { name: 'Authentication code' }).fill(otp);
    await page.getByRole('button', { name: 'Submit' }).click();

    // Assert that the user is logged in
    await expect(page).toHaveURL(/.*\/apps\/.*/)
})

test('enable TOTP and disable it again', async ({ page }) => {
    await enableTotp(page)

    // Disable TOTP
    await page.getByText('Enable TOTP').click()

    // Assert that TOTP was successfully disabled
    await expect(page.locator('#totp-enabled')).not.toBeChecked()
})

test('shows error when an incorrect verification code is entered', async ({ page }) => {
    // Go to personal settings and trigger TOTP setup
    await page.getByRole('button', { name: 'Settings menu' }).click()
    await page.getByRole('link', { name: 'Personal settings' }).click()
    await page.getByLabel('Personal').getByRole('link', { name: 'Security' }).click()
    await page.getByText('Enable TOTP').click()

    // Enter a wrong code
    await page.getByRole('textbox', { name: 'Authentication code' }).fill('000000')
    await page.getByRole('button', { name: 'Verify' }).click()

    // Setup form should still be visible and checkbox unchecked
    await expect(page.locator('#twofactor-totp-settings')).toContainText('Your new TOTP secret is:')
    await expect(page.locator('#totp-enabled')).not.toBeChecked()
})

test('submits verification code with Enter key', async ({ page }) => {
    // Go to personal settings and trigger TOTP setup
    await page.getByRole('button', { name: 'Settings menu' }).click()
    await page.getByRole('link', { name: 'Personal settings' }).click()
    await page.getByLabel('Personal').getByRole('link', { name: 'Security' }).click()
    await page.getByText('Enable TOTP').click()

    // Enter a valid code and submit with Enter instead of clicking Verify
    const key = await extractTotpKey(page)
    const { otp } = await TOTP.generate(key)
    await page.getByRole('textbox', { name: 'Authentication code' }).fill(otp)
    await page.getByRole('textbox', { name: 'Authentication code' }).press('Enter')

    // Assert that TOTP was successfully enabled
    await expect(page.locator('#totp-enabled')).toBeChecked()
})

test('can re-enable TOTP after disabling it', async ({ page }) => {
    await enableTotp(page)

    // Disable TOTP
    await page.getByText('Enable TOTP').click()
    await expect(page.locator('#totp-enabled')).not.toBeChecked()

    // Re-enable TOTP — click the checkbox again to trigger a new setup
    await page.getByText('Enable TOTP').click()
    await expect(page.locator('#twofactor-totp-settings')).toContainText('Your new TOTP secret is:')

    // Complete the new setup
    const key = await extractTotpKey(page)
    const { otp } = await TOTP.generate(key)
    await page.getByRole('textbox', { name: 'Authentication code' }).fill(otp)
    await page.getByRole('button', { name: 'Verify' }).click()

    // Assert that TOTP is enabled again
    await expect(page.locator('#totp-enabled')).toBeChecked()
})