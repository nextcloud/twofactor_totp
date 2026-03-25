/*
 * SPDX-FileCopyrightText: 2018 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

import { shallowMount } from '@vue/test-utils'
import { createPinia, setActivePinia } from 'pinia'

import PersonalTotpSettings from '../../components/PersonalTotpSettings.vue'

describe('PersonalTotpSettings', () => {
	let pinia

	beforeEach(() => {
		pinia = createPinia()
		setActivePinia(pinia)
	})

	it('does not load on start', () => {
		const settings = shallowMount(PersonalTotpSettings, {
			global: {
				plugins: [pinia],
			},
		})

		expect(settings.vm.loading).to.be.false
	})
})
