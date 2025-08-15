<!--
  - SPDX-FileCopyrightText: 2019 Nextcloud GmbH and Nextcloud contributors
  - SPDX-License-Identifier: AGPL-3.0-or-later
-->

<template>
	<div v-if="error">
		<span class="error"> {{ error }} </span>
	</div>
	<div v-else>
		<div v-if="loading" class="loading" />
		<form ref="confirmForm" method="POST" />
	</div>
</template>

<script>
import Logger from '../logger.js'

export default {
	name: 'LoginSetup',

	data() {
		return {
			error: this.$store.state.error,
			loading: true,
		}
	},

	mounted() {
		this.load()
	},

	methods: {
		load() {
			this.$store.dispatch('enable')
				.then(enabled => {
					Logger.debug('enable two-factor e-mail request returned')
					if (enabled) {
						Logger.debug('two-factor e-mail successfully enabled')
						this.$refs.confirmForm.submit()
					}
					this.loading = false
				})
				.catch(console.error.bind(this))
		},
	},
}
</script>

<style scoped>
.loading {
	min-height: 50px;
}
</style>
