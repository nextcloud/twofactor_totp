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
	<div class="setup-confirmation">
		<p class="setup-confirmation__secret">
			{{ t('twofactor_totp', 'Your new TOTP secret is:') }} {{ localSecret }}
		</p>

		<!-- Advanced Settings Button -->
		<div class="advanced-settings-container">
			<button class="advanced-settings-btn" @click="toggleAdvancedSettings">
				{{ showAdvanced ? t('twofactor_totp', 'Hide Advanced Settings') : t('twofactor_totp', 'Advanced Settings') }}
			</button>
		</div>

		<!-- Advanced Settings Section -->
		<div v-if="showAdvanced" class="advanced-settings">
			<p class="warning-message">
				{{ t('twofactor_totp', 'Warning: Not all TOTP apps support changing these settings or may not support their full range.') }}
			</p>
			<p class="instruction-message">
				{{ t('twofactor_totp', 'If your app does not support a setting, the QR code might not be accepted, a warning message may be displayed, or the OTP will be incorrect, preventing activation. Adjust settings until your TOTP app supports the configuration, or simply scan the pre-generated QR code with the default settings.') }}
			</p>

			<!-- Custom Secret Input -->
			<div class="form-group">
				<label for="custom-secret">{{ t('twofactor_totp', 'Secret') }}</label>
				<input id="custom-secret"
					v-model="customSecret"
					type="text"
					:disabled="loading"
					class="custom-secret-input"
					@input="validateCustomSecret">
			</div>
			<p v-if="customSecretWarning" class="error-message">
				{{ t('twofactor_totp', 'Invalid characters detected. Only A-Z and 2-7 are allowed.') }}
			</p>

			<!-- Algorithm Select -->
			<div class="form-group">
				<label for="algorithm">{{ t('twofactor_totp', 'Algorithm') }}</label>
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
			<div class="form-group">
				<label for="digits">{{ t('twofactor_totp', 'Digits (OTP token length)') }}</label>
				<select id="digits"
					v-model.number="digits"
					:disabled="loading"
					@mouseleave="onMouseLeave">
					<option v-for="length in digitsOptions" :key="length" :value="length">
						{{ length }}
					</option>
				</select>
			</div>

			<!-- Period Select -->
			<div class="form-group">
				<label for="period">{{ t('twofactor_totp', 'Period (OTP validity in seconds)') }}</label>
				<select id="period"
					v-model.number="period"
					:disabled="loading"
					@mouseleave="onMouseLeave">
					<option v-for="seconds in periodOptions" :key="seconds" :value="seconds">
						{{ seconds }}
					</option>
				</select>
			</div>

			<!-- Recreate QR Code Button -->
			<button :disabled="!settingsChanged || loading" @click="recreateQRCode">
				{{ t('twofactor_totp', 'Recreate QR-Code with custom settings') }}
			</button>
		</div>

		<p>{{ t('twofactor_totp', 'For quick setup, scan this QR code with your TOTP app:') }}</p>
		<QR :value="qrUrl" :options="{ width: 150 }" />
		<p>{{ t('twofactor_totp', 'After you configured your app, enter a test code below to ensure everything works correctly:') }}</p>
		<input id="totp-confirmation"
			ref="confirmationInput"
			v-model="confirmationCode"
			type="tel"
			minlength="4"
			maxlength="10"
			autocomplete="off"
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
import Logger from '../logger.js'

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
				this.fetchSettings()
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
			this.$store.dispatch('updateSettings', {
				secret: this.customSecret,
				algorithm: this.algorithm,
				digits: this.digits,
				period: this.period,
			}).then(() => {
				return this.$store.dispatch('recreateQrCode', { secret: this.customSecret })
			}).then(({ secret, qrUrl }) => {
				this.localSecret = secret
				this.localQrUrl = qrUrl
				this.settingsChanged = false
				this.$emit('updateQr', { secret, qrUrl })
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
			if (!base32Regex.test(this.customSecret)) {
				this.customSecretWarning = true
			} else {
				this.customSecretWarning = false
			}
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
	&__secret {
		word-break: break-all;
	}

	.advanced-settings-container {
		margin-top: 10px;
	}

	.advanced-settings {
		margin-top: 20px;
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
		margin-bottom: 10px;
		color: var(--color-error);
	}

	.form-group {
		display: flex;
		align-items: center;
		margin: 5px 0;

		label {
			margin-right: 10px;
			white-space: nowrap;
		}

		input, select {
			width: auto;
		}

		.custom-secret-input {
			width: 100%;
		}
	}

	button {
		margin-top: 10px;
		display: block;
	}
}
</style>
