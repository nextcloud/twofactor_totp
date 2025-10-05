<!--
  - SPDX-FileCopyrightText: 2025 Olav and Niklas Seyfarth, Contributors <https://github.com/datenschutz-individuell/twofactor_email/blob/master/CONTRIBUTORS.md>
  - SPDX-License-Identifier: AGPL-3.0-or-later
-->

<!-- Sync strings with LoginSetup.vue -->
<template>
	<div id="twofactor_email-personal_settings">
		<div v-if="hasEmail">
			<p>
				<NcCheckboxRadioSwitch type="switch"
					:checked.sync="enabled"
					:loading="loading"
					@update:checked="toggleEnabled">
					{{ t('twofactor_email', 'Use two-factor authentication via e-mail') }}
				</NcCheckboxRadioSwitch>
			</p>
			<p v-if="enabled">
				{{ t('twofactor_email', 'Codes will be sent to your primary e-mail address') }} <b>{{ email }}.</b>
			</p>
		</div>
		<div v-else>
			<span class="notice">
				{{ t('twofactor_email', 'You cannot enable two-factor authentication via e-mail. You need to set a primary e-mail address (in your personal settings) first.') }}
			</span>
		</div>
		<div v-if="error">
			<span v-if="error === 'no-email'" class="error">
				{{ t('twofactor_email', 'Apparently your previously configured e-mail address just vanished.') }}
			</span>
			<span v-else-if="error === 'save-failed'" class="error">
				{{ t('twofactor_email', 'Could not enable/disable two-factor authentication via e-mail.') }}
			</span>
			<span v-else class="error">
				{{ t('twofactor_email', 'Unhandled error!') }}
			</span>
		</div>
	</div>
</template>

<script>
import NcCheckboxRadioSwitch from '@nextcloud/vue/dist/Components/NcCheckboxRadioSwitch.js'
import { confirmPassword } from '@nextcloud/password-confirmation'
import '@nextcloud/password-confirmation/dist/style.css'
import Logger from '../Logger.js'

export default {
	name: 'PersonalSettings',

	components: {
		NcCheckboxRadioSwitch,
	},

	data() {
		return {
			enabled: this.$store.state.enabled,
			hasEmail: this.$store.state.hasEmail,
			email: this.$store.state.email,
			error: null,
			loading: false,
		}
	},

	methods: {
		toggleEnabled() {
			if (this.loading) {
				// Ignore event
				Logger.debug('still loading -> ignoring event')
				return
			}
			this.loading = true

			confirmPassword()
				.then(() => {
					let action
					if (this.enabled) {
						action = this.$store.dispatch('enable')
					} else {
						action = this.$store.dispatch('disable')
					}

					action
						.then(({ enabled, error }) => {
							if (enabled !== null) {
								this.enabled = enabled
							}
							this.error = error
						})
						.catch(console.error.bind(this))
						.then(() => {
							this.loading = false
						})
				})
		},
	},
}
</script>

<style scoped>
.loading {
	display: inline-block;
	vertical-align: middle;
	margin-left: -2px;
	margin-right: 1px;
}
</style>
