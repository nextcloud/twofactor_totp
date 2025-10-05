/*
 * SPDX-FileCopyrightText: 2025 Olav and Niklas Seyfarth, Contributors <https://github.com/datenschutz-individuell/twofactor_email/blob/main/CONTRIBUTORS.md>
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

global.OC = {
	getCurrentUser: () => {
		return { uid: false }
	},
	isUserAdmin: () => false,
}
