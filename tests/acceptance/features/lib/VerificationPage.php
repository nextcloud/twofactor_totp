<?php declare(strict_types=1);
/**
 * ownCloud
 *
 * @author Hari Bhandari <hari@jankaritech.com>
 * @copyright Copyright (c) 2019 Hari Bhandari hari@jankaritech.com
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

use Behat\Mink\Element\NodeElement;
use Behat\Mink\Session;

/**
 * Class VerificationPage
 *
 * @package Page
 */
class VerificationPage extends OwncloudPage {
	private $verificationFieldXpath = '//form/input[@name="challenge"]';
	private $verifySubmissionBtnXpath = '//form//button[@type="submit"]';
	private $errorTokenMessageXpath = '//div/span[contains(text(),"verifying the token")]';
	private $cancelOrLoginButtonXpath = '//a[@class="two-factor-cancel"]';

	/**
	 * there is no reliable loading indicator on the verification page, so just wait for
	 * the verification field to be there.
	 *
	 * @param Session $session
	 * @param int $timeout_msec
	 *
	 * @return void
	 * @throws \Exception
	 */
	public function waitTillPageIsLoaded(
		Session $session,
		int $timeout_msec = STANDARD_UI_WAIT_TIMEOUT_MILLISEC
	): void {
		$field = $this->waitTillElementIsNotNull($this->verificationFieldXpath);
		$this->assertElementNotNull(
			$field,
			__METHOD__ . ' Field for adding verification code could not be found'
		);

		$this->waitForOutstandingAjaxCalls($session);
	}

	/**
	 *
	 * @param string $key
	 *
	 * @return void
	 */
	public function addVerificationKey(string $key): void {
		$field = $this->waitTillElementIsNotNull($this->verificationFieldXpath);
		$field->setValue($key);
		$submit_btn = $this->find("xpath", $this->verifySubmissionBtnXpath);
		$submit_btn->click();
	}

	/**
	 *
	 * @return string
	 */
	public function getErrorMessage(): string {
		$errorMessageElement = $this->find(
			"xpath",
			$this->errorTokenMessageXpath
		);
		$this->assertElementNotNull(
			$errorMessageElement,
			__METHOD__ .
			" xpath $this->errorTokenMessageXpath" .
			" could not find token verification error message"
		);
		return $errorMessageElement->getText();
	}

	/**
	 *
	 * @return void
	 */
	public function cancelVerification(): void {
		$this->waitTillElementIsNotNull($this->cancelOrLoginButtonXpath);
		$cancel_btn = $this->find("xpath", $this->cancelOrLoginButtonXpath);
		$cancel_btn->click();
	}

	/**
	 *
	 * @return NodeElement|null
	 */
	public function isErrorMessagePresent(): ?NodeElement {
		return $this->find('xpath', $this->errorTokenMessageXpath);
	}
}
