import Axios from 'nextcloud-axios'
import Vue from 'vue'
import Vuex from 'vuex'

import state from './state'

Vue.use(Vuex)

export const mutations = {
	setState (state, totpState) {
		state.totpState = totpState
	}
}

const saveState = (data) => {
	const url = OC.generateUrl('/apps/twofactor_totp/settings/enable');
	return Axios.post(url, data)
		.then(resp => resp.data)
}

export const actions = {
	enable ({commit}) {
		return saveState({state: state.STATE_CREATED})
			.then(({state, secret, qr}) => {
				commit('setState', state)
				return {qr, secret}
			})
	},

	confirm ({commit}, code) {
		return saveState({state: state.STATE_ENABLED, code: code})
			.then(({state}) => commit('setState', state))
	},

	disable ({commit}) {
		return saveState({state: state.STATE_DISABLED})
			.then(({state}) => commit('setState', state))
	}
}

export const getters = {}

export default new Vuex.Store({
	strict: process.env.NODE_ENV !== 'production',
	state: {
		totpState: undefined
	},
	getters,
	mutations,
	actions
})
