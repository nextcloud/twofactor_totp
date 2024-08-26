<!--
  - @copyright 2019 Christoph Wurst <christoph@winzerhof-wurst.at>
  -
  - @author 2019 Christoph Wurst <christoph@winzerhof-wurst.at>
  - @author 2024 [ernolf] Raphael Gradenwitz <raphael.gradenwitz@googlemail.com>
  -
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
	<div :class="['setup-confirmation', { centered: isCentered }]">
		<p class="setup-confirmation__secret">
			{{ t('twofactor_totp', 'Your new TOTP secret is:') }} {{ localSecret }}
		</p>

		<!-- Advanced Settings Button -->
		<div :class="['advanced-settings-container', { centered: isCentered }]">
			<button class="advanced-settings-btn" @click="toggleAdvancedSettings">
				{{ showAdvanced ? t('twofactor_totp', 'Hide advanced settings') : t('twofactor_totp', 'Advanced settings') }}
			</button>
		</div>

		<!-- Advanced Settings Section -->
		<div v-if="showAdvanced" :class="['advanced-settings', { centered: isCentered }]">
			<p class="warning-message">
				{{ t('twofactor_totp', 'Warning: Not all TOTP devices or smartphone apps support the full range of these advanced settings.') }}
			</p>
			<p class="instruction-message">
				{{ t('twofactor_totp', 'If your app does not support a setting, the QR code might not be accepted, a warning message may be displayed, or the OTP will be incorrect, preventing activation. Adjust settings until your device/app supports the configuration, or simply scan the pre-generated QR code created with the default settings.') }}
			</p>

			<!-- Custom Secret Input -->
			<div :class="['form-group', { centered: isCentered }]">
				<label :class="{ centered: isCentered }"
					for="custom-secret"
					@mouseleave="onMouseLeave">
					{{ t('twofactor_totp', 'Secret') }}
				</label>
				<input id="custom-secret"
					v-model="customSecret"
					type="text"
					:disabled="loading"
					class="custom-secret-input"
					@input="validateCustomSecret">
			</div>
			<p v-if="customSecretWarning" class="error-message">
				{{ customSecretWarning }}
			</p>

			<!-- Settings Row (Algorithm, Digits, Period) -->
			<div class="form-row">
				<!-- Algorithm Select -->
				<div :class="['form-group', { centered: isCentered }]">
					<label :class="{ centered: isCentered }"
						for="algorithm"
						@mouseleave="onMouseLeave">
						{{ t('twofactor_totp', 'Algorithm') }}
					</label>
					<select id="algorithm"
						v-model.number="algorithm"
						:disabled="loading"
						@mouseleave="onMouseLeave">
						<option :value="1">
							SHA1
						</option>
						<option :value="2">
							SHA256
						</option>
						<option :value="3">
							SHA512
						</option>
					</select>
				</div>

				<!-- Digits Select -->
				<div :class="['form-group', { centered: isCentered }]">
					<label :class="{ centered: isCentered }"
						for="digits"
						:title="t('twofactor_totp', 'OTP token length')"
						@mouseleave="onMouseLeave">
						{{ t('twofactor_totp', 'Digits') }}
					</label>
					<select id="digits"
						v-model.number="digits"
						:disabled="loading"
						:title="t('twofactor_totp', 'OTP token length')"
						@mouseleave="onMouseLeave">
						<option v-for="length in digitsOptions" :key="length" :value="length">
							{{ length }}
						</option>
					</select>
				</div>

				<!-- Period Select -->
				<div :class="['form-group', { centered: isCentered }]">
					<label :class="{ centered: isCentered }"
						for="period"
						:title="t('twofactor_totp', 'OTP validity in seconds')"
						@mouseleave="onMouseLeave">
						{{ t('twofactor_totp', 'Period') }}
					</label>
					<select id="period"
						v-model.number="period"
						:disabled="loading"
						:title="t('twofactor_totp', 'OTP validity in seconds')"
						@mouseleave="onMouseLeave">
						<option v-for="seconds in periodOptions" :key="seconds" :value="seconds">
							{{ seconds }}
						</option>
					</select>
				</div>
			</div>

			<!-- Recreate QR Code Button -->
			<button :disabled="!settingsChanged || loading || customSecretWarning" @click="recreateQRCode">
				{{ t('twofactor_totp', 'Apply custom settings and recreate QR code') }}
			</button>
		</div>

		<p>{{ t('twofactor_totp', 'For quick setup, scan this QR code with your TOTP app:') }}</p>
		<QR :value="qrUrl" :options="{ width: 150 }" />
		<p>{{ t('twofactor_totp', 'After you configured your app, enter a test code below to ensure everything works correctly:') }}</p>
		<div id="['totp-confirmation-container', { centered: isCentered }]">
			<input id="totp-confirmation"
				ref="confirmationInput"
				v-model="confirmationCode"
				type="tel"
				minlength="4"
				maxlength="10"
				autocomplete="off"
				autocapitalize="off"
				:disabled="loading"
				:placeholder="t('twofactor_totp', 'Code')"
				@keydown="onConfirmKeyDown">
			<button id="totp-confirmation-submit"
				:disabled="isSubmitDisabled"
				@click="confirm">
				{{ t('twofactor_totp', 'Verify') }}
			</button>
		</div>
	</div>
