<?php

declare(strict_types = 1);

/**
 * @author Nico Kluge <nico.kluge@klugecoded.com>
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

namespace OCA\TwoFactorEMail\Service;

use OCA\TwoFactorEMail\Db\TwoFactorEMail;
use OCA\TwoFactorEMail\Exception\NoTwoFactorEMailFoundException;
use OCA\TwoFactorEMail\Exception\TwoFactorEMailAlreadySet;
use OCA\TwoFactorEMail\Exception\UserEMailAddressNotSet;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\IUser;

interface IEMailService {
	public const STATE_DISABLED = 0;
	public const STATE_CREATED = 1;
	public const STATE_ENABLED = 2;

	public function isEnabled(IUser $user): bool;

	/**
	 * Create a new two-factor email
	 *
	 * Note: the newly generated two-factor email is disabled by default, because
	 * the user should once confirm that the email confirmation was set up successfully.
	 *
	 * @param IUser $user
	 * @return string the newly created email
	 * @throws TwoFactorEMailAlreadySet
	 */
	public function createTwoFactorEMail(IUser $user, string $email = ""): TwoFactorEMail;

	/**
	 * Enable email confirmation for the given user. The two-factor email has to be generated
	 * beforehand, using IEMailService::createTwoFactorEMail
	 *
	 * @param IUser $user
	 * @param string $key for verification
	 * @return bool whether the key is valid and the two-factor email has been enabled
	 * @throws DoesNotExistException
	 * @throws NoTwoFactorEMailFoundException
	 */
	public function enable(IUser $user, string $key): bool;

	public function deleteTwoFactorEMail(IUser $user, bool $byAdmin = false): void;

	public function validateTwoFactorEMail(IUser $user, string $key): bool;

	/**
	 * @param IUser $user
	 * @param string $authenticationCode
	 * @return string the target email address, where the mail was sent to. empty string if no email available
	 * @throws DoesNotExistException
	 * @throws \Exception
	 */
	public function setAndSendAuthCode(IUser $user, string $authenticationCode): string;
}
