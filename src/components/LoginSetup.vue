<!--
  - SPDX-FileCopyrightText: 2025 Olav and Niklas Seyfarth, Contributors <https://github.com/datenschutz-individuell/twofactor_email/blob/main/CONTRIBUTORS.md>
  - SPDX-License-Identifier: AGPL-3.0-or-later
-->

<!-- Sync strings with PersonalSettings.vue -->
<template>
	<div id="twofactor_email-login_setup">
		<span v-if="store.error === 'no-email'" class="error">
			{{ t('twofactor_email', 'You cannot enable two-factor authentication via e-mail. You need to set a primary e-mail address (in your personal settings) first.') }}
		</span>
		<span v-else-if="store.error === 'save-failed'" class="error">
			{{ t('twofactor_email', 'Could not enable/disable two-factor authentication via e-mail.') }}
		</span>
		<span v-else-if="store.error" class="error">
			{{ t('twofactor_email', 'Unhandled error!') }}
		</span>
		<div v-else-if="loading" class="loading" style="min-height: 50px" />
		<div v-else>
			<p>Successfully enabled</p>
			<p>{{ t('twofactor_email', 'Codes will be sent to your primary e-mail address:') }} <b>{{ store.maskedEmail }}</b></p>
			<form method="POST">
				<button>{{ t('twofactor_email', 'Proceed') }}</button>
			</form>
		</div>
	</div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { t } from '@nextcloud/l10n'

import { usePersonalSettingsStore } from "../Store.js"

const store = usePersonalSettingsStore()
store.loadInitialState('maskedEmail')

const loading = ref(true)

onMounted(async () => {
	try {
		await store.enable()
	} catch (error) {
		console.error(error)
	} finally {
		loading.value = false
	}
})
</script>
