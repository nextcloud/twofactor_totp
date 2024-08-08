<?php

declare(strict_types=1);

/**
 * @author Christoph Wurst <christoph@winzerhof-wurst.at>
 * @copyright Copyright (c) 2016 Christoph Wurst <christoph@winzerhof-wurst.at>
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

use Base32\Base32;
use EasyTOTP\Factory;
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
use OCP\ILogger;
use OCP\IUser;
use OCP\Security\ICrypto;
use OCP\Security\ISecureRandom;
use RuntimeException;

class Totp implements ITotp {
/**
 * R6 - The algorithm MUST use a strong shared secret. The length of
 * the shared secret MUST be at least 128 bits. This document
 * RECOMMENDs a shared secret length of 160 bits.
 * https://datatracker.ietf.org/doc/html/rfc6238#section-4
 + https://datatracker.ietf.org/doc/html/rfc4226#section-4
 */
	private const MIN_SECRET_LENGTH = 26; // 130 bit
	private const DEFAULT_SECRET_LENGTH = 32; // 160 bit
	private const MAX_SECRET_LENGTH = 128; // 640 bit

	private const DEFAULT_ALGORITHM = ITotp::HASH_SHA1;

	private const DEFAULT_DIGITS = 6; // Tokenlength
	private const MAX_DIGITS = 8;

	private const DEFAULT_PERIOD = 30; // Period in seconds

	/** @var TotpSecretMapper */
	private $secretMapper;

	/** @var ICrypto */
	private $crypto;

	/** @var IEventDispatcher */
	private $eventDispatcher;

	/** @var ISecureRandom */
	private $random;

	/** @var IConfig */
	private $config;

	/** @var ILogger */
	private $logger;

	public function __construct(TotpSecretMapper $secretMapper,
		ICrypto $crypto,
		IEventDispatcher $eventDispatcher,
		ISecureRandom $random,
		IConfig $config,
		ILogger $logger) {
		$this->secretMapper = $secretMapper;
		$this->crypto = $crypto;
		$this->eventDispatcher = $eventDispatcher;
		$this->random = $random;
		$this->config = $config;
		$this->logger = $logger;
	}

	private function getSecretLength(): int {
		$length = (int)$this->config->getAppValue(Application::APP_ID, 'secret_length', (string) self::DEFAULT_SECRET_LENGTH);
		return ($length >= self::MIN_SECRET_LENGTH && $length <= self::MAX_SECRET_LENGTH) ? $length : self::DEFAULT_SECRET_LENGTH;
	}

	public function getDefaultAlgorithm(): int {
		$algorithm = (int)$this->config->getAppValue(Application::APP_ID, 'hash_algorithm', (string) self::DEFAULT_ALGORITHM);
		$this->logger->debug("Default Hash Algorithm from config: " . $algorithm);
		return in_array($algorithm, [ITotp::HASH_SHA1, ITotp::HASH_SHA256, ITotp::HASH_SHA512]) ? $algorithm : self::DEFAULT_ALGORITHM;
	}

	public function getDefaultDigits(): int {
		$length = (int)$this->config->getAppValue(Application::APP_ID, 'token_length', (string) self::DEFAULT_DIGITS);
		$this->logger->debug("Default Token Length from config: " . $length);
		return ($length >= self::DEFAULT_DIGITS && $length <= self::MAX_DIGITS) ? $length : self::DEFAULT_DIGITS;
	}

	public function getDefaultPeriod(): int {
		return self::DEFAULT_PERIOD;
	}

	public static function getAlgorithmById(int $id): string {
		switch ($id) {
			case ITotp::HASH_SHA1:
				return \EasyTOTP\TOTPInterface::HASH_SHA1;
			case ITotp::HASH_SHA256:
				return \EasyTOTP\TOTPInterface::HASH_SHA256;
			case ITotp::HASH_SHA512:
				return \EasyTOTP\TOTPInterface::HASH_SHA512;
			default:
				return \EasyTOTP\TOTPInterface::HASH_SHA1; // Default value
		}
	}

	public function getAlgorithmId(IUser $user): int {
		try {
			$secret = $this->secretMapper->getSecret($user);
			$algorithm = (int)$secret->getAlgorithm();
			$this->logger->debug("Hash Algorithm from secret: " . $algorithm);
			return $algorithm; // Returns ID
		} catch (DoesNotExistException $ex) {
			$this->logger->debug("Hash Algorithm not found, defaulting to " . self::DEFAULT_ALGORITHM);
			return self::DEFAULT_ALGORITHM; // Default value
		}
	}

	public function getDigits(IUser $user): int {
		try {
			$secret = $this->secretMapper->getSecret($user);
			$digits = (int)$secret->getDigits();
			$this->logger->debug("Digits token length from secret: " . $digits);
			return $digits;
		} catch (DoesNotExistException $ex) {
			$this->logger->debug("Digits token length not found, defaulting to " . self::DEFAULT_DIGITS);
			return self::DEFAULT_DIGITS; // Default value
		}
	}

	public function getPeriod(IUser $user): int {
		try {
			$secret = $this->secretMapper->getSecret($user);
			$digits = (int)$secret->getPeriod();
			$this->logger->debug("Period in seconds from secret: " . $digits);
			return $digits;
		} catch (DoesNotExistException $ex) {
			$this->logger->debug("Period in seconds not found, defaulting to " . self::DEFAULT_DIGITS);
			return self::DEFAULT_PERIOD; // Default value
		}
	}

	public function getSettings(IUser $user): array {
		$settings = [
			'algorithm' => $this->getAlgorithmId($user),
			'digits' => $this->getDigits($user),
			'period' => $this->getPeriod($user)
		];
		$this->logger->debug("Settings for user {$user->getUID()}: " . json_encode($settings));
		return $settings;
	}

	public function updateSettings(IUser $user, string $customSecret = null, int $algorithm, int $digits, int $period): void {
		try {
			$dbSecret = $this->secretMapper->getSecret($user);
			$dbSecret->setAlgorithm($algorithm);
			$dbSecret->setDigits($digits);
			$dbSecret->setPeriod($period);
			if ($customSecret !== null) {
				$dbSecret->setSecret($this->crypto->encrypt($customSecret));
			}
			$this->secretMapper->update($dbSecret);
			$this->logger->debug("Updated settings for user {$user->getUID()} with Algorithm: $algorithm, Digits: $digits, Period: $period" . ($customSecret !== null ? ", and a custom secret" : ""));
		} catch (DoesNotExistException $ex) {
			$this->logger->alert("Secret not found for user {$user->getUID()}, cannot update settings.");
			throw new RuntimeException('Secret not found for user.');
		}
	}

	public function hasSecret(IUser $user): bool {
		try {
			$secret = $this->secretMapper->getSecret($user);
			$this->logger->debug("Secret state for user {$user->getUID()}: " . $secret->getState());
			return ITotp::STATE_ENABLED === (int)$secret->getState();
		} catch (DoesNotExistException $ex) {
			$this->logger->debug("No secret found for user {$user->getUID()}");
			return false;
		}
	}

	private function generateSecret(): string {
		$secretLength = $this->getSecretLength();
		return $this->random->generate($secretLength, ISecureRandom::CHAR_UPPER.'234567');
	}

	/**
	 * @param IUser $user
	 * @param string $customSecret
	 * @param int $algorithm
	 * @param int $digits
	 * @param int $period
	 * @return string the newly created secret
	 */
	public function createSecret(IUser $user, string $customSecret = null, int $algorithm = self::DEFAULT_ALGORITHM, int $digits = self::DEFAULT_DIGITS, int $period = self::DEFAULT_PERIOD): string {
		try {
			// Delete existing one
			$oldSecret = $this->secretMapper->getSecret($user);
			$this->secretMapper->delete($oldSecret);
		} catch (DoesNotExistException $ex) {
			// Ignore
		}

		// Use the provided custom secret or generate a new one
		$secret = $customSecret ?? $this->generateSecret();

		$dbSecret = new TotpSecret();
		$dbSecret->setUserId($user->getUID());
		$dbSecret->setSecret($this->crypto->encrypt($secret));
		$dbSecret->setState(ITotp::STATE_CREATED);
		$dbSecret->setAlgorithm($algorithm);
		$dbSecret->setDigits($digits);
		$dbSecret->setPeriod($period);

		$this->secretMapper->insert($dbSecret);
		$this->logger->debug("Created new secret for user {$user->getUID()} with Token Length: $digits and Hash Algorithm: $algorithm");
		return $secret;
	}

	public function enable(IUser $user, $key): bool {
		if (!$this->validateSecret($user, $key)) {
			$this->logger->debug("Secret validation failed for user {$user->getUID()} with key $key");
			return false;
		}
		$dbSecret = $this->secretMapper->getSecret($user);
		$dbSecret->setState(ITotp::STATE_ENABLED);
		$this->secretMapper->update($dbSecret);

		$this->eventDispatcher->dispatch(StateChanged::class, new StateChanged($user, true));

		$this->logger->debug("Enabled TOTP for user {$user->getUID()}");
		return true;
	}

	public function deleteSecret(IUser $user, bool $byAdmin = false): void {
		try {
			// TODO: execute DELETE sql in mapper instead
			$dbSecret = $this->secretMapper->getSecret($user);
			$this->secretMapper->delete($dbSecret);
			$this->logger->debug("Deleted secret for user {$user->getUID()}");
		} catch (DoesNotExistException $ex) {
			// Ignore
			$this->logger->debug("No secret to delete for user {$user->getUID()}");
		}

		if ($byAdmin) {
			$this->eventDispatcher->dispatch(DisabledByAdmin::class, new DisabledByAdmin($user));
			$this->logger->debug("Dispatched DisabledByAdmin event for user {$user->getUID()}");
		} else {
			$this->eventDispatcher->dispatch(StateChanged::class, new StateChanged($user, false));
			$this->logger->debug("Dispatched StateChanged event for user {$user->getUID()}");
		}
	}

	public function validateSecret(IUser $user, string $key): bool {
		try {
			$dbSecret = $this->secretMapper->getSecret($user);
		} catch (DoesNotExistException $ex) {
			$this->logger->debug("No TOTP secret found for user {$user->getUID()}");
			throw new NoTotpSecretFoundException();
		}

		$secret = $this->crypto->decrypt($dbSecret->getSecret());
		$algorithm = self::getAlgorithmById($this->getAlgorithmId($user));
		$digits = $this->getDigits($user);
		$period = $this->getPeriod($user);
		$otp = Factory::getTOTP(Base32::decode($secret), $period, $digits, 0, $algorithm);

		$counter = null;
		$lastCounter = $dbSecret->getLastCounter();
		if ($lastCounter !== -1) {
			$counter = $lastCounter;
		}

		$result = $otp->verify($key, 3, $counter);
		if ($result instanceof TOTPValidResultInterface) {
			$dbSecret->setLastCounter($result->getCounter());
			$this->secretMapper->update($dbSecret);

			$this->logger->debug("Validated secret for user {$user->getUID()}");
			return true;
		}

		$this->logger->debug("Failed to validate secret for user {$user->getUID()}");
		return false;
	}
}
