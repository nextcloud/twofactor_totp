/*
 * SPDX-FileCopyrightText: 2019 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

module.exports = {
	extends: [
		'@nextcloud',
		'plugin:chai-friendly/recommended'
	],
	rules: {
		// v-model:arg is valid Vue 2.7+ syntax (replaces .sync); silence the Vue 2 lint rule
		'vue/no-v-model-argument': 'off',
	},
}