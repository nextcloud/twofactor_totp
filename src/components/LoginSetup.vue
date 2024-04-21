<!--
  - @copyright 2019 Christoph Wurst <christoph@winzerhof-wurst.at>
  -
  - @author 2024 Nico Kluge <nico.kluge@klugecoded.com>
  - @author 2019 Christoph Wurst <christoph@winzerhof-wurst.at>
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
	<div v-if="errorHint">
		<span class="error"> {{ errorHint }} </span>
	</div>
	<div v-else>
		<div v-if="loading" class="loading" />
		<SetupConfirmation v-else
			:loading="confirmationLoading"
			:email="email"
			:confirmation.sync="confirmation"
			@confirm="confirm" />
		<form ref="confirmForm" method="POST" />
	</div>
</template>

<script>
import Logger from '../logger.js'
import { saveState } from '../services/StateService.js'
import SetupConfirmation from './SetupConfirmation.vue'
import STATE from '../state.js'

export default {
	name: 'LoginSetup',
	components: {
		SetupConfirmation,
	},
	data() {
		return {
			loading: true,
			confirmationLoading: false,
			email: '',
			errorHint: '',
			confirmation: '',
		}
	},
	mounted() {
		this.load()
	},
	methods: {
		load() {
			this.loading = true
			Logger.info('starting e-mail setup')

			saveState({ state: STATE.STATE_CREATED }).then(
				({ email }) => {
					Logger.info('E-mail auth code received')

					this.email = email

					if (!this.email) {
						this.errorHint = t('twofactor_email', 'Unable to send email authentication code, because no user email address is set!')
					}

					this.loading = false
				},
			)
			.catch((e) => {
				this.errorHint = t('twofactor_email', 'Unable to activate email authentication. It\'s possible that the server is experiencing difficulties with mail delivery.')
				Logger.error('Could not enable e-mail', e)

				// Restore on error
				this.loading = false
			})
			.catch((e) => Logger.error(e))

		},
		confirm() {
			this.loading = true

			saveState({
				state: STATE.STATE_ENABLED,
				code: this.confirmation,
			}).then(({ state }) => {
				if (state === STATE.STATE_ENABLED) {
					Logger.info('E-mail auth code confirmed')

					Logger.info('todo: submit')
					this.$refs.confirmForm.submit()
				} else {
					Logger.warn('E-mail confirmation failed')

					this.loading = false
				}
			})
		},
	},
}
</script>

<style scoped>
.loading {
	min-height: 50px;
}
</style>
