/**
 * SPDX-FileCopyrightText: 2025 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

import { exec as execCallback } from 'child_process'
import util from 'util'

const exec = util.promisify(execCallback)

/**
 * Helper to execute the occ command.
 *
 * @param args Arguments ot pass to occ
 * @return The result of the command
 */
export async function occ(args: string): Promise<{stdout: string, stderr: string}> {
	return exec(`php ../../occ ${args}`)
}

/**
 * Enforce twofactor auth for all users.
 */
export async function enforceTwofactorAuth(enforce: boolean): Promise<void> {
	const flag = enforce ? '--on' : '--off'
	try {
		await occ(`twofactorauth:enforce ${flag}`)
	} catch (error) {
		console.error(`Failed to enfore twofactor auth: ${error}`)
		throw error
	}
}

/**
 * Disable twofactor totp for all given users.
 *
 * @param {string[]} users List of uids
 */
export async function disableTwofactorAuth(users: string[]): Promise<void> {
	for (const user of users) {
		try {
			await occ(`twofactorauth:disable ${user} totp`)
		} catch (error) {
			console.error(`Failed to disable twofactor totp for ${user}: ${error}`)
			throw error
		}
	}
}
