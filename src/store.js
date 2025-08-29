/*
 * SPDX-FileCopyrightText: 2017 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

import Vue from 'vue'
import Vuex from 'vuex'

import { persist } from './services/StateService.js'

Vue.use(Vuex)

export const mutations = {
	setEnabled(state, enabled) {
		state.enabled = enabled
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
		maskedEmail: 'ERROR [no email set]',
		email: '',
		error: false,
	},
	mutations,
	actions,
})
