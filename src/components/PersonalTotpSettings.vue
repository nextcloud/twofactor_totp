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
			<span class="icon-loading-small totp-loading"></span>
			<span> {{ t('twofactor_totp', 'Enable TOTP') }} </span>
		</template>
		<div v-else>
			<input type="checkbox"
				   id="totp-enabled"
				   class="checkbox"
				   v-model="enabled"
				   @change="toggleEnabled"
				   :disabled="loading">
			<label for="totp-enabled">{{ t('twofactor_totp', 'Enable TOTP') }}</label>
		</div>

		<div v-if="secret">
			<p>{{ t('twofactor_totp', 'Your new TOTP secret is:' )}} {{secret}}</p>
			<p> {{ t('twofactor_totp', 'For quick setup, scan this QR code with your TOTP app:') }}</p>
			<img :src="qr">
			<p> {{ t('twofactor_totp', 'After you configured your app, enter a test code below to ensure everything works correctly:') }} </p>
			<input id="totp-confirmation"
				   type="tel"
				   minlength="6"
				   maxlength="10"
				   autocomplete="off"
				   autocapitalize="off"
				   v-on:keydown="onConfirmKeyDown"
				   v-model="confirmation"
				   :disabled="loadingConfirmation"
				   :placeholder="t('twofactor_totp', 'Authentication code')">
			<input id="totp-confirmation-submit"
				   type="button"
				   v-on:click="enableTOTP"
				   :disabled="loadingConfirmation"
				   :value="t('twofactor_totp', 'Verify')">
		</div>
	</div>
</template>

<script>
	import confirmPassword from 'nextcloud-password-confirmation'

	import state from '../state'

	export default {
		name: 'PersonalTotpSettings',
		data () {
			return {
				loading: false,
				loadingConfirmation: false,
				enabled: this.$store.state.totpState === state.STATE_ENABLED,
				secret: undefined,
				confirmation: '',
			}
		},
		computed: {
			state() {
				return this.$store.state.totpState
			}
		},
		methods: {
			toggleEnabled() {
				if (this.loading) {
					// Ignore event
					return
				}

				if (this.enabled) {
					return this.createTOTP()
				} else {
					return this.disableTOTP()
				}
			},

			createTOTP () {
				// Show loading spinner
				this.loading = true

				return confirmPassword()
					.then(() => this.$store.dispatch('enable'))
					.then(({secret, qr}) => {
						this.secret = secret
						this.qr = qr
						// If the stat could be changed, keep showing the loading
						// spinner until the user has finished the registration
						this.loading = this.$store.state.totpState === state.STATE_CREATED
					})
					.catch(e => {
						OC.Notification.showTemporary(t('twofactor_totp', 'Could not enable TOTP'))
						console.error('Could not enable TOTP', e)

						// Restore on error
						this.loading = false
					})
					.catch(console.error)
			},

			enableTOTP () {
				// Show loading spinner and disable input elements
				this.loading = true
				this.loadingConfirmation = true

				return confirmPassword()
					.then(() => this.$store.dispatch('confirm', this.confirmation))
					.then(() => {
						if (this.$store.state.totpState === state.STATE_ENABLED) {
							// Success
							this.loading = false
							this.enabled = true
							this.qr = undefined
							this.secret = undefined
						} else {
							OC.Notification.showTemporary(t('twofactor_totp', 'Could not verify your key. Please try again'));
						}

						this.confirmation = ''
						this.loadingConfirmation = false
					})
					.catch(console.error)
			},

			onConfirmKeyDown(e) {
				if (e.which === 13) {
					this.enableTOTP()
				}
			},

			disableTOTP() {
				// Show loading spinner
				this.loading = true

				return confirmPassword()
					.then(() => this.$store.dispatch('disable'))
					.then(() => this.enabled = false)
					.catch(console.error.bind(this))
					.then(() => this.loading = false)
			}

		}
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