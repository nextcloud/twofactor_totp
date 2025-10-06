/*
 * SPDX-FileCopyrightText: 2025 Olav and Niklas Seyfarth, Contributors <https://github.com/datenschutz-individuell/twofactor_email/blob/main/CONTRIBUTORS.md>
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

import { createAppConfig } from '@nextcloud/vite-config'

export default createAppConfig({
	personal_settings: 'src/PersonalSettings.js',
	login_setup: 'src/LoginSetup.js',
	login_challenge: 'src/LoginChallenge.css',
}, {
	extractLicenseInformation: {
		validateLicenses: true,
	},
})
