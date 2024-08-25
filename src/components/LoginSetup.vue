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
	<div id="twofactor-totp-login-setup">
		<div v-if="loading" class="loading" />
		<SetupConfirmation v-if="secret"
			:isCentered="true"
			:loading="loadingConfirmation"
			:secret="secret"
			:qr-url="qrUrl"
			:confirmation.sync="confirmation"
			@confirm="enableTOTP"
			@update-qr="updateQr" />
		<form ref="confirmForm" method="POST" />
	</div>
</template>

<script>
import Vue from 'vue'
import Vuex from 'vuex'
import store from '../store.js' // Store importieren

import Logger from '../logger.js'
import SetupConfirmation from './SetupConfirmation.vue'
import STATE from '../state.js'

Vue.use(Vuex)

export default {
	name: 'LoginSetup',
	components: {
		SetupConfirmation,
	},
	store,
	data() {
		return {
			loading: false,
			loadingConfirmation: false,
			secret: undefined,
			qrUrl: '',
			confirmation: '',
		}
	},
	mounted() {
		this.createTOTP()
	},
	methods: {

		createTOTP() {
			// Show loading spinner
			this.loading = true

			Logger.debug('starting TOTP setup')

			return this.$store.dispatch('enable')
				.then(({ secret, qrUrl }) => {
					this.secret = secret
					this.qrUrl = qrUrl
					// If the state could be changed, keep showing the loading
					// spinner until the user has finished the registration
					this.loading
						= this.$store.state.totpState === STATE.STATE_CREATED
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

			Logger.debug('starting enable TOTP')

			return this.$store.dispatch('confirm', this.confirmation)
				.then(() => {
					if (this.$store.state.totpState === STATE.STATE_ENABLED) {
						// Success
						this.loading = false
						this.enabled = true
						this.qrUrl = ''
						this.secret = undefined
						Logger.info('TOTP secret confirmed')
						this.$refs.confirmForm.submit()
					} else {
						Logger.warn('TOTP confirmation failed')
						this.loading = false
					}
					this.confirmation = ''
					this.loadingConfirmation = false
				})
				.catch(Logger.error)
		},

		updateQr({ secret, qrUrl }) {
			this.secret = secret
			this.qrUrl = qrUrl
		},
	},
}
</script>

<style scoped>
.loading {
	min-height: 50px;
}
</style>