</template>

<script>
import QR from '@chenfengyuan/vue-qrcode'
import Logger from '../logger.js'

export default {
	name: 'SetupConfirmation',
	components: {
		QR,
	},
	props: {
		isCentered: {
			type: Boolean,
			default: false,
		},
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
			localQrUrl: this.qrUrl,
			confirmationCode: this.confirmation,
			localSecret: this.secret,
			customSecret: this.secret,
			algorithm: null,
			digits: null,
			period: null,
			digitsOptions: [4, 5, 6, 7, 8, 9, 10],
			periodOptions: [15, 20, 25, 30, 35, 40, 45, 50, 55, 60],
			settingsChanged: false,
			showAdvanced: false,
			customSecretWarning: false,
			initialSettings: {},
		}
	},
	computed: {
		isSubmitDisabled() {
			const code = this.confirmationCode
			const requiredLength = this.digits
			return !code || code.length !== requiredLength || /\D/.test(code) || this.loading
		},
	},
	watch: {
		confirmation(newVal) {
			this.confirmationCode = newVal
		},
		customSecret() {
			this.checkIfSettingsChanged()
		},
		algorithm() {
			this.checkIfSettingsChanged()
		},
		digits() {
			this.checkIfSettingsChanged()
		},
		period() {
			this.checkIfSettingsChanged()
		},
	},
	mounted() {
		// Set focus to the confirmation input field when the component is mounted
		this.$nextTick(() => {
			this.$refs.confirmationInput.focus()
		})

		// Store the initial settings
		this.storeInitialSettings()

		// Fetch settings when component is mounted
		this.fetchSettings()
	},
	methods: {
		confirm() {
			this.$emit('update:confirmation', this.confirmationCode)
			this.$emit('confirm', {
				algorithm: this.algorithm,
				digits: this.digits,
				period: this.period,
			})
		},
		onConfirmKeyDown(e) {
			// Exit early if the submit button is disabled
			if (this.isSubmitDisabled) {
				return
			}
			// Check if the Enter key (key code 13) was pressed
			if (e.which === 13) {
				this.confirm()
			}
		},
		fetchSettings() {
			this.$store.dispatch('getSettings')
				.then(() => {
					this.algorithm = this.$store.state.algorithm
					this.digits = this.$store.state.digits
					this.period = this.$store.state.period
					this.customSecret = this.secret
					this.storeInitialSettings()
				})
				.catch((e) => {
					Logger.error('Could not fetch settings', e)
				})
		},
		onMouseLeave() {
			this.checkIfSettingsChanged()
			event.target.blur()
		},
		toggleAdvancedSettings() {
			if (this.showAdvanced) {
				this.showAdvanced = false
				this.resetSettings()
			} else {
				this.showAdvanced = true
			}
			// Set focus to the confirmation input field when QRCode is recreated
			this.$nextTick(() => {
				this.$refs.confirmationInput.focus()
			})
		},
		resetSettings() {
			this.algorithm = this.initialSettings.algorithm
			this.digits = this.initialSettings.digits
			this.period = this.initialSettings.period
			this.customSecret = this.initialSettings.customSecret
			this.settingsChanged = false
		},

		recreateQRCode() {
			// console.log('recreateQRCode called')
			this.$store.dispatch('updateSettings', {
				secret: this.customSecret,
				algorithm: this.algorithm,
				digits: this.digits,
				period: this.period,
			}).then(() => {
				// console.log('updateSettings completed')
				return this.$store.dispatch('recreateQrCode', { secret: this.customSecret })
			}).then(({ secret, qrUrl }) => {
				// console.log('recreateQrCode completed', { secret, qrUrl })
				this.localSecret = secret
				this.localQrUrl = qrUrl
				this.settingsChanged = false
				this.$emit('update-qr', { secret, qrUrl })
				// Store the new settings as initialSettings
				this.storeInitialSettings()
				// Set focus to the confirmation input field when QRCode is recreated
				this.$nextTick(() => {
					this.$refs.confirmationInput.focus()
				})
			}).catch((e) => {
				Logger.error('Could not recreate QR code', e)
			})
		},
		validateCustomSecret() {
			const base32Regex = /^[A-Z2-7]*$/
			const minLength = 26
			const maxLength = 128
			let warningMessages = []
			if (this.customSecret.length < minLength) {
				warningMessages.push(this.t('twofactor_totp', 'The secret must have at least 26 characters.'))
			}
			if (this.customSecret.length > maxLength) {
				warningMessages.push(this.t('twofactor_totp', 'The secret must not exceed 128 characters.'))
			}
			if (!base32Regex.test(this.customSecret)) {
				warningMessages.push(this.t('twofactor_totp', 'Only A-Z and 2-7 are allowed.'))
			}
			// Set the warning message based on the validation
			if (warningMessages.length > 0) {
				this.customSecretWarning = warningMessages.join(' ');
			} else {
				this.customSecretWarning = false;
			}
			// Check if the settings have changed
			this.checkIfSettingsChanged()
		},
		checkIfSettingsChanged() {
			this.settingsChanged
				= this.customSecret !== this.initialSettings.customSecret
				|| this.algorithm !== this.initialSettings.algorithm
				|| this.digits !== this.initialSettings.digits
				|| this.period !== this.initialSettings.period
		},
		storeInitialSettings() {
			this.initialSettings = {
				customSecret: this.customSecret,
				algorithm: this.algorithm,
				digits: this.digits,
				period: this.period,
			}
		},
	},
}
</script>

