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

use Facebook\WebDriver\Exception\ElementNotSelectableException;
use Facebook\WebDriver\WebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use OC;
use OCA\TwoFactorTOTP\Db\TotpSecret;
use OCA\TwoFactorTOTP\Db\TotpSecretMapper;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\IUser;
use Otp\GoogleAuthenticator;

/**
 * @group Acceptance
 */
class TOTPAcceptenceTest extends AcceptanceTest {

	/** @var IUser */
	private $user;

	/** @var TotpSecretMapper */
	private $secretMapper;

	public function setUp() {
		parent::setUp();

		$this->user = OC::$server->getUserManager()->get('admin');
		$this->secretMapper = new TotpSecretMapper(OC::$server->getDatabaseConnection());
	}

	protected function tearDown() {
		parent::tearDown();

		// Always delete secret again
		try {
			$secret = $this->secretMapper->getSecret($this->user);
			if (!is_null($secret)) {
				$this->secretMapper->delete($secret);
			}
		} catch (DoesNotExistException $ex) {
			// Ignore
		}
	}

	public function testEnableTOTP() {
		$this->webDriver->get('http://localhost:8080/index.php/login');
		$this->assertContains('Nextcloud', $this->webDriver->getTitle());

		// Log in
		$this->webDriver->findElement(WebDriverBy::id('user'))->sendKeys('admin');
		$this->webDriver->findElement(WebDriverBy::id('password'))->sendKeys('admin');
		$this->webDriver->findElement(WebDriverBy::cssSelector('form[name=login] input[type=submit]'))->click();

		// Go to personal settings
		$this->webDriver->wait(20, 1000)->until(WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::id('expandDisplayName')));
		$this->webDriver->findElement(WebDriverBy::id('expandDisplayName'))->click();
		$this->webDriver->findElement(WebDriverBy::linkText('Personal'))->click();

		// Go to TOTP settings
		$this->webDriver->wait(20, 1000)->until(WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::linkText('TOTP second-factor auth')));
		$this->webDriver->findElement(WebDriverBy::linkText('TOTP second-factor auth'))->click();

		// Enable TOTP
		usleep(15 * 1000 * 1000); // Hard-coded sleep because the scripts need some time load the page
		$this->webDriver->wait(20, 1000)->until(function(WebDriver $driver) {
			try {
				return count($driver->findElements(WebDriverBy::id('totp-enabled'))) > 0;
			} catch (ElementNotSelectableException $ex) {
				return false;
			}
			return true;
		});
		$this->webDriver->executeScript('arguments[0].click(); console.log(arguments[0]);', [
		    $this->webDriver->findElement(WebDriverBy::id('totp-enabled')),
		]);
		$this->webDriver->wait(20, 1000)->until(WebDriverExpectedCondition::elementTextContains(WebDriverBy::id('twofactor-totp-settings'), 'This is your new TOTP secret:'));
	}

	private function createSecret() {
		$secret = GoogleAuthenticator::generateRandom();
		$dbsecret = new TotpSecret();
		$dbsecret->setSecret(OC::$server->getCrypto()->encrypt($secret));
		$dbsecret->setUserId($this->user->getUID());
		$this->secretMapper->insert($dbsecret);
		return $secret;
	}

	public function testLoginShouldFailWithWrongOTP() {
		$this->createSecret();

		$this->webDriver->get('http://localhost:8080/index.php/login');
		$this->assertContains('Nextcloud', $this->webDriver->getTitle());

		// Log in
		$this->webDriver->findElement(WebDriverBy::id('user'))->sendKeys('admin');
		$this->webDriver->findElement(WebDriverBy::id('password'))->sendKeys('admin');
		$this->webDriver->findElement(WebDriverBy::cssSelector('form[name=login] input[type=submit]'))->click();

		// Enter a wrong OTP
		$this->webDriver->findElement(WebDriverBy::name('challenge'))->sendKeys('000');
		$this->webDriver->findElement(WebDriverBy::cssSelector('input.confirm-inline.icon-confirm'))->click();

		$this->assertEquals('http://localhost:8080/index.php/login/challenge/totp', $this->webDriver->getCurrentURL());
	}

}
