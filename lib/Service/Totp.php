<?php

declare(strict_types=1);

/**
 * @author Christoph Wurst <christoph@winzerhof-wurst.at>
 * @copyright Copyright (c) 2016 Christoph Wurst <christoph@winzerhof-wurst.at>
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

use Base32\Base32;
use EasyTOTP\Factory;
use EasyTOTP\TOTPInterface;
use EasyTOTP\TOTPValidResultInterface;
use OCA\TwoFactorTOTP\AppInfo\Application;
use OCA\TwoFactorTOTP\Db\TotpSecret;
use OCA\TwoFactorTOTP\Db\TotpSecretMapper;
use OCA\TwoFactorTOTP\Event\DisabledByAdmin;
use OCA\TwoFactorTOTP\Event\StateChanged;
use OCA\TwoFactorTOTP\Exception\NoTotpSecretFoundException;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\EventDispatcher\IEventDispatcher;
use OCP\IConfig;
use OCP\IUser;
use OCP\Security\ICrypto;
use OCP\Security\ISecureRandom;

class Totp implements ITotp {
	private const DEFAULT_SECRET_LENGTH = 32;
	private const DEFAULT_HASH_ALGORITHM = TOTPInterface::HASH_SHA1;
	private const DEFAULT_TOKEN_LENGTH = 6;

	private const MIN_SECRET_LENGTH = 26;
	private const MAX_SECRET_LENGTH = 128;
	private const MIN_TOKEN_LENGTH = 6;
	private const MAX_TOKEN_LENGTH = 12;

	/** @var TotpSecretMapper */
	private $secretMapper;

	/** @var ICrypto */
	private $crypto;

	/** @var IEventDispatcher */
	private $eventDispatcher;

	/** @var ISecureRandom */
	private $random;

	public function __construct(TotpSecretMapper $secretMapper,
		ICrypto $crypto,
		IEventDispatcher $eventDispatcher,
		ISecureRandom $random,
		IConfig $config) {
		$this->secretMapper = $secretMapper;
		$this->crypto = $crypto;
		$this->eventDispatcher = $eventDispatcher;
		$this->random = $random;
		$this->config = $config;
	}

	private function getSecretLength(): int {
		$length = (int)$this->config->getAppValue(Application::APP_ID, 'secret_length', self::DEFAULT_SECRET_LENGTH);
		return ($length >= self::MIN_SECRET_LENGTH && $length <= self::MAX_SECRET_LENGTH) ? $length : self::DEFAULT_SECRET_LENGTH;
	}

	private function getHashAlgorithm(): string {
		$algorithm = strtolower($this->config->getAppValue(Application::APP_ID, 'hash_algorithm', self::DEFAULT_HASH_ALGORITHM));
		return in_array($algorithm, [TOTPInterface::HASH_SHA1, TOTPInterface::HASH_SHA256, TOTPInterface::HASH_SHA512], true) ? $algorithm : self::DEFAULT_HASH_ALGORITHM;
	}

	private function getTokenLength(): int {
		$length = (int)$this->config->getAppValue(Application::APP_ID, 'token_length', self::DEFAULT_TOKEN_LENGTH);
		return ($length >= self::MIN_TOKEN_LENGTH && $length <= self::MAX_TOKEN_LENGTH) ? $length : self::DEFAULT_TOKEN_LENGTH;
	}

	public function hasSecret(IUser $user): bool {
		try {
			$secret = $this->secretMapper->getSecret($user);
			return ITotp::STATE_ENABLED === (int)$secret->getState();
		} catch (DoesNotExistException $ex) {
			return false;
		}
	}

	private function generateSecret(): string {
		$secretLength = $this->getSecretLength();
		return $this->random->generate($secretLength, ISecureRandom::CHAR_UPPER.'234567');
	}

	/**
	 * @param IUser $user
	 */
	public function createSecret(IUser $user): string {
		try {
			// Delete existing one
			$oldSecret = $this->secretMapper->getSecret($user);
			$this->secretMapper->delete($oldSecret);
		} catch (DoesNotExistException $ex) {
			// Ignore
		}

		// Create new one
		$secret = $this->generateSecret();

		$dbSecret = new TotpSecret();
		$dbSecret->setUserId($user->getUID());
		$dbSecret->setSecret($this->crypto->encrypt($secret));
		$dbSecret->setState(ITotp::STATE_CREATED);

		$this->secretMapper->insert($dbSecret);
		return $secret;
	}

	public function enable(IUser $user, $key): bool {
		if (!$this->validateSecret($user, $key)) {
			return false;
		}
		$dbSecret = $this->secretMapper->getSecret($user);
		$dbSecret->setState(ITotp::STATE_ENABLED);
		$this->secretMapper->update($dbSecret);

		$this->eventDispatcher->dispatch(StateChanged::class, new StateChanged($user, true));

		return true;
	}

	public function deleteSecret(IUser $user, bool $byAdmin = false): void {
		try {
			// TODO: execute DELETE sql in mapper instead
			$dbSecret = $this->secretMapper->getSecret($user);
			$this->secretMapper->delete($dbSecret);
		} catch (DoesNotExistException $ex) {
			// Ignore
		}

		if ($byAdmin) {
			$this->eventDispatcher->dispatch(DisabledByAdmin::class, new DisabledByAdmin($user));
		} else {
			$this->eventDispatcher->dispatch(StateChanged::class, new StateChanged($user, false));
		}
	}

	public function validateSecret(IUser $user, string $key): bool {
		try {
			$dbSecret = $this->secretMapper->getSecret($user);
		} catch (DoesNotExistException $ex) {
			throw new NoTotpSecretFoundException();
		}

		$secret = $this->crypto->decrypt($dbSecret->getSecret());
		$hashAlgorithm = $this->getHashAlgorithm();
		$tokenLength = $this->getTokenLength();
		$otp = Factory::getTOTP(Base32::decode($secret), 30, $tokenLength, 0, $hashAlgorithm);

		$counter = null;
		$lastCounter = $dbSecret->getLastCounter();
		if ($lastCounter !== -1) {
			$counter = $lastCounter;
		}

		$result = $otp->verify($key, 3, $counter);
		if ($result instanceof TOTPValidResultInterface) {
			$dbSecret->setLastCounter($result->getCounter());
			$this->secretMapper->update($dbSecret);

			return true;
		}

		return false;
	}
}
