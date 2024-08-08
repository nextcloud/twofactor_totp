/**
 * @copyright 2018 Christoph Wurst <christoph@winzerhof-wurst.at>
 *
 * @author 2018 Christoph Wurst <christoph@winzerhof-wurst.at>
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

import PersonalTotpSettings from '../../components/PersonalTotpSettings.vue'

const localVue = createLocalVue()

localVue.use(Vuex)

// Mock OC.Notification
global.OC = {
	Notification: {
		showTemporary: jest.fn(),
	},
}

describe('PersonalTotpSettings', () => {
	let actions
	let store

	beforeEach(() => {
		actions = {
			enable: jest.fn(),
			confirm: jest.fn(),
			disable: jest.fn(),
			getSettings: jest.fn().mockResolvedValue({ algorithm: 1, digits: 6, period: 30 }),
			updateSettings: jest.fn().mockResolvedValue()
		}
		store = new Vuex.Store({
			state: {
				totpState: undefined,
				algorithm: 1,
				digits: 6,
				period: 30,
			},
			actions,
		})
	})

	it('does not load on start', () => {
		const settings = shallowMount(PersonalTotpSettings, {
			store,
			localVue,
		})

		expect(settings.vm.loading).toBe(false)
	})

	it('toggles advanced settings', async () => {
		const settings = shallowMount(PersonalTotpSettings, {
			store,
			localVue,
		})

		expect(settings.vm.showAdvanced).toBe(false)
		settings.vm.toggleAdvancedSettings()
		await settings.vm.$nextTick()
		expect(settings.vm.showAdvanced).toBe(true)
		settings.vm.toggleAdvancedSettings()
		await settings.vm.$nextTick()
		expect(settings.vm.showAdvanced).toBe(false)
	})

	it('enables TOTP', async () => {
		const settings = shallowMount(PersonalTotpSettings, {
			store,
			localVue,
		})

		settings.setData({ enabled: false })
		await settings.vm.toggleEnabled()

		expect(actions.enable).toHaveBeenCalled()
		expect(settings.vm.loading).toBe(true)
	})

	it('disables TOTP', async () => {
		const settings = shallowMount(PersonalTotpSettings, {
			store,
			localVue,
		})

		settings.setData({ enabled: true })
		await settings.vm.toggleEnabled()

		expect(actions.disable).toHaveBeenCalled()
		expect(settings.vm.enabled).toBe(false)
	})

	it('fetches settings', async () => {
		const settings = shallowMount(PersonalTotpSettings, {
			store,
			localVue,
		})

		await settings.vm.fetchSettings()

		expect(actions.getSettings).toHaveBeenCalled()
		expect(settings.vm.algorithm).toBe(1)
		expect(settings.vm.digits).toBe(6)
		expect(settings.vm.period).toBe(30)
	})

	it('saves changes', async () => {
		const settings = shallowMount(PersonalTotpSettings, {
			store,
			localVue,
		})

		settings.setData({ enabled: true, algorithm: 2, digits: 8, period: 45 })
		settings.vm.settingsChanged = true
		await settings.vm.updateSettings()

		expect(actions.updateSettings).toHaveBeenCalledWith(expect.anything(), {
			algorithm: 2,
			digits: 8,
			period: 45,
		})
		expect(settings.vm.loading).toBe(false)
		expect(settings.vm.settingsChanged).toBe(false)
	})

	it('confirms TOTP', async () => {
		const settings = shallowMount(PersonalTotpSettings, {
			store,
			localVue,
		})

		settings.setData({ enabled: true, confirmation: '123456' })
		await settings.vm.enableTOTP()

		expect(actions.confirm).toHaveBeenCalledWith(expect.anything(), '123456')
		expect(settings.vm.loading).toBe(false)
		expect(settings.vm.loadingConfirmation).toBe(false)
		expect(settings.vm.enabled).toBe(true)
	})

	it('checks if settings have changed', async () => {
		const settings = shallowMount(PersonalTotpSettings, {
			store,
			localVue,
		})

		settings.setData({ algorithm: 1, digits: 6, period: 30 })
		settings.vm.storeInitialSettings()

		settings.setData({ algorithm: 2 })
		settings.vm.checkIfSettingsChanged()
		expect(settings.vm.settingsChanged).toBe(true)

		settings.setData({ algorithm: 1 })
		settings.vm.checkIfSettingsChanged()
		expect(settings.vm.settingsChanged).toBe(false)
	})

	it('hides advanced settings when disabled', async () => {
		const settings = shallowMount(PersonalTotpSettings, {
			store,
			localVue,
		})

		settings.setData({ enabled: true, showAdvanced: true })
		settings.vm.enabled = false
		await settings.vm.$nextTick()

		expect(settings.vm.showAdvanced).toBe(false)
	})
})
