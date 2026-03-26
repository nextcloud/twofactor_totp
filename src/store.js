/*
 * SPDX-FileCopyrightText: 2017 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

import { defineStore } from 'pinia'

import { saveState } from './services/StateService.js'
import STATE from './state.js'

export const useTotpStore = defineStore('totp', {
	state: () => ({
		totpState: undefined,
	}),
	actions: {
		async enable() {
			const { state, secret, qrUrl } = await saveState({ state: STATE.STATE_CREATED })
			this.totpState = state
			return { secret, qrUrl }
		},

		async confirm(code) {
			const { state } = await saveState({ state: STATE.STATE_ENABLED, code })
			this.totpState = state
		},

		async disable() {
			const { state } = await saveState({ state: STATE.STATE_DISABLED })
			this.totpState = state
		},
	},
})
