/*
 * SPDX-FileCopyrightText: 2025 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

import { shallowMount } from '@vue/test-utils'
import SetupConfirmation from '../../components/SetupConfirmation.vue'

const defaultProps = {
	secret: 'JBSWY3DPEHPK3PXP',
	qrUrl: 'otpauth://totp/test?secret=JBSWY3DPEHPK3PXP',
}

describe('SetupConfirmation', () => {
	it('renders the secret', () => {
		const wrapper = shallowMount(SetupConfirmation, { props: defaultProps })
		expect(wrapper.text()).to.include(defaultProps.secret)
	})

	it('emits update:confirmation and confirm when verify button is clicked', async () => {
		const wrapper = shallowMount(SetupConfirmation, { props: defaultProps })
		await wrapper.find('#totp-confirmation').setValue('123456')
		await wrapper.find('#totp-confirmation-submit').trigger('click')

		expect(wrapper.emitted()['update:confirmation'][0]).to.deep.equal(['123456'])
		expect(wrapper.emitted().confirm).to.have.length(1)
	})

	it('emits confirm when Enter key is pressed in the input', async () => {
		const wrapper = shallowMount(SetupConfirmation, { props: defaultProps })
		await wrapper.vm.onConfirmKeyDown({ key: 'Enter' })

		expect(wrapper.emitted().confirm).to.have.length(1)
	})

	it('does not emit confirm for other keys', async () => {
		const wrapper = shallowMount(SetupConfirmation, { props: defaultProps })
		await wrapper.vm.onConfirmKeyDown({ key: 'a' })

		expect(wrapper.emitted().confirm).to.be.undefined
	})

	it('disables input and button when loading is true', () => {
		const wrapper = shallowMount(SetupConfirmation, {
			props: { ...defaultProps, loading: true },
		})

		expect(wrapper.find('#totp-confirmation').element.disabled).to.be.true
		expect(wrapper.find('#totp-confirmation-submit').element.disabled).to.be.true
	})

	it('enables input and button when loading is false', () => {
		const wrapper = shallowMount(SetupConfirmation, {
			props: { ...defaultProps, loading: false },
		})

		expect(wrapper.find('#totp-confirmation').element.disabled).to.be.false
		expect(wrapper.find('#totp-confirmation-submit').element.disabled).to.be.false
	})

	it('initialises confirmationCode from the confirmation prop', () => {
		const wrapper = shallowMount(SetupConfirmation, {
			props: { ...defaultProps, confirmation: 'prefilled' },
		})

		expect(wrapper.vm.confirmationCode).to.equal('prefilled')
	})

	it('updates confirmationCode when the confirmation prop changes', async () => {
		const wrapper = shallowMount(SetupConfirmation, {
			props: { ...defaultProps, confirmation: '' },
		})

		await wrapper.setProps({ confirmation: '654321' })

		expect(wrapper.vm.confirmationCode).to.equal('654321')
	})
})
