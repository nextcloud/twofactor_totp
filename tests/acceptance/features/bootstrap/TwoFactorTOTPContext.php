<?php
/**
 * ownCloud
 *
 * @author Saugat Pachhai <saugat@jankaritech.com>
 * @copyright Copyright (c) 2019 Saugat Pachhai saugat@jankaritech.com
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU AFFERO GENERAL PUBLIC LICENSE for more details.
 *
 * You should have received a copy of the GNU Affero General Public
 * License along with this library.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

use Behat\Behat\Context\Context;
use Page\PersonalSecuritySettingsPageWithTOTPEnabled;
use Otp\Otp;
use Base32\Base32;
use PHPUnit\Framework\Assert;

require_once 'bootstrap.php';

/**
 * Context for two factor totp app
 */
class TwoFactorTOTPContext implements Context {
	/**
	 * @var PersonalSecuritySettingsPageWithTOTPEnabled
	 */
	private $personalSecuritySettingsPage;

	/**
	 * @var bool
	 */
	private $totpUsed = false;

	/**
	 * @var string
	 */
	private $totpSecret;

	/**
	 * Returns secret key
	 *
	 * @return string
	 */
	private function getSecret() {
		return $this->totpSecret;
	}

	/**
	 * Generate One time key for login from secret
	 *
	 * @return string
	 */
	private function generateTOTPKey() {
		$otp = new Otp();
		return $otp->totp(Base32::decode($this->getSecret()));
	}
	/**
	 * WebUIPersonalSecuritySettingsTOTPEnabledContext constructor.
	 *
	 * @param PersonalSecuritySettingsPageWithTOTPEnabled $personalSecuritySettingsPage
	 */
	public function __construct(
		PersonalSecuritySettingsPageWithTOTPEnabled $personalSecuritySettingsPage
	) {
		// $personalSecuritySettingsPage is private, therefore needs to be overridden
		$this->personalSecuritySettingsPage = $personalSecuritySettingsPage;
	}

	/**
	 * @Given /^the user has activated TOTP Second\-factor auth but not verified$/
	 * @When /^the user activates TOTP Second\-factor auth but does not verify$/
	 *
	 * @return void
	 */
	public function theUserHasActivatedTOTPSecondFactorAuthButNotVerified() {
		$this->personalSecuritySettingsPage->activateTOTP();
		$this->totpSecret = $this->personalSecuritySettingsPage->getSecretCode();
	}

	/**
	 * Returns secret code extracted from QRCode
	 *
	 * @return string
	 */
	public function getSecretCodeFromQRCode() {
		$path = \tempnam(\sys_get_temp_dir(), 'totp_qrcode');
		$data = \explode(',', $this->personalSecuritySettingsPage->getQRCode());
		$file = \fopen($path, 'wb');
		\fwrite($file, \base64_decode($data[1]));
		$qrCode = new QrReader($path);
		\preg_match('/secret=(?P<secret>[A-Z0-9]{16})/', $qrCode->text(), $matches);
		return $matches['secret'];
	}

	/**
	 * @When the user adds one-time key generated from the secret key using the webUI
	 *
	 * @throws \Exception
	 *
	 * @return void
	 */
	public function theUserAddsVerificationKeyFromSecretKeyToVerifyUsingWebUI() {
		if (!$this->totpUsed) {
			$this->personalSecuritySettingsPage->addVerificationKey(
				$this->generateTOTPKey()
			);
			$this->totpUsed = true;
		} else {
			throw new \Exception(
				'TOTP Already used.' .
				'Key generation multiple times is not supported ' .
				'due to the possibility of same key generation.'
			);
		}
	}

	/**
	 * @Then the TOTP secret key should be verified on the webUI
	 *
	 * @return void
	 */
	public function totpSecretKeyShouldBeVerifiedOnTheWebUI() {
		Assert::assertTrue(
			$this->personalSecuritySettingsPage->isKeyVerified(),
			'The key could not be verified'
		);
	}

	/**
	 * @Then /^the secret code from QR code should match with the one displayed on the webUI$/
	 *
	 * @return void
	 */
	public function theSecretCodeFromQRCodeShouldMatchWithTheOneDisplayedOnTheWebUI() {
		Assert::assertEquals(
			$this->getSecretCodeFromQRCode(),
			$this->personalSecuritySettingsPage->getSecretCode()
		);
	}
}
