<?php declare(strict_types=1);
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
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Otp\GoogleAuthenticator;
use Page\PersonalSecuritySettingsPageWithTOTPEnabled;
use Otp\Otp;
use ParagonIE\ConstantTime\Encoding;
use PHPUnit\Framework\Assert;
use Page\VerificationPage;
use TestHelpers\OcsApiHelper;
use Zxing\QrReader;

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
	 * @var string|null
	 */
	private $totpSecret;

	/**
	 *
	 * @var featureContext
	 */
	private $featureContext;

	/**
	 *
	 * @var WebUIGeneralContext
	 */
	private $webUIGeneralContext;

	/**
	 *
	 * @var VerificationPage
	 */
	private $verificationPage;

	/**
	 * @var int
	 */
	private $counter;

	/**
	 * @var int
	 */
	private $timeCounter = null;

	/**
	 * Returns secret key
	 *
	 * @return string
	 */
	private function getSecret(): ?string {
		return $this->totpSecret;
	}

	/**
	 * Generate One time key for login from secret
	 *
	 * @return string
	 * @throws Exception
	 */
	private function generateTOTPKey(): string {
		$otp = new Otp();
		if ($this->timeCounter === null) {
			$this->timeCounter = \floor(\time() / 30);
		} else {
			$this->timeCounter += 1;
		}
		$this->counter += 1;
		if ($this->counter > 2) {
			throw new \Exception(
				'Key used more than twice'
			);
		}
		if ($this->getSecret() === null) {
			$this->totpSecret = GoogleAuthenticator::generateRandom();
		}
		return $otp->totp(Encoding::base32DecodeUpper($this->getSecret()), $this->timeCounter);
	}

	/**
	 * WebUIPersonalSecuritySettingsTOTPEnabledContext constructor.
	 *
	 * @param PersonalSecuritySettingsPageWithTOTPEnabled $personalSecuritySettingsPage
	 * @param VerificationPage $verificationPage
	 */
	public function __construct(
		PersonalSecuritySettingsPageWithTOTPEnabled $personalSecuritySettingsPage,
		VerificationPage $verificationPage
	) {
		// $personalSecuritySettingsPage is private, therefore needs to be overridden
		$this->personalSecuritySettingsPage = $personalSecuritySettingsPage;

		$this->verificationPage = $verificationPage;
	}

	/**
	 * @Given /^the user has activated TOTP Second\-factor auth but not verified$/
	 * @When /^the user activates TOTP Second\-factor auth but does not verify$/
	 *
	 * @return void
	 */
	public function theUserHasActivatedTOTPSecondFactorAuthButNotVerified(): void {
		$this->personalSecuritySettingsPage->activateTOTP();
		$this->totpSecret = $this->personalSecuritySettingsPage->getSecretCode();
	}

	/**
	 * Returns secret code extracted from QRCode
	 *
	 * @return string
	 */
	public function getSecretCodeFromQRCode(): string {
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
	public function theUserAddsVerificationKeyFromSecretKeyToVerifyUsingWebUI(): void {
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
	 * @When the user re-logs in as :username to the two-factor authentication verification page
	 *
	 * @param string $username
	 *
	 * @return void
	 * @throws Exception
	 */
	public function theUserReLogsInAsForTwoFactorAuthentication(string $username): void {
		$this->webUIGeneralContext->theUserLogsOutOfTheWebUI();
		$password = $this->featureContext->getPasswordForUser($username);

		$this->webUIGeneralContext->loginAs($username, $password, $target = 'VerificationPage');
	}

	/**
	 * @When the user adds one-time key :key on the verification page on the webUI
	 *
	 * @param string $key
	 *
	 * @return void
	 */
	public function theUserAddsOneTimeKeyOnTheVerificationPageOnTheWebui(string $key): void {
		$this->verificationPage->addVerificationKey($key);
	}

	/**
	 * @Then the user should see an error message on the verification page saying :message
	 *
	 * @param string $message
	 *
	 * @return void
	 */
	public function theUserShouldSeeAnErrorMessageOnTheVerificationPageSaying(string $message): void {
		$errormessage = $this->verificationPage->getErrorMessage();
		PHPUnit\Framework\Assert::assertEquals($message, $errormessage);
	}

	/**
	 * @When the user cancels the verification using the webUI
	 *
	 * @return void
	 */
	public function theUserCancelsTheVerificationUsingTheWebui(): void {
		$this->verificationPage->cancelVerification();
	}

	/**
	 * @When the user adds one-time key generated from the secret key on the verification page on the webUI
	 *
	 * @return void
	 * @throws Exception
	 */
	public function theUserAddsOneTimeKeyGeneratedOnVerificationPageOnTheWebui(): void {
		$key = $this->generateTOTPKey();
		$this->verificationPage->addVerificationKey($key);
		$response = $this->verificationPage->isErrorMessagePresent();
		if ($response) {
			throw new \Exception("Recently added verification key is not verified");
		}
	}

	/**
	 * @Then the TOTP secret key should be verified on the webUI
	 *
	 * @return void
	 */
	public function totpSecretKeyShouldBeVerifiedOnTheWebUI(): void {
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
	public function theSecretCodeFromQRCodeShouldMatchWithTheOneDisplayedOnTheWebUI(): void {
		Assert::assertEquals(
			$this->getSecretCodeFromQRCode(),
			$this->personalSecuritySettingsPage->getSecretCode()
		);
	}

	/**
	 * Send request with secret key for two factor authentication
	 *
	 * @param string $user
	 * @param string $secretKey
	 *
	 * @return void
	 */
	public function sendRequestWithSecretKey($user, $secretKey): void {
		$response = OcsApiHelper::sendRequest(
			$this->featureContext->getBaseUrl(),
			$this->featureContext->getAdminUsername(),
			$this->featureContext->getAdminPassword(),
			'GET',
			"/apps/twofactor_totp/api/v1/validate/$user/$secretKey",
			$this->featureContext->getStepLineRef(),
			[],
			1
		);
		$this->featureContext->setResponse($response);
	}

	/**
	 * @When the administrator tries to verify with the one-time key generated from the secret key for user :user
	 *
	 * @param string $user
	 *
	 * @return void
	 * @throws Exception
	 */
	public function theAdministratorTriesToVerifyTheOtpKeyForUserUsingTheCorrectKey(string $user): void {
		$secretKey = $this->generateTOTPKey();
		$this->sendRequestWithSecretKey($user, $secretKey);
	}

	/**
	 * @Then the result of the last verification request should be true
	 *
	 * @throws \Exception
	 *
	 * @return void
	 */
	public function theResultOfTheLastVerificationRequestShouldBeTrue(): void {
		$result = \json_decode(
			\json_encode(
				$this->featureContext->getResponseXml()->data[0]
			),
			true
		)['result'];
		if ((int)$result !== 1) {
			throw new \Exception(
				"Invalid OTP"
			);
		}
	}

	/**
	 * @When the administrator tries to verify with an invalid key :secretKey for user :user
	 *
	 * @param string $secretKey
	 * @param string $user
	 *
	 * @return void
	 */
	public function theAdministratorTriesToVerifyTheOtpKeyForUserUsingTheWrongKey(
		string $secretKey,
		string $user
	): void {
		$this->sendRequestWithSecretKey($user, $secretKey);
	}

	/**
	 * @Then the result of the last verification request should be false
	 *
	 * @throws \Exception
	 *
	 * @return void
	 */
	public function theResultOfTheLastVerificationRequestShouldBeFalse(): void {
		$result = \json_decode(
			\json_encode(
				$this->featureContext->getResponseXml()->data[0]
			),
			true
		);
		if (\sizeof($result['result']) !== 0) {
			throw new \Exception(
				"Valid OTP"
			);
		}
	}

	/**
	 * This will run before EVERY scenario.
	 * It will set the properties for this object.
	 *
	 * @BeforeScenario @webUI
	 *
	 * @param BeforeScenarioScope $scope
	 *
	 * @return void
	 */
	public function before(BeforeScenarioScope $scope): void {
		// Get the environment
		$environment = $scope->getEnvironment();
		// Get all the contexts you need in this context
		$this->featureContext = $environment->getContext('FeatureContext');
		$this->webUIGeneralContext = $environment->getContext('WebUIGeneralContext');
	}
}