<style lang="scss" scoped>
.setup-confirmation {
	/* Centered only if isCentered is true */
	&.centered {
		display: flex;
		flex-direction: column;
		align-items: center;
		text-align: center; /* Centered short text as well */
	}

	&__secret {
		word-break: break-all;
	}

	.advanced-settings-container {
		&.centered {
			width: 100%;
			text-align: center; /* centers the button */
			margin-top: 10px;
		}
	}

	.advanced-settings-btn {
		padding: 10px 20px;
		cursor: pointer;
		font-size: 1em;
	}

	.advanced-settings {
		width: 100%;
		margin-top: 20px;
		display: flex;
		flex-direction: column;
		align-items: flex-start;
		&.centered {
			justify-content: center;
			align-items: center;
			text-align: center;
		}
		.warning-message {
			color: var(--color-warning);
			font-weight: bold;
		}
		.instruction-message {
			color: var(--color-info);
			margin-bottom: 10px;
		}
		.error-message {
			color: var(--color-error);
			margin-bottom: 10px;
		}
		.form-group {
			display: flex;
			align-items: center;
			flex-direction: row;
			margin: 5px 0;
			width: 100%;
			justify-content: center;
			text-align: center;
			label {
				margin-right: 10px;
				white-space: nowrap;
				flex: 0 1 auto; /* Flexible label */
				text-align: left;
				cursor: default; /* Ensure default cursor for labels */
			}
			.custom-secret-input {
				flex-grow: 1;
				width: 100%;
			}
			select {
				width: auto;
				padding: 8px;
				border: 1px solid #ccc;
				box-sizing: border-box;
				cursor: pointer; /* Shows pointer cursor for select fields */
			}
		}
		.form-row {
			display: flex;
			.form-group {
				margin-right: 10px; // Add spacing between elements
				&:last-child {
					margin-right: 0; // Remove right margin from last item
				}
			}
		}
	}

	/* Authentication Code and Verify Button side by side */
	#totp-confirmation-container {
		margin: 5px 0;

		input#totp-confirmation {
			width: auto;
			max-width: 100px;
			padding: 8px;
			border: 1px solid #ccc;
			box-sizing: border-box;
			margin-right: 10px; /* Distance to Verify-Button */
		}

		button#totp-confirmation-submit {
			padding: 8px 20px;
			cursor: pointer;
			font-size: 1em;
		}

		button#totp-confirmation-submit:disabled {
			background-color: #ccc;
			cursor: not-allowed;
		}

		&.centered {
			justify-content: center;
		}
	}
}
</style>
