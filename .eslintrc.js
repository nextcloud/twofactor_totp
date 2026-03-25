/*
 * SPDX-FileCopyrightText: 2019 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

module.exports = {
	extends: [
		'@nextcloud',
		'plugin:vue/vue3-recommended',
		'plugin:chai-friendly/recommended'
	],
	rules: {
		// v-model:arg is valid Vue 3 syntax; the Vue 2 rule from @nextcloud must be disabled
		'vue/no-v-model-argument': 'off',
	},
}