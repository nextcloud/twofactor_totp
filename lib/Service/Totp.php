<?php

declare(strict_types=1);

/*
 * SPDX-FileCopyrightText: 2016 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-only
 */

namespace OCA\TwoFactorTOTP\Service;

use Base32\Base32;
use EasyTOTP\Factory;
use EasyTOTP\TOTPValidResultInterface;
use OCA\TwoFactorTOTP\Db\TotpSecret;
use OCA\TwoFactorTOTP\Db\TotpSecretMapper;
use OCA\TwoFactorTOTP\Event\DisabledByAdmin;
use OCA\TwoFactorTOTP\Event\StateChanged;
use OCA\TwoFactorTOTP\Exception\NoTotpSecretFoundException;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Services\IAppConfig;
use OCP\EventDispatcher\IEventDispatcher;
use OCP\IUser;
use OCP\Security\ICrypto;
use OCP\Security\ISecureRandom;
use Override;

/** @psalm-api */
class Totp implements ITotp {

	public function __construct(
		private readonly TotpSecretMapper $secretMapper,
		private readonly ICrypto $crypto,
		private readonly IEventDispatcher $eventDispatcher,
		private readonly ISecureRandom $random,
		private readonly IAppConfig $appConfig,
	) {
	}

	private function getSecretLength(): int {
		$length = $this->appConfig->getAppValueInt('secret_length', ITotp::DEFAULT_SECRET_LENGTH);
		if ($length >= ITotp::MIN_SECRET_LENGTH && $length <= ITotp::MAX_SECRET_LENGTH) {
			return $length;
		}
		return ITotp::DEFAULT_SECRET_LENGTH;
	}

	private function getDefaultAlgorithm(): string {
		$algorithm = $this->appConfig->getAppValueString('algorithm', ITotp::DEFAULT_ALGORITHM);
		$valid = [
			ITotp::ALGORITHM_SHA1,
			ITotp::ALGORITHM_SHA256,
			ITotp::ALGORITHM_SHA512,
		];
		return in_array($algorithm, $valid, true) ? $algorithm : ITotp::DEFAULT_ALGORITHM;
	}

	#[Override]
	public function hasSecret(IUser $user): bool {
		try {
			$secret = $this->secretMapper->getSecret($user);
			return (int)$secret->getState() === ITotp::STATE_ENABLED;
		} catch (DoesNotExistException) {
			return false;
		}
	}

	private function generateSecret(): string {
		return $this->random->generate($this->getSecretLength(), ISecureRandom::CHAR_UPPER . '234567');
	}

	/**
	 * @param IUser $user
	 */
	#[Override]
	public function createSecret(IUser $user): string {
		try {
			// Delete existing one
			$oldSecret = $this->secretMapper->getSecret($user);
			$this->secretMapper->delete($oldSecret);
		} catch (DoesNotExistException) {
			// Ignore
		}

		// Create new one
		$secret = $this->generateSecret();

		$dbSecret = new TotpSecret();
		$dbSecret->setUserId($user->getUID());
		$dbSecret->setSecret($this->crypto->encrypt($secret));
		$dbSecret->setState(ITotp::STATE_CREATED);
		$dbSecret->setAlgorithm($this->getDefaultAlgorithm());

		$this->secretMapper->insert($dbSecret);
		return $secret;
	}

	#[Override]
	public function getSecret(IUser $user): TotpSecret {
		try {
			return $this->secretMapper->getSecret($user);
		} catch (DoesNotExistException $e) {
			throw new NoTotpSecretFoundException(
				$e->getMessage(),
				$e->getCode(),
				$e,
			);
		}
	}

	#[Override]
	public function enable(IUser $user, $key): bool {
		$dbSecret = $this->secretMapper->getSecret($user);
		if (!$this->validateSecret($dbSecret, $key)) {
			return false;
		}
		$dbSecret->setState(ITotp::STATE_ENABLED);
		$this->secretMapper->update($dbSecret);

		$this->eventDispatcher->dispatch(StateChanged::class, new StateChanged($user, true));

		return true;
	}

	#[Override]
	public function deleteSecret(IUser $user, bool $byAdmin = false): void {
		try {
			// TODO: execute DELETE sql in mapper instead
			$dbSecret = $this->secretMapper->getSecret($user);
			$this->secretMapper->delete($dbSecret);
		} catch (DoesNotExistException) {
			// Ignore
		}

		if ($byAdmin) {
			$this->eventDispatcher->dispatch(DisabledByAdmin::class, new DisabledByAdmin($user));
		} else {
			$this->eventDispatcher->dispatch(StateChanged::class, new StateChanged($user, false));
		}
	}

	#[Override]
	public function validateSecret(TotpSecret $secret, string $key): bool {
		$decryptedSecret = $this->crypto->decrypt($secret->getSecret());
		$algorithm = $secret->getAlgorithm() ?: ITotp::DEFAULT_ALGORITHM;
		$otp = Factory::getTOTP(Base32::decode($decryptedSecret), 30, 6, 0, $algorithm);

		$counter = null;
		$lastCounter = $secret->getLastCounter();
		if ($lastCounter !== -1) {
			$counter = $lastCounter;
		}

		$result = $otp->verify($key, 3, $counter);
		if ($result instanceof TOTPValidResultInterface) {
			$secret->setLastCounter($result->getCounter());
			$this->secretMapper->update($secret);

			return true;
		}

		return false;
	}
}
