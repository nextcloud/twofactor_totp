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
    await enforceTwofactorAuth(true)
})

test.beforeEach(async () => {
    await disableTwofactorAuth(['admin'])
})

test('set up and log in', async ({ page }) => {
    // Log in and choose to set up TOTP
    await login(page, false)
    await page.waitForURL('./index.php/login/setupchallenge')
    await page.getByRole('link', { name: 'TOTP (Authenticator app)' }).click()

    // Assert setup instructions to be shown
    await expect(page.locator('#body-login')).toContainText('Your new TOTP secret is:')
    await expect(page.locator('#body-login')).toContainText('For quick setup, scan this QR code with your TOTP app:')
    await expect(page.locator('canvas')).toBeVisible()
    await expect(page.locator('#body-login')).toContainText('After you configured your app, enter a test code below to ensure everything works correctly:')

    // Extract TOTP key and enter token
    const key = await extractTotpKey(page)
    const { otp: otp1, expires } = await TOTP.generate(key)
    await page.getByRole('textbox', { name: 'Authentication code' }).fill(otp1)
    await page.getByRole('button', { name: 'Verify' }).click()

    // Extract TOTP key and enter token to set up TOTP
    await page.getByRole('link', { name: 'TOTP (Authenticator app)' }).click()
    await expect(page.locator('#body-login')).toContainText('Get the authentication code from the two-factor authentication app on your device.')

    // Wait for next token and log in
    await waitForNextToken(page, expires)
    const { otp: otp2 } = await TOTP.generate(key)
    await page.getByRole('textbox', { name: 'Authentication code' }).fill(otp2)
    await page.getByRole('button', { name: 'Submit' }).click()

	// Assert that log in was successful
    await expect(page).toHaveURL(/.*\/apps\/.*/)
})

test('stays on setup screen when wrong verification code is entered', async ({ page }) => {
    await login(page, false)
    await page.waitForURL('./index.php/login/setupchallenge')
    await page.getByRole('link', { name: 'TOTP (Authenticator app)' }).click()

    // Enter a wrong code
    await page.getByRole('textbox', { name: 'Authentication code' }).fill('000000')
    await page.getByRole('button', { name: 'Verify' }).click()

    // Setup screen should still be visible
    await expect(page.locator('#body-login')).toContainText('Your new TOTP secret is:')
})

test('submits verification code with Enter key', async ({ page }) => {
    await login(page, false)
    await page.waitForURL('./index.php/login/setupchallenge')
    await page.getByRole('link', { name: 'TOTP (Authenticator app)' }).click()

    // Enter a valid code and submit with Enter instead of clicking Verify
    const key = await extractTotpKey(page)
    const { otp, expires } = await TOTP.generate(key)
    await page.getByRole('textbox', { name: 'Authentication code' }).fill(otp)
    await page.getByRole('textbox', { name: 'Authentication code' }).press('Enter')

    // Should proceed to the TOTP login challenge
    await page.getByRole('link', { name: 'TOTP (Authenticator app)' }).click()
    await expect(page.locator('#body-login')).toContainText('Get the authentication code from the two-factor authentication app on your device.')

    // Complete login with a fresh token
    await waitForNextToken(page, expires)
    const { otp: otp2 } = await TOTP.generate(key)
    await page.getByRole('textbox', { name: 'Authentication code' }).fill(otp2)
    await page.getByRole('button', { name: 'Submit' }).click()

    // Assert that log in was successful
    await expect(page).toHaveURL(/.*\/apps\/.*/)
})