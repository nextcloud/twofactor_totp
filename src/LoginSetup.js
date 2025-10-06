/*
 * SPDX-FileCopyrightText: 2025 Olav and Niklas Seyfarth, Contributors <https://github.com/datenschutz-individuell/twofactor_email/blob/main/CONTRIBUTORS.md>
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

import { createApp } from 'vue'

import { pinia } from './Store.js'
import LoginSetup from './components/LoginSetup.vue'

const View = createApp(LoginSetup)
	.use(pinia)
View.mount('#twofactor_email-login_setup')
