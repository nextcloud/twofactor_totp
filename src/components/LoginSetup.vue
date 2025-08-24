<!--
  - SPDX-FileCopyrightText: 2019 Nextcloud GmbH and Nextcloud contributors
  - SPDX-License-Identifier: AGPL-3.0-or-later
-->

<template>
	<div v-if="error">
		<span v-if="error === 'no-email'" class="error"> {{ t('twofactor_email', 'Cannot enable two-factor authentication via e-mail as your e-mail address is not configured.') }} </span>
		<span v-else class="error"> {{ t('twofactor_email', 'Something went wrong') }} </span>
	</div>
	<div v-else>
		<div v-if="loading" class="loading" />
		<p>Successfully enabled</p>
		<p>Codes will be sent to your primary e-mail address:<br><b>{{ t('twofactor_email', maskedEmail) }}</b></p>
		<form ref="confirmForm" method="POST">
			<button>{{ t('twofactor_email', 'Proceed') }}</button>
		</form>
	</div>
</template>

<script>
import Logger from '../logger.js'

export default {
	name: 'LoginSetup',

	data() {
		return {
			error: this.$store.state.error,
			maskedEmail: this.$store.state.maskedEmail,
			loading: true,
		}
	},

	mounted() {
		this.load()
	},

	methods: {
		load() {
			this.$store.dispatch('enable')
				.then(({ enabled, error }) => {
					Logger.debug('enable two-factor e-mail request returned')
					if (enabled) {
						Logger.debug('two-factor e-mail successfully enabled')
					} else {
						this.error = error
					}
					this.loading = false
				})
				.catch(console.error.bind(this))
		},
		continue() {
			this.$refs.confirmForm.submit()
		}
	},
}
</script>

<style scoped>
.loading {
	min-height: 50px;
}
</style>
