import Vue from 'vue'
import Vuex from 'vuex'

import { saveState, getState, updateSettings, getDefaults } from './services/StateService.js'
import STATE from './state.js'

Vue.use(Vuex)

export const mutations = {
	setState(state, totpState) {
		state.totpState = totpState
	},
	setSettings(state, settings) {
		state.secret = settings.secret
		state.algorithm = settings.algorithm
		state.digits = settings.digits
		state.period = settings.period
	},
}

export const actions = {
	enable({ commit }) {
		return getDefaults().then(data => {
			const algorithm = data.defaultAlgorithm
			const digits = data.defaultDigits
			const period = data.defaultPeriod

			return saveState({ state: STATE.STATE_CREATED, algorithm, digits, period }).then(
				({ state, secret, qrUrl }) => {
					commit('setState', state)
					return { qrUrl, secret }
				},
			)
		})
	},

	recreateQrCode({ commit, state }, { secret }) {
		const algorithm = state.algorithm
		const digits = state.digits
		const period = state.period
		return saveState({ state: STATE.STATE_CREATED, secret, algorithm, digits, period }).then(
			({ state, secret, qrUrl }) => {
				commit('setState', state)
				commit('setSettings', { secret, algorithm, digits, period })
				return { qrUrl, secret }
			},
		)
	},

	confirm({ commit }, code) {
		return saveState({
			state: STATE.STATE_ENABLED,
			code,
		}).then(({ state }) => commit('setState', state))
	},

	disable({ commit }) {
		return saveState({ state: STATE.STATE_DISABLED }).then(({ state }) =>
			commit('setState', state),
		)
	},

	updateSettings({ commit }, settings) {
		return updateSettings(settings).then(() => {
			commit('setSettings', settings)
		})
	},

	getSettings({ commit }) {
		return getState().then(({ algorithm, digits, period }) => {
			commit('setSettings', { algorithm, digits, period })
		})
	},
}

export const getters = {}

export default new Vuex.Store({
	strict: process.env.NODE_ENV !== 'production',
	state: {
		totpState: undefined,
		secret: null,
		algorithm: 1, // default value
		digits: 6, // default value
		period: 30, // default value
	},
	getters,
	mutations,
	actions,
})
