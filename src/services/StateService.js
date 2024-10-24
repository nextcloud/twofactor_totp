/*
 * SPDX-FileCopyrightText: 2019 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

import Axios from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'

export const saveState = async (data) => {
	const url = generateUrl('/apps/twofactor_totp/settings/enable')

	const resp = await Axios.post(url, data)
	return resp.data
}
