/*
 * SPDX-FileCopyrightText: 2019 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

import Vue from 'vue'
import Logger from './logger.js'
import LoginSetup from './components/LoginSetup.vue'

Vue.mixin({
	methods: {
		t,
	},
})

Logger.debug('rendering login setup view')
const View = Vue.extend(LoginSetup)
new View().$mount('#twofactor-totp-login-setup')
Logger.debug('login setup view rendered')
