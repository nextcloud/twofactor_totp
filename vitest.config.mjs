/**
 * SPDX-FileCopyrightText: 2026 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

import vue from '@vitejs/plugin-vue'
import { defineConfig } from 'vitest/config'

export default defineConfig({
	plugins: [vue()],
	test: {
		environment: 'jsdom',
		globals: true,
		include: ['src/tests/**/*.spec.js'],
		server: {
			deps: {
				inline: [/@nextcloud\//],
			},
		},
		setupFiles: ['./src/tests/vitest.setup.js'],
	},
})
