/*
 * SPDX-FileCopyrightText: 2019 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

import Axios from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'
import Logger from '../logger.js'

/**
 * @param {boolean} enabled Enable or disable?
 * @return {Promise}
 */
export function persist(enabled) {
	const url = generateUrl('/apps/twofactor_email/settings/state')
	const data = {
		state: enabled,
	}

	Logger.debug('sending two-factor e-mail state change request', data)
	return Axios.post(url, data)
		.then(resp => resp.data)
}
