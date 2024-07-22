/**
 * @copyright 2019 Christoph Wurst <christoph@winzerhof-wurst.at>
 *
 * @author 2019 Christoph Wurst <christoph@winzerhof-wurst.at>
 *
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

import Axios from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'

// Function to save the TOTP state
export const saveState = async (data) => {
	const url = generateUrl('/apps/twofactor_totp/settings/enable')
	const resp = await Axios.post(url, data)
	return resp.data
}

// Function to get the TOTP state
export const getState = async () => {
	const url = generateUrl('/apps/twofactor_totp/settings/state')
	const resp = await Axios.get(url)
	return resp.data
}

// Function to update TOTP settings
export const updateSettings = async (settings) => {
	const url = generateUrl('/apps/twofactor_totp/settings/update')
	const resp = await Axios.post(url, settings)
	return resp.data
}
