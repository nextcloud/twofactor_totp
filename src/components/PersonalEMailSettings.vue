<!--
  - @copyright 2018 Christoph Wurst <christoph@winzerhof-wurst.at>
  -
  - @author 2024 Nico Kluge <nico.kluge@klugecoded.com>
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
	<div id="twofactor-email-settings">
		<template v-if="loading">
			<span class="icon-loading-small email-loading" />
			<span> {{ t('twofactor_email', 'Enable e-mail') }} </span>
		</template>
		<div v-else>
			<input id="email-enabled"
				v-model="enabled"
				type="checkbox"
				class="checkbox"
				:disabled="loading"
				@change="toggleEnabled">
			<label for="email-enabled">{{
				t('twofactor_email', 'Enable e-mail')
			}}</label>
		</div>
		<div v-if="errorHint">
			<span class="error"> {{ errorHint }} </span>
		</div>
		<SetupConfirmation v-if="email"
			:email="email"
			:loading="loadingConfirmation"
			:confirmation.sync="confirmation"
			@confirm="enableTwoFactorEMail" />
	</div>
</template>

<script>
import { confirmPassword } from '@nextcloud/password-confirmation'
import '@nextcloud/password-confirmation/dist/style.css'

import Logger from '../logger.js'
import SetupConfirmation from './SetupConfirmation.vue'
import state from '../state.js'

export default {
	name: 'PersonalEMailSettings',
	components: {
		SetupConfirmation,
	},
	data() {
		return {
			loading: false,
			loadingConfirmation: false,
			enabled: this.$store.state.emailState === state.STATE_ENABLED,
			email: '',
			errorHint: '',
			confirmation: '',
		}
	},
	computed: {
		state() {
			return this.$store.state.emailState
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
				return this.createTwoFactorEMail()
			} else {
				return this.disableTwoFactorEMail()
			}
		},

		createTwoFactorEMail() {
			// Show loading spinner
			this.loading = true
			this.errorHint = ''

			Logger.debug('starting setup')

			return confirmPassword()
				.then(() => this.$store.dispatch('enable'))
				.then(({ email }) => {
					this.email = email
					// If the stat could be changed, keep showing the loading
					// spinner until the user has finished the registration
					this.loading = this.$store.state.emailState === state.STATE_CREATED
					if (!this.email) {
						this.errorHint = t('twofactor_email', 'Unable to send email authentication code, because no user email address is set!')
						this.enabled = false
					}
				})
				.catch((e) => {
					this.errorHint = t('twofactor_email', 'Unable to activate email authentication. It\'s possible that the server is experiencing difficulties with mail delivery.')
					Logger.error('Could not enable e-mail', e)

					// Restore on error
					this.loading = false
					this.enabled = false
				})
				.catch((e) => Logger.error(e))
		},

		enableTwoFactorEMail() {
			// Show loading spinner and disable input elements
			this.loading = true
			this.loadingConfirmation = true
			this.errorHint = ''

			Logger.debug('starting enable')

			return confirmPassword()
				.then(() => this.$store.dispatch('confirm', this.confirmation))
				.then(() => {
					if (this.$store.state.emailState === state.STATE_ENABLED) {
						// Success
						this.loading = false
						this.enabled = true
						this.email = ''
						this.errorHint = ''
					} else {
						this.errorHint = t('twofactor_email', 'Could not verify your code. Please try again.')
					}

					this.confirmation = ''
					this.loadingConfirmation = false
				})
				.catch(Logger.error)
		},

		disableTwoFactorEMail() {
			// Show loading spinner
			this.loading = true
			this.errorHint = ''

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
.email-loading {
	display: inline-block;
	vertical-align: sub;
	margin-left: -2px;
	margin-right: 4px;
}
</style>
