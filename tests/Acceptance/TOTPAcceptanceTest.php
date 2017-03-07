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
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\WebDriverBrowserType;
use Facebook\WebDriver\Remote\WebDriverCapabilityType;
use Facebook\WebDriver\WebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use OC;
use OCA\TwoFactorTOTP\Db\TotpSecret;
use OCA\TwoFactorTOTP\Db\TotpSecretMapper;
use OCP\IUser;
use Otp\GoogleAuthenticator;
use PHPUnit_Framework_TestCase;

/**
 * @group Acceptance
 */
class TOTPAcceptenceTest extends PHPUnit_Framework_TestCase {

	/** @var IUser */
	private static $user;

	/** @var TotpSecretMapper */
	private static $secretMapper;

	/** @var RemoteWebDriver */
	protected $webDriver;

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();

		self::$user = OC::$server->getUserManager()->get('admin');
		self::$secretMapper = new TotpSecretMapper(OC::$server->getDatabaseConnection());
	}

	public function setUp() {
		$capabilities = [
			WebDriverCapabilityType::BROWSER_NAME => $this->getBrowser(),
		];

		if ($this->isRunningOnCI()) {
			$capabilities['tunnel-identifier'] = getenv('TRAVIS_JOB_NUMBER');
			$capabilities['build'] = getenv('TRAVIS_BUILD_NUMBER');
			$capabilities['name'] = 'PR' . getenv('TRAVIS_PULL_REQUEST') . ', Build ' . getenv('TRAVIS_BUILD_NUMBER');
			$user = 'nextcloud-totp';
			$accessKey = getenv('SAUCE_ACCESS_KEY');
			$this->webDriver = RemoteWebDriver::create("http://$user:$accessKey@ondemand.saucelabs.com/wd/hub", $capabilities);
		} else {
			$this->webDriver = RemoteWebDriver::create("http://localhost:4444/wd/hub", $capabilities);
		}
	}

	private function getBrowser() {
		$env = getenv('SELENIUM_BROWSER');
		if ($env !== false) {
			return $env;
		}
		return WebDriverBrowserType::FIREFOX;
	}

	private function isRunningOnCI() {
		return getenv('TRAVIS') !== false;
	}

	public function tearDown() {
		// Always delete secret again
		$secret = self::$secretMapper->getSecret(self::$user);
		if (!is_null($secret)) {
			self::$secretMapper->delete($secret);
		}

		$this->webDriver->quit();
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
		$dbsecret->setUserId(self::$user->getUID());
		self::$secretMapper->insert($dbsecret);
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
		$this->webDriver->findElement(WebDriverBy::cssSelector('button[type="submit"]'))->click();

		$this->assertEquals('http://localhost:8080/index.php/login/challenge/totp', $this->webDriver->getCurrentURL());
	}

}
