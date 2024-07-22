import Vue from 'vue'
import Vuex from 'vuex'

import { saveState, getState, updateSettings } from './services/StateService.js'
import state from './state.js'

Vue.use(Vuex)

export const mutations = {
	setState(state, totpState) {
		state.totpState = totpState
	},
	setSettings(state, settings) {
		state.tokenLength = settings.tokenLength
		state.hashAlgorithm = settings.hashAlgorithm
	},
}

export const actions = {
	enable({ commit }) {
		return saveState({ state: state.STATE_CREATED }).then(
			({ state, secret, qrUrl }) => {
				commit('setState', state)
				return { qrUrl, secret }
			},
		)
	},

	confirm({ commit }, code) {
		return saveState({
			state: state.STATE_ENABLED,
			code,
		}).then(({ state }) => commit('setState', state))
	},

	disable({ commit }) {
		return saveState({ state: state.STATE_DISABLED }).then(({ state }) =>
			commit('setState', state),
		)
	},

	updateSettings({ commit }, settings) {
		return updateSettings(settings).then(() => {
			commit('setSettings', settings)
		})
	},

	getSettings({ commit }) {
		return getState().then(({ tokenLength, hashAlgorithm }) => {
			commit('setSettings', { tokenLength, hashAlgorithm })
		})
	},
}

export const getters = {}

export default new Vuex.Store({
	strict: process.env.NODE_ENV !== 'production',
	state: {
		totpState: undefined,
		tokenLength: 6, // default value
		hashAlgorithm: 1, // default value
	},
	getters,
	mutations,
	actions,
})
