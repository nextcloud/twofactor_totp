<?php

declare(strict_types = 1);

/**
 * @author Christoph Wurst <christoph@winzerhof-wurst.at>
 * @author 2024 [ernolf] Raphael Gradenwitz <raphael.gradenwitz@googlemail.com>
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

	public const HASH_SHA1 = 1;
	public const HASH_SHA256 = 2;
	public const HASH_SHA512 = 3;

	public const DEFAULT_ALGORITHM = ITotp::HASH_SHA1;
	public const DEFAULT_DIGITS = 6; // Tokenlength
	public const DEFAULT_PERIOD = 30; // Period in seconds


	public function getAlgorithmById(int $id): string;

	public function hasSecret(IUser $user): bool;

	/**
	 * Get the default hash algorithm
	 *
	 * @return int the default algorithm
	 */
	public function getDefaultAlgorithm(): int;

	/**
	 * Get the default token length in digits
	 *
	 * @return int the default digits
	 */
	public function getDefaultDigits(): int;

	/**
	 * Create a new secret
	 *
	 * Note: the newly generated secret is disabled by default, because
	 * the user should once confirm that the OTP app was set up successfully.
	 *
	 * @param IUser $user
	 * @param string $customSecret
	 * @param int $algorithm
	 * @param int $digits
	 * @param int $period
	 * @return string the newly created secret
	 * @throws TotpSecretAlreadySet
	 */
	public function createSecret(IUser $user, string $customSecret = null, int $algorithm = self::DEFAULT_ALGORITHM, int $digits = self::DEFAULT_DIGITS, int $period = self::DEFAULT_PERIOD): string;

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

	public function getAlgorithmId(IUser $user): int;

	public function getDigits(IUser $user): int;

	public function getPeriod(IUser $user): int;

	public function updateSettings(IUser $user, string $customSecret = null, int $algorithm, int $digits, int $period): void;

	public function getSettings(IUser $user): array;
}
