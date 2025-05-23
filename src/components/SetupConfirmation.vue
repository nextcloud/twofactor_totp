<!--
  - SPDX-FileCopyrightText: 2019 Nextcloud GmbH and Nextcloud contributors
  - SPDX-License-Identifier: AGPL-3.0-or-later
-->

<template>
	<div class="setup-confirmation">
		<p class="setup-confirmation__secret">
			{{ t('twofactor_totp', 'Your new TOTP secret is:') }} {{ secret }}
		</p>
		<p>
			{{
				t(
					'twofactor_totp',
					'For quick setup, scan this QR code with your TOTP app:'
				)
			}}
		</p>
		<QR :value="qrUrl" :options="{ width: 150 }" />
		<p>
			{{
				t(
					'twofactor_totp',
					'After you configured your app, enter a test code below to ensure everything works correctly:'
				)
			}}
		</p>
		<input id="totp-confirmation"
			v-model="confirmationCode"
			type="text"
			minlength="6"
			maxlength="10"
			autocomplete="one-time-code"
			inputmode="numeric"
			autocapitalize="off"
			:disabled="loading"
			:placeholder="t('twofactor_totp', 'Authentication code')"
			@keydown="onConfirmKeyDown">
		<input id="totp-confirmation-submit"
			type="button"
			:disabled="loading"
			:value="t('twofactor_totp', 'Verify')"
			@click="confirm">
	</div>
</template>

<script>
import QR from '@chenfengyuan/vue-qrcode'

export default {
	name: 'SetupConfirmation',
	components: {
		QR,
	},
	props: {
		loading: {
			type: Boolean,
			default: false,
		},
		secret: {
			type: String,
			required: true,
		},
		qrUrl: {
			type: String,
			required: true,
		},
		confirmation: {
			type: String,
			default: '',
		},
	},
	data() {
		return {
			confirmationCode: this.confirmation,
		}
	},
	watch: {
		confirmation(newVal) {
			this.confirmationCode = newVal
		},
	},
	methods: {
		confirm() {
			this.$emit('update:confirmation', this.confirmationCode)
			this.$emit('confirm')
		},
		onConfirmKeyDown(e) {
			if (e.which === 13) {
				this.confirm()
			}
		},
	},
}
</script>

<style lang="scss" scoped>
.setup-confirmation {
	&__secret {
		word-break: break-all;
	}
}
</style>
