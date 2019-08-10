<!--
  - @copyright 2019 Christoph Wurst <christoph@winzerhof-wurst.at>
  -
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
	<div>
		<div v-if="loading" class="loading"></div>
		<SetupConfirmation
			v-else
			:loading="confirmationLoading"
			:secret="secret"
			:qr-url="qrUrl"
			:confirmation.sync="confirmation"
			@confirm="confirm"
		/>
		<form ref="confirmForm" method="POST"></form>
	</div>
</template>

<script>
import Logger from '../logger'
import { saveState } from '../services/StateService'
import SetupConfirmation from './SetupConfirmation'
import STATE from '../state'

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
				}
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
