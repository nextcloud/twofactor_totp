<?php

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

use Base32\Base32;
use OCA\TwoFactorTOTP\Db\TotpSecret;
use OCA\TwoFactorTOTP\Db\TotpSecretMapper;
use OCA\TwoFactorTOTP\Exception\NoTotpSecretFoundException;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\IUser;
use OCP\Security\ICrypto;
use Otp\GoogleAuthenticator;
use Otp\Otp;
use OCP\Activity\IManager as ActivityManager;

class Totp implements ITotp {

	/** @var TotpSecretMapper */
	private $secretMapper;

	/** @var ICrypto */
	private $crypto;

	/** @var ActivityManager */
	private $activityManager;

	public function __construct(TotpSecretMapper $secretMapper, ICrypto $crypto, ActivityManager $activityManager) {
		$this->secretMapper = $secretMapper;
		$this->crypto = $crypto;
		$this->activityManager = $activityManager;
	}

	public function hasSecret(IUser $user) {
		try {
			$this->secretMapper->getSecret($user);
		} catch (DoesNotExistException $ex) {
			return false;
		}
		return true;
	}

	/**
	 * @todo prevent duplicates
	 *
	 * @param IUser $user
	 */
	public function createSecret(IUser $user) {
		$secret = GoogleAuthenticator::generateRandom();

		$dbSecret = new TotpSecret();
		$dbSecret->setUserId($user->getUID());
		$dbSecret->setSecret($this->crypto->encrypt($secret));

		$this->secretMapper->insert($dbSecret);
		$this->publishEvent($user, 'totp_enabled');

		return $secret;
	}

	/**
	 * Push an TOTP event the user's activity stream
	 *
	 * @param IUser $user
	 * @param string $event
	 */
	private function publishEvent(IUser $user, $event) {

		$activity = $this->activityManager->generateEvent();
		$activity->setApp('twofactor_totp')
			->setType('twofactor_totp')
			->setAuthor($user->getUID())
			->setAffectedUser($user->getUID())
			->setMessage($event);
		$activity->setSubject($event . '_subject');
		$this->activityManager->publish($activity);
	}

	public function deleteSecret(IUser $user) {
		try {
			// TODO: execute DELETE sql in mapper instead
			$dbSecret = $this->secretMapper->getSecret($user);
			$this->secretMapper->delete($dbSecret);
		} catch (DoesNotExistException $ex) {
			// Ignore
		}
		$this->publishEvent($user, 'totp_disabled');
	}

	public function validateSecret(IUser $user, $key) {
		try {
			$dbSecret = $this->secretMapper->getSecret($user);
		} catch (DoesNotExistException $ex) {
			throw new NoTotpSecretFoundException();
		}

		$secret = $this->crypto->decrypt($dbSecret->getSecret());

		$otp = new Otp();
		$valid = $otp->checkTotp(Base32::decode($secret), $key, 3);

		if ($valid) {
			$this->publishEvent($user, 'totp_success');
		} else {
			$this->publishEvent($user, 'totp_error');
		}

		return $valid;
	}

}
