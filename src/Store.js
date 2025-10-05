/*
 * SPDX-FileCopyrightText: 2025 Olav and Niklas Seyfarth, Contributors <https://github.com/datenschutz-individuell/twofactor_email/blob/master/CONTRIBUTORS.md>
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

import Vue from 'vue'
import Vuex from 'vuex'

import { persist } from './services/StateManager.js'

Vue.use(Vuex)

export const mutations = {
	setEnabled(state, enabled) {
		if (enabled !== null) {
			state.enabled = enabled
		}
	},
}

export const actions = {
	enable({ commit }) {
		return persist(true)
			.then(({ enabled, error }) => {
				commit('setEnabled', enabled)
				return { enabled, error }
			})
	},

	disable({ commit }) {
		return persist(false)
			.then(({ enabled }) => {
				commit('setEnabled', enabled)
				return { enabled }
			})
	},
}

export default new Vuex.Store({
	strict: process.env.NODE_ENV !== 'production',
	state: {
		enabled: false,
		hasEmail: false,
		maskedEmail: 'UNEXPECTED ERROR: no e-mail address set',
		email: 'UNEXPECTED ERROR: no e-mail address set',
		error: false,
	},
	mutations,
	actions,
})
