/*
 * SPDX-FileCopyrightText: 2025 Olav and Niklas Seyfarth, Contributors <https://github.com/datenschutz-individuell/twofactor_email/blob/master/CONTRIBUTORS.md>
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

import Vue from 'vue'
import { loadState } from '@nextcloud/initial-state'
import store from './Store.js'

import LoginSetup from './components/LoginSetup.vue'

Vue.mixin({
	methods: {
		t,
	},
})

store.replaceState({
	maskedEmail: loadState('twofactor_email', 'maskedEmail'),
})

const View = Vue.extend(LoginSetup)
new View({
	store,
}).$mount('#twofactor_email-atlogin_setup')
