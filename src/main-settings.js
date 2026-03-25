/*
 * SPDX-FileCopyrightText: 2018 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

import Vue from 'vue'
import { createPinia, PiniaVuePlugin } from 'pinia'
import { loadState } from '@nextcloud/initial-state'
import Logger from './logger.js'
import { useTotpStore } from './store.js'

import PersonalTotpSettings from './components/PersonalTotpSettings.vue'

Vue.use(PiniaVuePlugin)
Vue.mixin({
	methods: {
		t,
	},
})

const pinia = createPinia()
const store = useTotpStore(pinia)
store.totpState = loadState('twofactor_totp', 'state')

const View = Vue.extend(PersonalTotpSettings)
new View({ pinia }).$mount('#twofactor-totp-settings')

Logger.debug('personal settings loaded and rendered')
