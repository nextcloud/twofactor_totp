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
import QR from '@chenfengyuan/vue-qrcode'

const localVue = createLocalVue()
localVue.use(Vuex)

describe('SetupConfirmation', () => {
	let store
	let actions

	beforeEach(() => {
		actions = {
			getSettings: jest.fn().mockResolvedValue({
				algorithm: 1,
				digits: 6,
				period: 30,
			}),
			updateSettings: jest.fn().mockResolvedValue(),
			recreateQrCode: jest.fn().mockResolvedValue({
				secret: 'newSecret',
				qrUrl: 'newQrUrl',
			}),
		}
		store = new Vuex.Store({
			state: {
				algorithm: 1,
				digits: 6,
				period: 30,
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
		expect(wrapper.element).toMatchSnapshot()
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
		expect(wrapper.vm.showAdvanced).toBe(false)
		wrapper.vm.toggleAdvancedSettings()
		await wrapper.vm.$nextTick()
		expect(wrapper.vm.showAdvanced).toBe(true)
		wrapper.vm.toggleAdvancedSettings()
		await wrapper.vm.$nextTick()
		expect(wrapper.vm.showAdvanced).toBe(false)
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
		expect(actions.getSettings).toHaveBeenCalled()
		expect(wrapper.vm.algorithm).toBe(1)
		expect(wrapper.vm.digits).toBe(6)
		expect(wrapper.vm.period).toBe(30)
	})

	it('confirms TOTP', async () => {
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
		expect(wrapper.emitted().confirm).toBeTruthy()
		expect(wrapper.emitted().confirm[0]).toEqual([{
			algorithm: wrapper.vm.algorithm,
			digits: wrapper.vm.digits,
			period: wrapper.vm.period,
		}])
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
			customSecret: 'newSecret',
			algorithm: 2,
			digits: 8,
			period: 45,
		})
		await wrapper.vm.recreateQRCode()
		expect(actions.updateSettings).toHaveBeenCalledWith(expect.anything(), {
			customSecret: 'newSecret',
			algorithm: 2,
			digits: 8,
			period: 45,
		})
		expect(actions.recreateQrCode).toHaveBeenCalledWith(expect.anything(), { customSecret: 'newSecret' })
		expect(wrapper.vm.secret).toBe('newSecret')
		expect(wrapper.vm.qrUrl).toBe('newQrUrl')
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
		expect(wrapper.vm.customSecretWarning).toBe(true)
		wrapper.setData({ customSecret: 'VALID2' })
		wrapper.vm.validateCustomSecret()
		expect(wrapper.vm.customSecretWarning).toBe(false)
	})
})
