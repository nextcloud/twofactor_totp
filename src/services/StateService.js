/*
 * SPDX-FileCopyrightText: 2019 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

import Axios from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'

/**
 * Persist the TOTP state change on the server.
 *
 * @param {object} data request payload, e.g. the new state and confirmation code
 * @return {Promise<object>} the updated state returned by the server
 */
export async function saveState(data) {
	const url = generateUrl('/apps/twofactor_totp/settings/enable')

	const resp = await Axios.post(url, data)
	return resp.data
}
