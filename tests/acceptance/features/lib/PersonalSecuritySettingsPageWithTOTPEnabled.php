<?php
/**
 * ownCloud
 *
 * @author Saugat Pachhai <saugat@jankaritech.com>
 * @copyright Copyright (c) 2019 Saugat Pachhai saugat@jankaritech.com
 *
 * This code is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License,
 * as published by the Free Software Foundation;
 * either version 3 of the License, or any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>
 *
 */

namespace Page;

/**
 * Class PersonalSecuritySettingsPageWithTOTPEnabled
 *
 * @package Page
 */
class PersonalSecuritySettingsPageWithTOTPEnabled extends PersonalSecuritySettingsPage {
	private $activateTOTPLabelXpath = '//label[@for="totp-enabled"]';
	private $qrCodeImageXpath = '//div[@id="twofactor-totp-settings"]//img';
	private $secretCodeXpath = '//div[@id="twofactor-totp-settings"]//div/span';
	private $verificationFieldXpath = '//input[@id="totp-challenge"]';
	private $totpVerifyMsgXpath = '//span[@id="totp-verify-msg"]';
	private $verifySubmissionBtnXpath = '//button[@id="totp-verify-secret"]';

	/**
	 * Activate TOTP for the user
	 *
	 * @return void
	 */
	public function activateTOTP() {
		$label = $this->waitTillElementIsNotNull($this->activateTOTPLabelXpath);
		$this->assertElementNotNull(
			$label,
			__METHOD__ . " Label not found to activate TOTP"
		);
		$label->click();
	}

	/**
	 * Returns QR Code image in base64
	 *
	 * @return string
	 */
	public function getQRCode() {
		$image = $this->waitTillElementIsNotNull($this->qrCodeImageXpath);
		$this->assertElementNotNull(
			$image,
			__METHOD__ . ' QR code not found on the webUI'
		);
		return $image->getAttribute("src");
	}

	/**
	 * Returns secret code from the activity page
	 *
	 * @return string
	 */
	public function getSecretCode() {
		$secret = $this->waitTillElementIsNotNull($this->secretCodeXpath);
		$this->assertElementNotNull(
			$secret,
			__METHOD__ . ' Secret code not found on the webUI'
		);
		$parts = \explode(':', $secret->getText());
		return \trim($parts[1]);
	}

	/**
	 * Verifies 2fa by filling the $key
	 *
	 * @param string $key
	 *
	 * @return void
	 */
	public function addVerificationKey($key) {
		$field = $this->waitTillElementIsNotNull($this->verificationFieldXpath);
		$this->assertElementNotNull(
			$field,
			__METHOD__ . ' Field for adding verification code could not be found'
		);
		$field->setValue($key);

		$submit_btn = $this->find("xpath", $this->verifySubmissionBtnXpath);
		$this->assertElementNotNull(
			$submit_btn,
			__METHOD__ . ' Button for code submission could not be found'
		);
		$submit_btn->click();
	}

	/**
	 * Returns true if verified
	 *
	 * The message remains visible for a few seconds only. So, we need to call
	 * this just after calling addVerificationKey method.
	 *
	 * @return bool
	 */
	public function isKeyVerified() {
		$verificationMsg = $this->waitTillElementIsNotNull($this->totpVerifyMsgXpath);
		$this->assertElementNotNull(
			$verificationMsg,
			__METHOD__ . ' The verification msg could not be found'
		);
		return ($verificationMsg->getText() === 'Verified');
	}
}
