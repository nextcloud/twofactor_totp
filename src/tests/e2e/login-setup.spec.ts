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

test('wrong code stays on setup screen', async ({ page }) => {
    // Log in and choose to set up TOTP
    await login(page, false)
    await page.waitForURL('./index.php/login/setupchallenge')
    await page.getByRole('link', { name: 'TOTP (Authenticator app)' }).click()

    // Assert setup form is shown
    await expect(page.locator('#body-login')).toContainText('Your new TOTP secret is:')

    // Enter a wrong code and verify
    await page.getByRole('textbox', { name: 'Authentication code' }).fill('000000')
    await page.getByRole('button', { name: 'Verify' }).click()

    // Assert that the setup form is still shown (not redirected)
    await expect(page.locator('#body-login')).toContainText('Your new TOTP secret is:')
    await expect(page).not.toHaveURL(/.*\/apps\/.*/)
})

test('submit setup code via Enter key', async ({ page }) => {
    test.slow()

    // Log in and choose to set up TOTP
    await login(page, false)
    await page.waitForURL('./index.php/login/setupchallenge')
    await page.getByRole('link', { name: 'TOTP (Authenticator app)' }).click()

    // Assert setup instructions to be shown
    await expect(page.locator('#body-login')).toContainText('Your new TOTP secret is:')

    // Extract key and submit via Enter key
    const key = await extractTotpKey(page)
    const { otp: otp1, expires } = await TOTP.generate(key)
    await page.getByRole('textbox', { name: 'Authentication code' }).fill(otp1)
    await page.getByRole('textbox', { name: 'Authentication code' }).press('Enter')

    // Proceed to TOTP challenge and log in
    await page.getByRole('link', { name: 'TOTP (Authenticator app)' }).click()
    await expect(page.locator('#body-login')).toContainText('Get the authentication code from the two-factor authentication app on your device.')

    await waitForNextToken(page, expires)
    const { otp: otp2 } = await TOTP.generate(key)
    await page.getByRole('textbox', { name: 'Authentication code' }).fill(otp2)
    await page.getByRole('button', { name: 'Submit' }).click()

    // Assert that log in was successful
    await expect(page).toHaveURL(/.*\/apps\/.*/)
})