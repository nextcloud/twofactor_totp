<!--
  - SPDX-FileCopyrightText: 2019 Nextcloud GmbH and Nextcloud contributors
  - SPDX-License-Identifier: AGPL-3.0-or-later
-->

<template>
	<div>
		<div v-if="loading" class="loading" />
		<SetupConfirmation v-else
			:loading="confirmationLoading"
			:secret="secret"
			:qr-url="qrUrl"
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
			secret: '',
			qrUrl: '',
			confirmation: '',
		}
	},
	mounted() {
		this.load()
	},
	methods: {
		load() {
			this.loading = true
			Logger.info('starting TOTP setup')

			saveState({ state: STATE.STATE_CREATED }).then(
				({ secret, qrUrl }) => {
					Logger.info('TOTP secret received')

					this.secret = secret
					this.qrUrl = qrUrl

					this.loading = false
				},
			)
		},
		confirm() {
			this.loading = true

			saveState({
				state: STATE.STATE_ENABLED,
				code: this.confirmation,
			}).then(({ state }) => {
				if (state === STATE.STATE_ENABLED) {
					Logger.info('TOTP secret confirmed')

					Logger.info('todo: submit')
					this.$refs.confirmForm.submit()
				} else {
					Logger.warn('TOTP confirmation failed')

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
