<!--
  - SPDX-FileCopyrightText: 2019 Nextcloud GmbH and Nextcloud contributors
  - SPDX-License-Identifier: AGPL-3.0-or-later
-->

<template>
	<div class="setup-confirmation">
		<p class="setup-confirmation__twofactor-email">
			{{ t('twofactor_email', 'We have sent an email containing your authentication code to:') }} {{ email }}
		</p>
		<p>
			{{
				t(
					'twofactor_email',
					'Please enter your authentication code:'
				)
			}}
		</p>
		<input id="email-confirmation"
			v-model="confirmationCode"
			type="text"
			minlength="6"
			maxlength="10"
			autocomplete="one-time-code"
			inputmode="numeric"
			autocapitalize="off"
			:disabled="loading"
			:placeholder="t('twofactor_email', 'Authentication code')"
			@keydown="onConfirmKeyDown">
		<input id="email-confirmation-submit"
			type="button"
			:disabled="loading"
			:value="t('twofactor_email', 'Verify')"
			@click="confirm">
	</div>
</template>

<script>
export default {
	name: 'SetupConfirmation',
	props: {
		loading: {
			type: Boolean,
			default: false,
		},
		email: {
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
	&__twofactor-email {
		word-break: break-all;
	}
}
</style>
