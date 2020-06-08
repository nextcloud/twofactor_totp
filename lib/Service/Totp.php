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

use Base32\Base32;
use OCA\TwoFactor_Totp\Db\TotpSecret;
use OCA\TwoFactor_Totp\Db\TotpSecretMapper;
use OCA\TwoFactor_Totp\Exception\NoTotpSecretFoundException;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\IUser;
use OCP\Security\ICrypto;
use Otp\GoogleAuthenticator;
use Otp\Otp;

class Totp implements ITotp {

	/** @var TotpSecretMapper */
	private $secretMapper;

	/** @var ICrypto */
	private $crypto;

	/** @var Otp */
	private $otp;

	public function __construct(TotpSecretMapper $secretMapper, ICrypto $crypto, Otp $otp) {
		$this->secretMapper = $secretMapper;
		$this->crypto = $crypto;
		$this->otp = $otp;
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
	 *
	 * @param IUser $user
	 */
	public function createSecret(IUser $user) {
		$this->deleteSecret($user);
		$secret = GoogleAuthenticator::generateRandom();

		$dbSecret = new TotpSecret();
		$dbSecret->setUserId($user->getUID());
		$dbSecret->setSecret($this->crypto->encrypt($secret));
		$dbSecret->setVerified(false);
		$this->secretMapper->insert($dbSecret);

		return $secret;
	}

	/**
	 * @param IUser $user
	 */
	public function deleteSecret(IUser $user) {
		try {
			// TODO: execute DELETE sql in mapper instead
			$dbSecret = $this->secretMapper->getSecret($user);
			$this->secretMapper->delete($dbSecret);
		} catch (DoesNotExistException $ex) {
		}
	}

	/**
	 * @param IUser $user
	 * @param string $key
	 */
	public function verifySecret(IUser $user, $key) {
		if ($this->validateKey($user, $key) === true) {
			$dbSecret = $this->secretMapper->getSecret($user);
			$dbSecret->setVerified(true);
			$this->secretMapper->update($dbSecret);
			return true;
		}
		return false;
	}

	/**
	 * @param IUser $user
	 * @param string $key
	 * @return boolean
	 * @throws NoTotpSecretFoundException
	 */
	public function validateKey(IUser $user, $key) {
		try {
			$dbSecret = $this->secretMapper->getSecret($user);
		} catch (DoesNotExistException $ex) {
			throw new NoTotpSecretFoundException();
		}

		/**
		 * Check last validated key to prevent consecutive usage of the same key
		 */
		if ($dbSecret->getLastValidatedKey() !== $key) {
			$secret = $this->crypto->decrypt($dbSecret->getSecret());
			/* @phpstan-ignore-next-line */
			if ($this->otp->checkTotp(Base32::decode($secret), $key, 3) === true) {
				$dbSecret->setLastValidatedKey($key);
				$this->secretMapper->update($dbSecret);
				return true;
			}
		}
		return false;
	}

	/**
	 * @param IUser $user
	 * @return boolean
	 */
	public function isVerified(IUser $user) {
		try {
			$secret = $this->secretMapper->getSecret($user);
			return $secret->getVerified();
		} catch (DoesNotExistException $ex) {
			return false;
		}
	}
}
