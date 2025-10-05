<!--
  - SPDX-FileCopyrightText: 2019 Nextcloud GmbH and Nextcloud contributors
  - SPDX-License-Identifier: AGPL-3.0-or-later
-->

<!-- Sync strings with PersonalSettings.vue -->
<template>
	<div v-if="error">
		<span v-if="error === 'no-email'" class="error">
			{{ t('twofactor_email', 'You cannot enable two-factor authentication via e-mail. You need to set a primary e-mail address (in your personal settings) first.') }}
		</span>
		<span v-else-if="error === 'save-failed'" class="error">
			{{ t('twofactor_email', 'Could not enable/disable two-factor authentication via e-mail.') }}
		</span>
		<span v-else class="error">
			{{ t('twofactor_email', 'Unhandled error!') }}
		</span>
	</div>
	<div v-else>
		<div v-if="loading" class="loading" />
		<p>Successfully enabled</p>
		<p>{{ t('twofactor_email', 'Codes will be sent to your primary e-mail address') }}:<br><b>{{ maskedEmail }}</b></p>
		<form ref="confirmForm" method="POST">
			<button>{{ t('twofactor_email', 'Proceed') }}</button>
		</form>
	</div>
</template>

<script>
import Logger from '../Logger.js'

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
