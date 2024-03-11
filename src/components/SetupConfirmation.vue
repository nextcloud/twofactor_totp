<!--
  - @copyright 2019 Christoph Wurst <christoph@winzerhof-wurst.at>
  -
  - @author 2019 Christoph Wurst <christoph@winzerhof-wurst.at>
   - @author 2024 Nico Kluge <nico.kluge@klugecoded.com>

  - @license GNU AGPL version 3 or any later version
  -
  - This program is free software: you can redistribute it and/or modify
  - it under the terms of the GNU Affero General Public License as
  - published by the Free Software Foundation, either version 3 of the
  - License, or (at your option) any later version.
  -
  - This program is distributed in the hope that it will be useful,
  - but WITHOUT ANY WARRANTY; without even the implied warranty of
  - MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  - GNU Affero General Public License for more details.
  -
  - You should have received a copy of the GNU Affero General Public License
  - along with this program.  If not, see <http://www.gnu.org/licenses/>.
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
			type="tel"
			minlength="6"
			maxlength="10"
			autocomplete="off"
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
