/*
 * SPDX-FileCopyrightText: 2018 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

import { shallowMount, createLocalVue } from '@vue/test-utils'
import { createPinia, setActivePinia, PiniaVuePlugin } from 'pinia'

import PersonalTotpSettings from '../../components/PersonalTotpSettings.vue'

const localVue = createLocalVue()
localVue.use(PiniaVuePlugin)

describe('PersonalTotpSettings', () => {
	beforeEach(() => {
		setActivePinia(createPinia())
	})

	it('does not load on start', () => {
		const settings = shallowMount(PersonalTotpSettings, { localVue })

		expect(settings.vm.loading).to.be.false
	})
})
