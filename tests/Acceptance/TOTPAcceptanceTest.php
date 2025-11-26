<?php

declare(strict_types=1);

/*
 * SPDX-FileCopyrightText: 2017 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-only
 */

namespace OCA\TwoFactorTOTP\Tests\Acceptance;

use Base32\Base32;
use ChristophWurst\Nextcloud\Testing\Selenium;
use ChristophWurst\Nextcloud\Testing\TestCase;
use ChristophWurst\Nextcloud\Testing\TestUser;
use Facebook\WebDriver\Exception\ElementNotInteractableException;
use Facebook\WebDriver\WebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use OC;
use OCA\TwoFactorTOTP\Db\TotpSecret;
use OCA\TwoFactorTOTP\Db\TotpSecretMapper;
use OCA\TwoFactorTOTP\Provider\TotpProvider;
use OCA\TwoFactorTOTP\Service\ITotp;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\Authentication\TwoFactorAuth\IRegistry;
use OCP\IUser;
use Otp\GoogleAuthenticator;
use Otp\Otp;

/**
 * @group Acceptance
 */
class TOTPAcceptanceTest extends TestCase {
	use TestUser;
	use Selenium;

	/** @var IUser */
	private $user;

	/** @var TotpSecretMapper */
	private $secretMapper;

	public function setUp(): void {
		parent::setUp();

		$this->user = $this->createTestUser();
		$this->secretMapper = new TotpSecretMapper(OC::$server->getDatabaseConnection());
	}

	public function testEnableTOTP(): void {
		$this->webDriver->get('http://localhost:8080/index.php/login');
		$this->assertStringContainsString('Nextcloud', $this->webDriver->getTitle());

		// Log in
		$this->webDriver->findElement(WebDriverBy::id('user'))->sendKeys($this->user->getUID());
		$this->webDriver->findElement(WebDriverBy::id('password'))->sendKeys('password');
		$this->webDriver->findElement(WebDriverBy::cssSelector('form[name=login] [type=submit]'))->click();

		// Go to personal settings and TOTP settings
		$this->webDriver->get('http://localhost:8080/index.php/settings/user/security');

		// Enable TOTP
		// Wait for state being loaded from the server
		$this->webDriver->wait(20, 200)->until(function (WebDriver $driver) {
			try {
				return count($driver->findElements(WebDriverBy::id('totp-enabled'))) > 0;
			} catch (ElementNotInteractableException) {
				return false;
			}
		});
		$this->webDriver->executeScript('arguments[0].click(); console.log(arguments[0]);', [
			$this->webDriver->findElement(WebDriverBy::id('totp-enabled')),
		]);
		$this->webDriver->wait(15, 200)->until(WebDriverExpectedCondition::elementTextContains(WebDriverBy::id('twofactor-totp-settings'), 'Your new TOTP secret is:'));
		$this->assertHasSecret(ITotp::STATE_CREATED);

		// Enter a wrong OTP
		$this->webDriver->findElement(WebDriverBy::id('totp-confirmation'))->sendKeys('000000');
		$this->webDriver->findElement(WebDriverBy::id('totp-confirmation-submit'))->click();

		// Wait for the notification
		// TODO: this was replaced with toastify https://github.com/nextcloud/server/pull/15124
		// $this->webDriver->wait(15, 200)->until(WebDriverExpectedCondition::elementTextContains(WebDriverBy::id('notification'), 'Could not verify your key. Please try again'));

		// Enter a correct OTP
		$this->webDriver->findElement(WebDriverBy::id('totp-confirmation'))->sendKeys($this->getValidTOTP());
		$this->webDriver->findElement(WebDriverBy::id('totp-confirmation-submit'))->click();

		// Try to locate checked checkbox
		$this->webDriver->wait(20, 200)->until(function (WebDriver $driver) {
			try {
				return $driver->findElement(WebDriverBy::id('totp-enabled'))->getAttribute('checked') === 'true';
			} catch (ElementNotInteractableException) {
				return false;
			}
		});
		$this->assertHasSecret(ITotp::STATE_ENABLED);
	}

	private function assertHasSecret($state): void {
		try {
			$secret = $this->secretMapper->getSecret($this->user);
			if ($state !== (int)$secret->getState()) {
				self::fail('TOTP secret has wrong state');
			}
		} catch (DoesNotExistException) {
			self::fail('User does not have a totp secret');
		}
	}

	private function getValidTOTP(): string {
		$dbSecret = $this->secretMapper->getSecret($this->user);
		$secret = OC::$server->getCrypto()->decrypt($dbSecret->getSecret());
		$otp = new Otp();
		return $otp->totp(Base32::decode($secret));
	}

	private function createSecret(): string {
		$secret = GoogleAuthenticator::generateRandom();
		$dbsecret = new TotpSecret();
		$dbsecret->setSecret(OC::$server->getCrypto()->encrypt($secret));
		$dbsecret->setUserId($this->user->getUID());
		$this->secretMapper->insert($dbsecret);

		/** @var IRegistry $registry */
		$registry = OC::$server->query(IRegistry::class);
		/** @var TotpProvider $provider */
		$provider = OC::$server->query(TotpProvider::class);
		$registry->enableProviderFor($provider, $this->user);

		return $secret;
	}

	public function testLoginShouldFailWithWrongOTP(): void {
		$this->createSecret();

		$this->webDriver->get('http://localhost:8080/index.php/login');
		$this->assertStringContainsString('Nextcloud', $this->webDriver->getTitle());

		// Log in
		$this->webDriver->findElement(WebDriverBy::id('user'))->sendKeys($this->user->getUID());
		$this->webDriver->findElement(WebDriverBy::id('password'))->sendKeys('password');
		$this->webDriver->findElement(WebDriverBy::cssSelector('form[name=login] [type=submit]'))->click();

		$this->webDriver->wait(20, 200)->until(function (WebDriver $driver) {
			try {
				return $driver->findElements(WebDriverBy::className('totp-form'));
			} catch (ElementNotInteractableException) {
				return false;
			}
		});

		// Enter a wrong OTP
		$this->webDriver->findElement(WebDriverBy::name('challenge'))->sendKeys('000000');
		$this->webDriver->findElement(WebDriverBy::cssSelector('button[type="submit"]'))->submit();

		$this->webDriver->wait(20, 200)->until(WebDriverExpectedCondition::elementTextContains(WebDriverBy::className('body-login-container'), 'Error while validating your second factor'));

		$this->assertEquals('http://localhost:8080/index.php/login/challenge/totp', $this->webDriver->getCurrentURL());
	}
}
