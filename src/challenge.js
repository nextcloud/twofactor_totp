/*
 * SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

/**
 * Remove every non-digit character from a TOTP code
 *
 * @param {string} value the raw input value
 * @return {string}
 */
export function sanitizeCode(value) {
	return value.replace(/\D/g, '')
}

/**
 * Keep only digits in the given input while the user types or pastes
 *
 * @param {HTMLInputElement} input the TOTP code input
 */
export function restrictToDigits(input) {
	input.addEventListener('input', () => {
		const sanitized = sanitizeCode(input.value)
		if (sanitized !== input.value) {
			input.value = sanitized
		}
	})
}
