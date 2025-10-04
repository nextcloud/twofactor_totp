/*
 * SPDX-FileCopyrightText: 2019 Nextcloud GmbH and Nextcloud contributors
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
