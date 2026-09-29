/*
 * SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

import { restrictToDigits, sanitizeCode } from '../challenge.js'

describe('sanitizeCode', () => {
	it.each([
		['123456', '123456'],
		['123 456', '123456'],
		['12-34-56', '123456'],
		['abc', ''],
		['', ''],
	])('turns %p into %p', (value, expected) => {
		expect(sanitizeCode(value)).to.equal(expected)
	})
})

describe('restrictToDigits', () => {
	it('strips non-digits on input', () => {
		const input = document.createElement('input')
		restrictToDigits(input)

		input.value = '12a3 4'
		input.dispatchEvent(new Event('input'))

		expect(input.value).to.equal('1234')
	})

	it('leaves a digit-only value untouched', () => {
		const input = document.createElement('input')
		restrictToDigits(input)

		input.value = '123456'
		input.dispatchEvent(new Event('input'))

		expect(input.value).to.equal('123456')
	})
})
