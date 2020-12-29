<?php

declare(strict_types = 1);

/**
 * @author Christoph Wurst <christoph@winzerhof-wurst.at>
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

namespace OCA\TwoFactorTOTP\Service;

use OCA\TwoFactorTOTP\Exception\NoTotpSecretFoundException;
use OCA\TwoFactorTOTP\Exception\TotpSecretAlreadySet;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\IUser;

interface ITotp {
	public const STATE_DISABLED = 0;
	public const STATE_CREATED = 1;
	public const STATE_ENABLED = 2;

	public function hasSecret(IUser $user): bool;

	/**
	 * Create a new secret
	 *
	 * Note: the newly generated secret is disabled by default, because
	 * the user should once confirm that the OTP app was set up successfully.
	 *
	 * @param IUser $user
	 * @return string the newly created secret
	 * @throws TotpSecretAlreadySet
	 */
	public function createSecret(IUser $user): string;

	/**
	 * Enable OTP for the given user. The secret has to be generated
	 * beforehand, using ITotp::createSecret
	 *
	 * @param IUser $user
	 * @param string $key for verification
	 * @return bool whether the key is valid and the secret has been enabled
	 * @throws DoesNotExistException
	 * @throws NoTotpSecretFoundException
	 */
	public function enable(IUser $user, $key): bool;

	public function deleteSecret(IUser $user, bool $byAdmin = false): void;

	public function validateSecret(IUser $user, string $key): bool;
}
