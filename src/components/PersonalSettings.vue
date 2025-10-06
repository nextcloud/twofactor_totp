<!--
  - SPDX-FileCopyrightText: 2025 Olav and Niklas Seyfarth, Contributors <https://github.com/datenschutz-individuell/twofactor_email/blob/main/CONTRIBUTORS.md>
  - SPDX-License-Identifier: AGPL-3.0-or-later
-->

<!-- Sync strings with LoginSetup.vue -->
<template>
	<div id="twofactor_email-personal_settings">
		<div v-if="store.hasEmail">
			<p>
				<NcCheckboxRadioSwitch v-model="store.enabled"
						type="switch"
						:loading="loading"
						@update:model-value="onUpdate">
					{{ t('twofactor_email', 'Use two-factor authentication via e-mail') }}
				</NcCheckboxRadioSwitch>
			</p>
			<p v-if="store.enabled">
				{{ t('twofactor_email', 'Codes will be sent to your primary e-mail address:') }} <b>{{ store.email }}</b>
			</p>
		</div>
		<div v-else>
			<span class="notice">
				{{ t('twofactor_email', 'You cannot enable two-factor authentication via e-mail. You need to set a primary e-mail address (in your personal settings) first.') }}
			</span>
		</div>
		<span v-if="store.error === 'no-email'" class="error">
			{{ t('twofactor_email', 'Apparently your previously configured e-mail address just vanished.') }}
		</span>
		<span v-else-if="store.error === 'save-failed'" class="error">
			{{ t('twofactor_email', 'Could not enable/disable two-factor authentication via e-mail.') }}
		</span>
		<span v-else-if="store.error" class="error">
			{{ t('twofactor_email', 'Unhandled error!') }}
		</span>
	</div>
</template>

<script setup>
import { ref } from "vue";
import NcCheckboxRadioSwitch from '@nextcloud/vue/components/NcCheckboxRadioSwitch'
import { t } from '@nextcloud/l10n'
import { confirmPassword } from '@nextcloud/password-confirmation'
import '@nextcloud/password-confirmation/style.css'

import Logger from '../Logger.js'
import { usePersonalSettingsStore } from "../Store.js"

const store = usePersonalSettingsStore()
store.loadInitialState('enabled', 'hasEmail', 'email')

const loading = ref(false)

async function onUpdate() {
	if (loading.value) {
		// Ignore event
		Logger.debug('still loading -> ignoring event')
		return
	}
	loading.value = true

	try {
		await confirmPassword()
		await store.save()
	} catch (error) {
		console.error(error)
	} finally {
		loading.value = false
	}
}
</script>

<style scoped>
.loading {
	display: inline-block;
	vertical-align: middle;
	margin-inline: -2px 1px;
}
</style>
