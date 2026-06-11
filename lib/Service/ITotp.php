<?php

declare(strict_types = 1);

/*
 * SPDX-FileCopyrightText: 2016 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-only
 */

namespace OCA\TwoFactorTOTP\Service;

use OCA\TwoFactorTOTP\Db\TotpSecret;
use OCA\TwoFactorTOTP\Exception\NoTotpSecretFoundException;
use OCA\TwoFactorTOTP\Exception\TotpSecretAlreadySet;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\IUser;

interface ITotp {
	public const STATE_DISABLED = 0;
	public const STATE_CREATED = 1;
	public const STATE_ENABLED = 2;

	public const ALGORITHM_SHA1 = 'sha1';
	public const ALGORITHM_SHA256 = 'sha256';
	public const ALGORITHM_SHA512 = 'sha512';
	public const DEFAULT_ALGORITHM = self::ALGORITHM_SHA1;

	/** Minimum secret length in Base32 characters (130 bits) */
	public const MIN_SECRET_LENGTH = 26;
	/** Default secret length in Base32 characters (160 bits, per RFC 4226 recommendation) */
	public const DEFAULT_SECRET_LENGTH = 32;
	/** Maximum secret length in Base32 characters */
	public const MAX_SECRET_LENGTH = 128;

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
	 * @throws NoTotpSecretFoundException
	 */
	public function getSecret(IUser $user): TotpSecret;

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

	public function validateSecret(TotpSecret $secret, string $key): bool;
}
