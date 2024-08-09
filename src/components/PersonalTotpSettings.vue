<!--
  - @copyright 2018 Christoph Wurst <christoph@winzerhof-wurst.at>
  -
  - @author 2018 Christoph Wurst <christoph@winzerhof-wurst.at>
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
	<div id="twofactor-totp-settings">
		<template v-if="loading">
			<span class="icon-loading-small totp-loading" />
			<span> {{ t('twofactor_totp', 'Enable TOTP') }} </span>
		</template>
		<div v-else>
			<!-- Checkbox and Advanced Settings Button in the same row -->
			<div class="row">
				<div class="checkbox-container">
					<input id="totp-enabled"
						v-model="enabled"
						type="checkbox"
						class="checkbox"
						:disabled="loading"
						@change="toggleEnabled">
					<label for="totp-enabled">{{
						t('twofactor_totp', 'Enable TOTP')
					}}</label>
				</div>

				<!-- Advanced Settings Button -->
				<div v-if="enabled" class="advanced-settings-container">
					<button class="advanced-settings-btn" @click="toggleAdvancedSettings">
						{{ showAdvanced ? t('twofactor_totp', 'Hide Advanced Settings') : t('twofactor_totp', 'Advanced Settings') }}
					</button>
				</div>
			</div>

			<!-- Advanced Settings Section -->
			<div v-if="showAdvanced && enabled" class="advanced-settings">
				<p class="warning-message">
					{{ t('twofactor_totp', 'Warning: Not all TOTP apps support changing these settings or may not support their full range.') }}
				</p>
				<p class="instruction-message">
					{{ t('twofactor_totp', 'Changes made here must be reflected exactly in your TOTP app. Ensure that both settings match. Keep any setting in this form that cannot be edited or updated in your TOTP app unchanged here.') }}
				</p>

				<!-- Algorithm Select -->
				<div class="form-group">
					<label for="algorithm">{{
						t('twofactor_totp', 'Algorithm')
					}}</label>
					<select id="algorithm"
						v-model.number="algorithm"
						:disabled="loading || !enabled"
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
					<label for="digits">{{
						t('twofactor_totp', 'Digits (OTP token length)')
					}}</label>
					<select id="digits"
						v-model.number="digits"
						:disabled="loading || !enabled"
						@mouseleave="onMouseLeave">
						<option v-for="length in digitsOptions" :key="length" :value="length">
							{{ length }}
						</option>
					</select>
				</div>

				<!-- Period Select -->
				<div class="form-group">
					<label for="period">{{
						t('twofactor_totp', 'Period (OTP validity in seconds)')
					}}</label>
					<select id="period"
						v-model.number="period"
						:disabled="loading || !enabled"
						@mouseleave="onMouseLeave">
						<option v-for="seconds in periodOptions" :key="seconds" :value="seconds">
							{{ seconds }}
						</option>
					</select>
				</div>

				<!-- Save Button -->
				<button :disabled="!settingsChanged || loading"
					@click="updateSettings">
					{{ t('twofactor_totp', 'Save changes') }}
				</button>
			</div>
		</div>

		<SetupConfirmation v-if="secret"
			:secret="secret"
			:qr-url="qrUrl"
			:loading="loadingConfirmation"
			:confirmation.sync="confirmation"
			@confirm="enableTOTP"
			@updateQR="updateQR" />
	</div>
</template>

<script>
import { confirmPassword } from '@nextcloud/password-confirmation'
import '@nextcloud/password-confirmation/dist/style.css'

import Logger from '../logger.js'
import SetupConfirmation from './SetupConfirmation.vue'
import STATE from '../state.js'

