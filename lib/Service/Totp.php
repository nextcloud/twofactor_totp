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
use OCP\EventDispatcher\IEventDispatcher;
use OCP\IUser;
use OCP\Security\ICrypto;
use OCP\Security\ISecureRandom;

class Totp implements ITotp {

	public function __construct(
		private TotpSecretMapper $secretMapper,
		private ICrypto $crypto,
		private IEventDispatcher $eventDispatcher,
		private ISecureRandom $random,
	) {
	}

	public function hasSecret(IUser $user): bool {
		try {
			$secret = $this->secretMapper->getSecret($user);
			return (int)$secret->getState() === ITotp::STATE_ENABLED;
		} catch (DoesNotExistException $ex) {
			return false;
		}
	}

	private function generateSecret(): string {
		return $this->random->generate(32, ISecureRandom::CHAR_UPPER . '234567');
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
		$otp = Factory::getTOTP(Base32::decode($secret), 30, 6);

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
