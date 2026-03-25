/*
 * SPDX-FileCopyrightText: 2018 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

import { createApp } from 'vue'
import { createPinia } from 'pinia'
import { loadState } from '@nextcloud/initial-state'
import Logger from './logger.js'
import { useTotpStore } from './store.js'

import PersonalTotpSettings from './components/PersonalTotpSettings.vue'

const pinia = createPinia()
const app = createApp(PersonalTotpSettings)
app.mixin({ methods: { t } })
app.use(pinia)

const store = useTotpStore(pinia)
store.totpState = loadState('twofactor_totp', 'state')

app.mount('#twofactor-totp-settings')

Logger.debug('personal settings loaded and rendered')
