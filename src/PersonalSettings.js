/*
 * SPDX-FileCopyrightText: 2018 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

import Vue from 'vue'
import { loadState } from '@nextcloud/initial-state'
import store from './Store.js'

import PersonalSettings from './components/PersonalSettings.vue'

Vue.mixin({
	methods: {
		t,
	},
})

store.replaceState({
	enabled: loadState('twofactor_email', 'enabled'),
	hasEmail: loadState('twofactor_email', 'hasEmail'),
	email: loadState('twofactor_email', 'email'),
})

const View = Vue.extend(PersonalSettings)
new View({
	store,
}).$mount('#twofactor_email-personal_settings')
