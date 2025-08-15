<!--
  - SPDX-FileCopyrightText: 2018 Nextcloud GmbH and Nextcloud contributors
  - SPDX-License-Identifier: AGPL-3.0-or-later
-->

<template>
	<div id="twofactor-email-settings">
		<NcCheckboxRadioSwitch v-if="hasEmail"
			type="switch"
			:checked.sync="enabled"
			:loading="loading"
			@update:checked="toggleEnabled">
			{{ t('twofactor_email', 'Use two-factor authentication via e-mail') }}
		</NcCheckboxRadioSwitch>
		<div v-else>
			<span class="notice"> {{ t('twofactor_email', 'You need to set your email address first.') }} </span>
		</div>
		<div v-if="error">
			<span class="error"> {{ error }} </span>
		</div>
	</div>
</template>

<script>
import NcCheckboxRadioSwitch from '@nextcloud/vue/dist/Components/NcCheckboxRadioSwitch.js'

import { confirmPassword } from '@nextcloud/password-confirmation'
import '@nextcloud/password-confirmation/dist/style.css'

import Logger from '../logger.js'

export default {
	name: 'PersonalEMailSettings',

	components: {
		NcCheckboxRadioSwitch,
	},

	data() {
		return {
			enabled: this.$store.state.enabled,
			hasEmail: this.$store.state.hasEmail,
			error: this.$store.state.error,
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
						.then(enabled => {
							this.enabled = enabled
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
