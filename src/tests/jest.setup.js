/*
 * SPDX-FileCopyrightText: 2018 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

import { config } from '@vue/test-utils'

config.global.mocks.t = (app, str) => str

global.expect = require('chai').expect
window.Date = Date

// https://github.com/jsdom/jsdom/issues/3363
if (typeof global.structuredClone !== 'function') {
	global.structuredClone = (obj) => JSON.parse(JSON.stringify(obj))
}

global.OC = {
	getCurrentUser: () => {
		return { uid: false }
	},
	isUserAdmin: () => false,
}
