<?php

/**
 * @author Christoph Wurst <christoph@winzerhof-wurst.at>
 * @author Semih Serhat Karakaya <karakayasemi@itu.edu.tr>
 *
 * Two-factor TOTP
 *
 * This code is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License, version 3,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License, version 3,
 * along with this program.  If not, see <http://www.gnu.org/licenses/>
 *
 */

namespace OCA\TwoFactor_Totp\Service;

use OCA\TwoFactor_Totp\Exception\NoTotpSecretFoundException;
use OCA\TwoFactor_Totp\Exception\TotpSecretAlreadySet;
use OCP\IUser;

interface ITotp {

	/**
	 * @param IUser $user
	 */
	public function hasSecret(IUser $user);

	/**
	 * @param IUser $user
	 * @return string the newly created secret
	 * @throws TotpSecretAlreadySet
	 */
	public function createSecret(IUser $user);

	/**
	 * @param IUser $user
	 */
	public function deleteSecret(IUser $user);

	/**
	 * @param IUser $user
	 * @param string $key 6 digits numeric time-based one time password.
	 * @return boolean If key is correct
	 * @throws NoTotpSecretFoundException
	 */
	public function validateKey(IUser $user, $key);

	/**
	 * @param IUser $user
	 * @param string $key
	 */
	public function verifySecret(IUser $user, $key);

	/**
	 * @param IUser $user
	 * @return boolean
	 */
	public function isVerified(IUser $user);
}
