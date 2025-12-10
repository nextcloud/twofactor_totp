/**
 * SPDX-FileCopyrightText: 2025 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

import { type Page } from "@playwright/test"

const yourNewTotpSecretIs = 'Your new TOTP secret is:'

export async function extractTotpKey(page: Page): Promise<string> {
    const humanReadableKeyText = await page.getByText(yourNewTotpSecretIs).innerText()
    const keyMatches = new RegExp(`${yourNewTotpSecretIs} ([A-Z2-7=]+)`).exec(humanReadableKeyText)
    const key = keyMatches?.[1]
    if (!key) {
        throw new Error('Could not find TOTP key')
    }

    return key
}

export async function waitForNextToken(page: Page, expires: number): Promise<void> {
    await page.waitForTimeout(Math.max(expires - new Date().getTime() + 1000, 0))
}