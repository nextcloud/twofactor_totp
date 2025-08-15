/*
 * SPDX-FileCopyrightText: 2019 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

import Vue from 'vue'
import store from './store.js'

import LoginSetup from './components/LoginSetup.vue'

Vue.mixin({
	methods: {
		t,
	},
})

const View = Vue.extend(LoginSetup)
new View({
	store,
}).$mount('#twofactor-email-login-setup')