export default {
	name: 'PersonalTotpSettings',
	components: {
		SetupConfirmation,
	},
	data() {
		return {
			loading: false,
			loadingConfirmation: false,
			enabled: this.$store.state.totpState === STATE.STATE_ENABLED,
			secret: undefined,
			qrUrl: '',
			confirmation: '',
			algorithm: null, // initially null to ensure it gets set when advanced settings are loaded
			digits: null, // initially null to ensure it gets set when advanced settings are loaded
			period: null, // initially null to ensure it gets set when advanced settings are loaded
			digitsOptions: [4, 5, 6, 7, 8, 9, 10], // options for digits
			periodOptions: [15, 20, 25, 30, 35, 40, 45, 50, 55, 60], // options for period
			settingsChanged: false, // track if settings have changed
			showAdvanced: false, // whether to show advanced settings
			initialSettings: {},
		}
	},
	computed: {
		state() {
			return this.$store.state.totpState
		},
	},
	watch: {
		algorithm() {
			this.checkIfSettingsChanged()
		},
		digits() {
			this.checkIfSettingsChanged()
		},
		period() {
			this.checkIfSettingsChanged()
		},
		enabled(newValue) {
			if (!newValue) {
				this.hideAdvancedSettings()
			}
		},
	},
	mounted() {
		this.storeInitialSettings()
	},

	methods: {
		toggleEnabled() {
			if (this.loading) {
				// Ignore event
				Logger.debug('still loading -> ignoring event')
				return
			}

			if (this.enabled) {
				this.createTOTP()
			} else {
				this.disableTOTP()
			}
		},

		createTOTP() {
			// Show loading spinner
			this.loading = true

			Logger.debug('starting setup')

			return confirmPassword()
				.then(() => this.$store.dispatch('enable'))
				.then(({ secret, qrUrl }) => {
					this.secret = secret
					this.qrUrl = qrUrl
					// If the state could be changed, keep showing the loading
					// spinner until the user has finished the registration
					this.loading = this.$store.state.totpState === STATE.STATE_CREATED
				})
				.catch((e) => {
					OC.Notification.showTemporary(
						this.t('twofactor_totp', 'Could not enable TOTP'),
					)
					Logger.error('Could not enable TOTP', e)

					// Restore on error
					this.loading = false
					this.enabled = false
				})
		},

		enableTOTP() {
			// Show loading spinner and disable input elements
			this.loading = true
			this.loadingConfirmation = true

			Logger.debug('starting enable')

			return confirmPassword()
				.then(() => this.$store.dispatch('confirm', this.confirmation))
				.then(() => {
					if (this.$store.state.totpState === STATE.STATE_ENABLED) {
						// Success
						this.loading = false
						this.enabled = true
						this.qrUrl = ''
						this.secret = undefined
					} else {
						OC.Notification.showTemporary(
							this.t('twofactor_totp', 'Could not verify your key. Please try again'),
						)
					}

					this.confirmation = ''
					this.loadingConfirmation = false
				})
				.catch(Logger.error)
		},

		disableTOTP() {
			// Show loading spinner
			this.loading = true

			Logger.debug('starting disable')

			return confirmPassword()
				.then(() => this.$store.dispatch('disable'))
				.then(() => {
					this.enabled = false
					this.hideAdvancedSettings()
				})
				.catch(Logger.error.bind(this))
				.then(() => (this.loading = false))
		},

		fetchSettings() {
			this.$store.dispatch('getSettings')
				.then(() => {
					this.algorithm = this.$store.state.algorithm
					this.digits = this.$store.state.digits
					this.period = this.$store.state.period
					this.storeInitialSettings()
				})
				.catch((e) => {
					Logger.error('Could not fetch settings', e)
				})
		},

		updateSettings() {
			if (this.enabled) {
				// Show loading spinner
				this.loading = true

				Logger.debug('Starting settings update')

				// Confirm password before updating settings
				return confirmPassword()
					.then(() => this.$store.dispatch('updateSettings', {
						algorithm: this.algorithm,
						digits: this.digits,
						period: this.period,
					}))
					.then(() => {
						this.loading = false
						this.settingsChanged = false
						OC.Notification.showTemporary(
							this.t('twofactor_totp', 'Settings updated successfully'),
						)
					})
					.catch((e) => {
						OC.Notification.showTemporary(
							this.t('twofactor_totp', 'Could not update settings'),
						)
						Logger.error('Could not update settings', e)
						this.loading = false
					})
			}
		},

		onMouseLeave() {
			this.checkIfSettingsChanged()
			event.target.blur()
		},

		checkIfSettingsChanged() {
			this.settingsChanged
				= this.algorithm !== this.initialSettings.algorithm
				|| this.digits !== this.initialSettings.digits
				|| this.period !== this.initialSettings.period
		},

		toggleAdvancedSettings() {
			this.showAdvanced = !this.showAdvanced
			if (this.showAdvanced && !this.initialSettings.algorithm) {
				this.fetchSettings()
			}
		},

		hideAdvancedSettings() {
			this.showAdvanced = false
		},

		storeInitialSettings() {
			this.initialSettings = {
				algorithm: this.algorithm,
				digits: this.digits,
				period: this.period,
			}
		},

		updateQR({ secret, qrUrl }) {
			this.secret = secret
			this.qrUrl = qrUrl
		},
	},
}
</script>

<style scoped>
.totp-loading {
	display: inline-block;
	vertical-align: sub;
	margin-left: -2px;
	margin-right: 4px;
}

.row {
	display: flex;
	align-items: center;
	justify-content: flex-start;
}

.checkbox-container {
	display: flex;
	align-items: center;
}

.advanced-settings-container {
	margin-left: 80px;
}

.advanced-settings-btn {
	padding: 5px 10px;
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
</style>
