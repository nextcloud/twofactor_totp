/*
 * SPDX-FileCopyrightText: 2018 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

import Vue from 'vue'
import { loadState } from '@nextcloud/initial-state'
import Logger from './logger.js'
import store from './store.js'

import PersonalTotpSettings from './components/PersonalTotpSettings.vue'

Vue.mixin({
	methods: {
		t,
	},
})

store.replaceState({
	totpState: loadState('twofactor_totp', 'state'),
})

const View = Vue.extend(PersonalTotpSettings)
new View({
	store,
}).$mount('#twofactor-totp-settings')

Logger.debug('personal settings loaded and rendered')
