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

		<SetupConfirmation v-if="secret"
			:secret="secret"
			:qr-url="qrUrl"
			:loading="loadingConfirmation"
			:confirmation.sync="confirmation"
			@confirm="enableTOTP" />
	</div>
</template>

<script>
import confirmPassword from '@nextcloud/password-confirmation'

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
		}
	},
	computed: {
		state() {
			return this.$store.state.totpState
		},
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
					// If the stat could be changed, keep showing the loading
					// spinner until the user has finished the registration
					this.loading
						= this.$store.state.totpState === state.STATE_CREATED
				})
				.catch((e) => {
					OC.Notification.showTemporary(
						t('twofactor_totp', 'Could not enable TOTP'),
					)
					Logger.error('Could not enable TOTP', e)

					// Restore on error
					this.loading = false
					this.enabled = false
				})
				.catch((e) => Logger.error(e))
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
							t(
								'twofactor_totp',
								'Could not verify your key. Please try again',
							),
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
</style>
