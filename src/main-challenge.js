/*
 * SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

import { restrictToDigits } from './challenge.js'

const input = document.querySelector('.totp-form input[name="challenge"]')
if (input) {
	restrictToDigits(input)
}
