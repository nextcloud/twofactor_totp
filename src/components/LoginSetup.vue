<!--
  - SPDX-FileCopyrightText: 2019 Nextcloud GmbH and Nextcloud contributors
  - SPDX-License-Identifier: AGPL-3.0-or-later
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
