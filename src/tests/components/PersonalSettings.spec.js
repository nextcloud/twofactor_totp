/*
 * SPDX-FileCopyrightText: 2018 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

import { shallowMount, createLocalVue } from '@vue/test-utils'
import Vuex from 'vuex'

import PersonalSettings from '../../components/PersonalSettings.vue'

const localVue = createLocalVue()

localVue.use(Vuex)

describe('PersonalSettings', () => {
	let actions
	let store

	beforeEach(() => {
		actions = {
			enable: () => {},
			disable: () => {},
		}
		store = new Vuex.Store({
			state: {},
			actions,
		})
	})

	it('does not load on start', () => {
		const settings = shallowMount(PersonalSettings, {
			store,
			localVue,
		})

		expect(settings.vm.loading).to.be.false
	})
})
