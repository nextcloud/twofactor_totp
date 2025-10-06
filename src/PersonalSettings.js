/*
 * SPDX-FileCopyrightText: 2025 Olav and Niklas Seyfarth, Contributors <https://github.com/datenschutz-individuell/twofactor_email/blob/main/CONTRIBUTORS.md>
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

import { createApp } from 'vue'

import { pinia } from './Store.js'
import PersonalSettings from './components/PersonalSettings.vue'

const View = createApp(PersonalSettings)
	.use(pinia)
View.mount('#twofactor_email-personal_settings')
