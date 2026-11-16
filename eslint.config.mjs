/**
 * SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

import { recommended } from '@nextcloud/eslint-config'
import chaiFriendly from 'eslint-plugin-chai-friendly'

export default [
	{
		ignores: ['js/**', 'vendor/**', 'vendor-bin/**', 'src/tests/e2e/**'],
	},
	...recommended,
	{
		files: ['src/tests/**/*.js'],
		...chaiFriendly.configs.recommendedFlat,
		languageOptions: {
			globals: {
				afterAll: 'readonly',
				afterEach: 'readonly',
				beforeAll: 'readonly',
				beforeEach: 'readonly',
				describe: 'readonly',
				expect: 'readonly',
				it: 'readonly',
				vi: 'readonly',
			},
		},
	},
	{
		rules: {
			// v-model:arg is valid Vue 3 syntax
			'vue/no-v-model-argument': 'off',
		},
	},
]
