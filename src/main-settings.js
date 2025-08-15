/*
 * SPDX-FileCopyrightText: 2018 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

import Vue from 'vue'
import { loadState } from '@nextcloud/initial-state'
import store from './store.js'

import PersonalEMailSettings from './components/PersonalEMailSettings.vue'

Vue.mixin({
	methods: {
		t,
	},
})

store.replaceState({
	enabled: loadState('twofactor_email', 'enabled'),
	hasEmail: loadState('twofactor_email', 'hasEmail'),
})

const View = Vue.extend(PersonalEMailSettings)
new View({
	store,
}).$mount('#twofactor-email-settings')
