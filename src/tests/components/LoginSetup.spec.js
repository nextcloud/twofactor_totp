/*
 * SPDX-FileCopyrightText: 2025 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

import { flushPromises, shallowMount } from '@vue/test-utils'
import LoginSetup from '../../components/LoginSetup.vue'
import { saveState } from '../../services/StateService.js'
import STATE from '../../state.js'

vi.mock('../../services/StateService.js', () => ({
	saveState: vi.fn(),
}))

describe('LoginSetup', () => {
	beforeEach(() => {
		vi.resetAllMocks()
	})

	it('shows a loading spinner on mount while the secret is being fetched', () => {
		saveState.mockResolvedValue({ secret: 'ABC', qrUrl: 'otpauth://...' })
		const wrapper = shallowMount(LoginSetup)

		expect(wrapper.vm.loading).to.be.true
	})

	it('requests a new TOTP secret on mount', async () => {
		saveState.mockResolvedValue({ secret: 'ABC', qrUrl: 'otpauth://...' })
		shallowMount(LoginSetup)
		await flushPromises()

		expect(saveState.mock.calls).to.have.length(1)
		expect(saveState.mock.calls[0][0]).to.deep.equal({ state: STATE.STATE_CREATED })
	})

	it('stores secret and qrUrl and hides spinner after mount', async () => {
		const secret = 'JBSWY3DPEHPK3PXP'
		const qrUrl = 'otpauth://totp/test?secret=' + secret
		saveState.mockResolvedValue({ secret, qrUrl })

		const wrapper = shallowMount(LoginSetup)
		await flushPromises()

		expect(wrapper.vm.secret).to.equal(secret)
		expect(wrapper.vm.qrUrl).to.equal(qrUrl)
		expect(wrapper.vm.loading).to.be.false
	})

	it('submits the form when confirmation succeeds', async () => {
		saveState
			.mockResolvedValueOnce({ secret: 'ABC', qrUrl: 'otpauth://...' })
			.mockResolvedValueOnce({ state: STATE.STATE_ENABLED })

		const wrapper = shallowMount(LoginSetup)
		await flushPromises()

		const submitMock = vi.fn()
		vi.spyOn(wrapper.vm.$refs.confirmForm, 'submit').mockImplementation(submitMock)

		wrapper.vm.confirmation = '123456'
		wrapper.vm.confirm()
		await flushPromises()

		expect(submitMock.mock.calls).to.have.length(1)
	})

	it('shows loading and does not submit when confirmation code is wrong', async () => {
		saveState
			.mockResolvedValueOnce({ secret: 'ABC', qrUrl: 'otpauth://...' })
			.mockResolvedValueOnce({ state: STATE.STATE_CREATED })

		const wrapper = shallowMount(LoginSetup)
		await flushPromises()

		const submitMock = vi.fn()
		vi.spyOn(wrapper.vm.$refs.confirmForm, 'submit').mockImplementation(submitMock)

		wrapper.vm.confirmation = '000000'
		wrapper.vm.confirm()
		await flushPromises()

		expect(submitMock.mock.calls).to.have.length(0)
		expect(wrapper.vm.loading).to.be.false
	})
})
