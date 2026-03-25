/*
 * SPDX-FileCopyrightText: 2025 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

import { createPinia, setActivePinia } from 'pinia'
import { useTotpStore } from '../store.js'
import STATE from '../state.js'

jest.mock('../services/StateService.js', () => ({
	saveState: jest.fn(),
}))

import { saveState } from '../services/StateService.js'

describe('totp store', () => {
	beforeEach(() => {
		setActivePinia(createPinia())
		jest.resetAllMocks()
	})

	describe('initial state', () => {
		it('initialises totpState as undefined', () => {
			const store = useTotpStore()
			expect(store.totpState).to.be.undefined
		})
	})

	describe('actions', () => {
		it('enable calls saveState with STATE_CREATED and updates totpState', async () => {
			saveState.mockResolvedValue({
				state: STATE.STATE_CREATED,
				secret: 'JBSWY3DPEHPK3PXP',
				qrUrl: 'otpauth://totp/test',
			})

			const store = useTotpStore()
			await store.enable()

			expect(saveState.mock.calls[0][0]).to.deep.equal({ state: STATE.STATE_CREATED })
			expect(store.totpState).to.equal(STATE.STATE_CREATED)
		})

		it('enable returns the secret and qrUrl from the response', async () => {
			saveState.mockResolvedValue({
				state: STATE.STATE_CREATED,
				secret: 'JBSWY3DPEHPK3PXP',
				qrUrl: 'otpauth://totp/test',
			})

			const store = useTotpStore()
			const result = await store.enable()

			expect(result).to.deep.equal({
				secret: 'JBSWY3DPEHPK3PXP',
				qrUrl: 'otpauth://totp/test',
			})
		})

		it('confirm calls saveState with STATE_ENABLED and the code, and updates totpState', async () => {
			saveState.mockResolvedValue({ state: STATE.STATE_ENABLED })

			const store = useTotpStore()
			await store.confirm('123456')

			expect(saveState.mock.calls[0][0]).to.deep.equal({
				state: STATE.STATE_ENABLED,
				code: '123456',
			})
			expect(store.totpState).to.equal(STATE.STATE_ENABLED)
		})

		it('disable calls saveState with STATE_DISABLED and updates totpState', async () => {
			saveState.mockResolvedValue({ state: STATE.STATE_DISABLED })

			const store = useTotpStore()
			await store.disable()

			expect(saveState.mock.calls[0][0]).to.deep.equal({ state: STATE.STATE_DISABLED })
			expect(store.totpState).to.equal(STATE.STATE_DISABLED)
		})
	})
})
