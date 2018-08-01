<?php

/**
 * @author Christoph Wurst <christoph@winzerhof-wurst.at>
 * @copyright Copyright (c) 2017 Christoph Wurst <christoph@winzerhof-wurst.at>
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

namespace OCA\TwoFactorTOTP\Tests\Acceptance;

use Base32\Base32;
use ChristophWurst\Nextcloud\Testing\Selenium;
use ChristophWurst\Nextcloud\Testing\TestCase;
use ChristophWurst\Nextcloud\Testing\TestUser;
use Facebook\WebDriver\Exception\ElementNotSelectableException;
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
use PHPUnit_Framework_AssertionFailedError;

/**
 * @group Acceptance
 */
class TOTPAcceptenceTest extends TestCase {

	use TestUser;
	use Selenium;

	/** @var IUser */
	private $user;

	/** @var TotpSecretMapper */
	private $secretMapper;

	public function setUp() {
		parent::setUp();

		$this->user = $this->createTestUser();
		$this->secretMapper = new TotpSecretMapper(OC::$server->getDatabaseConnection());
	}

	public function testEnableTOTP() {
		$this->webDriver->get('http://localhost:8080/index.php/login');
		$this->assertContains('Nextcloud', $this->webDriver->getTitle());

		// Log in
		$this->webDriver->findElement(WebDriverBy::id('user'))->sendKeys($this->user->getUID());
		$this->webDriver->findElement(WebDriverBy::id('password'))->sendKeys('password');
		$this->webDriver->findElement(WebDriverBy::cssSelector('form[name=login] input[type=submit]'))->click();

		// Go to personal settings and TOTP settings
		$this->webDriver->get('http://localhost:8080/index.php/settings/user/security');

		// Enable TOTP
		// Wait for state being loaded from the server
		$this->webDriver->wait(20, 200)->until(function (WebDriver $driver) {
			try {
				return count($driver->findElements(WebDriverBy::id('totp-enabled'))) > 0;
			} catch (ElementNotSelectableException $ex) {
				return false;
			}
		});
		$this->webDriver->executeScript('arguments[0].click(); console.log(arguments[0]);', [
			$this->webDriver->findElement(WebDriverBy::id('totp-enabled')),
		]);
		$this->webDriver->wait(15, 200)->until(WebDriverExpectedCondition::elementTextContains(WebDriverBy::id('twofactor-totp-settings'), 'This is your new TOTP secret:'));
		$this->assertHasSecret(ITotp::STATE_CREATED);

		// Enter a wrong OTP
		$this->webDriver->findElement(WebDriverBy::id('totp-confirmation'))->sendKeys('000000');
		$this->webDriver->findElement(WebDriverBy::id('totp-confirmation-submit'))->click();

		// Wait for the notification
		$this->webDriver->wait(15, 200)->until(WebDriverExpectedCondition::elementTextContains(WebDriverBy::id('notification'), 'Could not verify your key. Please try again'));

		// Enter a correct OTP
		$this->webDriver->findElement(WebDriverBy::id('totp-confirmation'))->sendKeys($this->getValidTOTP());
		$this->webDriver->findElement(WebDriverBy::id('totp-confirmation-submit'))->click();

		// Try to locate checked checkbox
		$this->webDriver->wait(20, 200)->until(function (WebDriver $driver) {
			try {
				return $driver->findElement(WebDriverBy::id('totp-enabled'))->getAttribute('checked') === 'true';
			} catch (ElementNotSelectableException $ex) {
				return false;
			}
		});
		$this->assertHasSecret(ITotp::STATE_ENABLED);
	}

	private function assertHasSecret($state) {
		try {
			$secret = $this->secretMapper->getSecret($this->user);
			if ($state !== (int)$secret->getState()) {
				throw new PHPUnit_Framework_AssertionFailedError('TOTP secret has wrong state');
			}
		} catch (DoesNotExistException $ex) {
			throw new PHPUnit_Framework_AssertionFailedError('User does not have a totp secret');
		}
	}

	private function getValidTOTP() {
		$dbSecret = $this->secretMapper->getSecret($this->user);
		$secret = OC::$server->getCrypto()->decrypt($dbSecret->getSecret());
		$otp = new Otp();
		return $otp->totp(Base32::decode($secret));
	}

	private function createSecret() {
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

	public function testLoginShouldFailWithWrongOTP() {
		$this->createSecret();

		$this->webDriver->get('http://localhost:8080/index.php/login');
		$this->assertContains('Nextcloud', $this->webDriver->getTitle());

		// Log in
		$this->webDriver->findElement(WebDriverBy::id('user'))->sendKeys($this->user->getUID());
		$this->webDriver->findElement(WebDriverBy::id('password'))->sendKeys('password');
		$this->webDriver->findElement(WebDriverBy::cssSelector('form[name=login] input[type=submit]'))->click();

		$this->webDriver->wait(20, 200)->until(function (WebDriver $driver) {
			try {
				return $driver->findElements(WebDriverBy::className('totp-form'));
			} catch (ElementNotSelectableException $ex) {
				return false;
			}
		});

		// Enter a wrong OTP
		$this->webDriver->findElement(WebDriverBy::name('challenge'))->sendKeys('000000');
		$this->webDriver->findElement(WebDriverBy::cssSelector('button[type="submit"]'))->submit();

		$this->webDriver->wait(20, 200)->until(WebDriverExpectedCondition::elementTextContains(WebDriverBy::className('warning'), 'Error while validating your second factor'));

		$this->assertEquals('http://localhost:8080/index.php/login/challenge/totp', $this->webDriver->getCurrentURL());
	}

}
