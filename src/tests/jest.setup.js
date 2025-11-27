/*
 * SPDX-FileCopyrightText: 2018 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

import Vue from 'vue'

Vue.mixin({
	methods: {
		t: (app, str) => str,
	},
})

global.expect = require('chai').expect
// https://github.com/vuejs/vue-test-utils/issues/936
// better fix for "TypeError: Super expression must either be null or
// a function" than pinning an old version of prettier.
//
// https://github.com/vuejs/vue-cli/issues/2128#issuecomment-453109575
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
