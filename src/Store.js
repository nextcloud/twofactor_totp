/*
 * SPDX-FileCopyrightText: 2025 Olav and Niklas Seyfarth, Contributors <https://github.com/datenschutz-individuell/twofactor_email/blob/main/CONTRIBUTORS.md>
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

import { createPinia, defineStore } from 'pinia'
import { loadState } from '@nextcloud/initial-state'

import { persist } from './services/StateManager.js'

export const pinia = createPinia()

export const usePersonalSettingsStore = defineStore('personalSettings', {
	state: () => ({
		enabled: false,
		hasEmail: false,
		maskedEmail: 'UNEXPECTED ERROR: no e-mail address set',
		email: 'UNEXPECTED ERROR: no e-mail address set',
		error: false,
	}),
	actions: {
		/**
		 * Loads the initial state from Nextcloud into the Store.
		 * Only tries to fetch the given keys.
		 * All initial state keys must be the same as in the store.
		 *
		 * @param {string} keys keys to load from initial state
		 */
		loadInitialState(...keys) {
			const initialState = {}
			for (const key of keys) {
				initialState[key] = loadState('twofactor_email', key)
			}
			this.$patch(initialState)
		},
		async save() {
			const result = await persist(this.enabled)
			this.$patch({
				enabled: result.enabled ?? this.enabled,
				error: result.error,
			})
		},
		async enable() {
			this.enabled = true
			await this.save()
		},
	},
})
