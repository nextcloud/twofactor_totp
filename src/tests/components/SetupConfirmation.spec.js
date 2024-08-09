/**
 * @copyright 2024 [ernolf] Raphael Gradenwitz <raphael.gradenwitz@googlemail.com>
 *
 * @author 2024 [ernolf] Raphael Gradenwitz <raphael.gradenwitz@googlemail.com>
 *
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

import { shallowMount, createLocalVue } from '@vue/test-utils'
import Vuex from 'vuex'

import SetupConfirmation from '../../components/SetupConfirmation.vue'

const localVue = createLocalVue()

localVue.use(Vuex)

describe('SetupConfirmation', () => {
	let store
	let actions

	beforeEach(() => {
		actions = {
			getSettings: jest.fn().mockResolvedValue({
				algorithm: null,
				digits: null,
				period: null,
			}),
			updateSettings: jest.fn().mockResolvedValue(),
			recreateQrCode: jest.fn().mockResolvedValue({
				secret: 'newSecret',
				qrUrl: 'newQrUrl',
			}),
		}
		store = new Vuex.Store({
			state: {
				algorithm: null,
				digits: null,
				period: null,
			},
			actions,
		})
	})

	it('renders correctly', () => {
		const wrapper = shallowMount(SetupConfirmation, {
			store,
			localVue,
			propsData: {
				secret: 'testSecret',
				qrUrl: 'testQrUrl',
				loading: false,
			},
		})
		// Check if component is rendered
		if (!wrapper.element) {
			throw new Error('Component did not render correctly')
		}
	})

	it('toggles advanced settings', async () => {
		const wrapper = shallowMount(SetupConfirmation, {
			store,
			localVue,
			propsData: {
				secret: 'testSecret',
				qrUrl: 'testQrUrl',
				loading: false,
			},
		})
		// Check initial value
		if (wrapper.vm.showAdvanced !== false) {
			throw new Error('showAdvanced should be false')
		}
		wrapper.vm.toggleAdvancedSettings()
		await wrapper.vm.$nextTick()
		if (wrapper.vm.showAdvanced !== true) {
			throw new Error('showAdvanced should be true')
		}
	})

	it('fetches settings', async () => {
		const wrapper = shallowMount(SetupConfirmation, {
			store,
			localVue,
			propsData: {
				secret: 'testSecret',
				qrUrl: 'testQrUrl',
				loading: false,
			},
		})
		await wrapper.vm.fetchSettings()
		// Verify actions are called
		if (actions.getSettings.mock.calls.length === 0) {
			throw new Error('getSettings should have been called')
		}
		// Check if values are null
		if (wrapper.vm.algorithm !== null || wrapper.vm.digits !== null || wrapper.vm.period !== null) {
			throw new Error('Values should be null initially')
		}
	})

	it('confirms TOTP', () => {
		const wrapper = shallowMount(SetupConfirmation, {
			store,
			localVue,
			propsData: {
				secret: 'testSecret',
				qrUrl: 'testQrUrl',
				loading: false,
			},
		})
		wrapper.setData({ confirmationCode: '123456' })
		wrapper.vm.confirm()
		// Check if confirm event is emitted
		if (!wrapper.emitted().confirm) {
			throw new Error('confirm event should be emitted')
		}
		const emittedEvent = wrapper.emitted().confirm[0]
		if (
			emittedEvent[0].algorithm !== wrapper.vm.algorithm
			|| emittedEvent[0].digits !== wrapper.vm.digits
			|| emittedEvent[0].period !== wrapper.vm.period
		) {
			throw new Error('Values in confirm event are incorrect')
		}
	})

	it('recreates QR code', async () => {
		const wrapper = shallowMount(SetupConfirmation, {
			store,
			localVue,
			propsData: {
				secret: 'testSecret',
				qrUrl: 'testQrUrl',
				loading: false,
			},
		})

		wrapper.setData({
			customSecret: 'customSecret',
			algorithm: 2,
			digits: 8,
			period: 45,
		})

		await wrapper.vm.recreateQRCode()

		// Check if updateSettings was called
		console.log('updateSettings calls:', actions.updateSettings.mock.calls)
		if (actions.updateSettings.mock.calls.length !== 1) {
			throw new Error('updateSettings should have been called once')
		}
		/* TODO
		// Check if recreateQrCode was called
		console.log('recreateQrCode calls:', actions.recreateQrCode.mock.calls);
		if (actions.recreateQrCode.mock.calls.length !== 1) {
			throw new Error('recreateQrCode should have been called once');
		}

		// Check emitted events
		const emittedEvents = wrapper.emitted('update-qr');
		console.log('Emitted events:', emittedEvents);
		if (!emittedEvents) {
			throw new Error('update-qr event should have been emitted');
		}
		if (emittedEvents[0][0].secret !== 'newSecret' || emittedEvents[0][0].qrUrl !== 'newQrUrl') {
			throw new Error('Values in update-qr event are incorrect');
		}

		// Check local state
		if (wrapper.vm.localSecret !== 'newSecret' || wrapper.vm.localQrUrl !== 'newQrUrl') {
			throw new Error('QR code was not recreated correctly');
		}
*/
	})

	it('validates custom secret', () => {
		const wrapper = shallowMount(SetupConfirmation, {
			store,
			localVue,
			propsData: {
				secret: 'testSecret',
				qrUrl: 'testQrUrl',
				loading: false,
			},
		})
		wrapper.setData({ customSecret: 'INVALID!' })
		wrapper.vm.validateCustomSecret()
		if (wrapper.vm.customSecretWarning !== true) {
			throw new Error('customSecretWarning should be true')
		}
		wrapper.setData({ customSecret: 'VALID2' })
		wrapper.vm.validateCustomSecret()
		if (wrapper.vm.customSecretWarning !== false) {
			throw new Error('customSecretWarning should be false')
		}
	})
})
