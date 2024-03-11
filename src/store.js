import Vue from 'vue'
import Vuex from 'vuex'

import { saveState } from './services/StateService.js'
import state from './state.js'

Vue.use(Vuex)

export const mutations = {
	setState(state, emailState) {
		state.emailState = emailState
	},
}

export const actions = {
	enable({ commit }) {
		return saveState({ state: state.STATE_CREATED }).then(
			({ state, email }) => {
				commit('setState', state)
				return { email }
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
}

export const getters = {}

export default new Vuex.Store({
	strict: process.env.NODE_ENV !== 'production',
	state: {
		emailState: undefined,
	},
	getters,
	mutations,
	actions,
})
