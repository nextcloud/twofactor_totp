/*
 * SPDX-FileCopyrightText: 2019 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

import { getCurrentUser } from '@nextcloud/auth'
import { getLoggerBuilder } from '@nextcloud/logger'

const builder = getLoggerBuilder().setApp('twofactor_totp')

const user = getCurrentUser()
if (user !== null) {
	builder.setUid(user.uid)
}

export default builder.build()
