/*
 * SPDX-FileCopyrightText: 2019 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

import { createApp } from 'vue'
import Logger from './logger.js'
import LoginSetup from './components/LoginSetup.vue'

Logger.debug('rendering login setup view')
const app = createApp(LoginSetup)
app.mixin({ methods: { t } })
app.mount('#twofactor-totp-login-setup')
Logger.debug('login setup view rendered')
