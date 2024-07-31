<!--
  - @copyright 2018 Christoph Wurst <christoph@winzerhof-wurst.at>
  -
  - @author 2018 Christoph Wurst <christoph@winzerhof-wurst.at>
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
			<div v-if="showAdvanced" class="advanced-settings">
				<p class="warning-message">
					{{ t('twofactor_totp', 'Warning: Changing these settings may break TOTP functionality. Proceed with caution.') }}
				</p>
				<p class="instruction-message">
					{{ t('twofactor_totp', 'Changes made here must be set exactly the same in your TOTP app. Only a few TOTP apps (like Aegis) support these custom settings. If changing one of these values is not possible in your TOTP app, then that value must be left untouched here. Otherwise, TOTP functionality will be broken until you change it back to the default of 6 for "Digits" and SHA1 for "Hash algorithm."') }}
				</p>

				<!-- Token Length Select -->
				<label for="token-length">{{
					t('twofactor_totp', 'Digits (OTP token length)')
				}}</label>
				<select id="token-length"
					v-model="tokenLength"
					:disabled="loading || !enabled"
					@change="onSettingsChange">
					<option v-for="length in tokenLengthOptions" :key="length" :value="length">
						{{ length }}
					</option>
				</select>

				<!-- Hash Algorithm Select -->
				<label for="hash-algorithm">{{
					t('twofactor_totp', 'Hash algorithm')
				}}</label>
				<select id="hash-algorithm"
					v-model="hashAlgorithm"
					:disabled="loading || !enabled"
					@change="onSettingsChange">
					<option value="1">
						SHA1
					</option>
					<option value="2">
						SHA256
					</option>
					<option value="3">
						SHA512
					</option>
				</select>

				<!-- Save Button -->
				<button :disabled="!settingsChanged || loading"
					@click="updateSettings">
					{{ t('twofactor_totp', 'Save') }}
				</button>
			</div>
		</div>

		<SetupConfirmation v-if="secret"
			:secret="secret"
			:qr-url="qrUrl"
			:loading="loadingConfirmation"
			:confirmation.sync="confirmation"
			@confirm="enableTOTP" />
	</div>
</template>

<script>
import { confirmPassword } from '@nextcloud/password-confirmation'
import '@nextcloud/password-confirmation/dist/style.css'

import Logger from '../logger.js'
import SetupConfirmation from './SetupConfirmation.vue'
import state from '../state.js'

export default {
	name: 'PersonalTotpSettings',
	components: {
		SetupConfirmation,
	},
	data() {
		return {
			loading: false,
			loadingConfirmation: false,
			enabled: this.$store.state.totpState === state.STATE_ENABLED,
			secret: undefined,
			qrUrl: '',
			confirmation: '',
			tokenLength: this.$store.state.tokenLength, // default value from store
			hashAlgorithm: this.$store.state.hashAlgorithm, // default value from store
			tokenLengthOptions: [4, 5, 6, 7, 8, 9, 10], // options for token length
			settingsChanged: false, // track if settings have changed
			showAdvanced: false, // whether to show advanced settings
		}
	},
	computed: {
		state() {
			return this.$store.state.totpState
		},
	},

	created() {
		this.fetchSettings()
	},
	methods: {
		toggleEnabled() {
			if (this.loading) {
				// Ignore event
				Logger.debug('still loading -> ignoring event')
				return
			}

			if (this.enabled) {
				return this.createTOTP()
			} else {
				return this.disableTOTP()
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
					this.loading = this.$store.state.totpState === state.STATE_CREATED
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
					if (this.$store.state.totpState === state.STATE_ENABLED) {
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
				.then(() => (this.enabled = false))
				.catch(Logger.error.bind(this))
				.then(() => (this.loading = false))
		},

		fetchSettings() {
			this.$store.dispatch('getSettings')
				.then(() => {
					this.tokenLength = this.$store.state.tokenLength
					this.hashAlgorithm = this.$store.state.hashAlgorithm
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
						tokenLength: this.tokenLength,
						hashAlgorithm: this.hashAlgorithm,
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

		onSettingsChange() {
			this.settingsChanged = true
		},

		toggleAdvancedSettings() {
			this.showAdvanced = !this.showAdvanced
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
	color: red;
	font-weight: bold;
}

.instruction-message {
	margin-top: 10px;
	margin-bottom: 10px;
}
</style>
