<?php

declare(strict_types=1);

/**
 * @author Nico Kluge <nico.kluge@klugecoded.com>
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

namespace OCA\TwoFactorEMail\Tests\Acceptance;

use ChristophWurst\Nextcloud\Testing\Selenium;
use ChristophWurst\Nextcloud\Testing\TestCase;
use ChristophWurst\Nextcloud\Testing\TestUser;
use Facebook\WebDriver\Exception\ElementNotInteractableException;
use Facebook\WebDriver\WebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use OC;
use OCA\TwoFactorEMail\Db\TwoFactorEMail;
use OCA\TwoFactorEMail\Db\TwoFactorEMailMapper;
use OCA\TwoFactorEMail\Provider\EMailProvider;
use OCA\TwoFactorEMail\Service\IEMailService;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\Authentication\TwoFactorAuth\IRegistry;
use OCP\IUser;

/**
 * @group Acceptance
 */
class TwoFactorEMailAcceptanceTest extends TestCase {
	use TestUser;
	use Selenium;

	/** @var IUser */
	private $user;

	/** @var TwoFactorEMailMapper */
	private $twoFactorEMailMapper;

	public function setUp(): void {
		parent::setUp();

		$this->user = $this->createTestUser();
		$this->twoFactorEMailMapper = new TwoFactorEMailMapper(OC::$server->getDatabaseConnection());
	}

	public function testEnableTwoFactorEmail(): void {
		$this->webDriver->get('http://localhost:8080/index.php/login');
		self::assertStringContainsString('Nextcloud', $this->webDriver->getTitle());

		// Log in
		$this->webDriver->findElement(WebDriverBy::id('user'))->sendKeys($this->user->getUID());
		$this->webDriver->findElement(WebDriverBy::id('password'))->sendKeys('password');
		$this->webDriver->findElement(WebDriverBy::cssSelector('form[name=login] [type=submit]'))->click();

		// Go to personal settings and email settings
		$this->webDriver->get('http://localhost:8080/index.php/settings/user/security');

		// Enable TwoFactorEMail
		// Wait for state being loaded from the server
		$this->webDriver->wait(20, 200)->until(function (WebDriver $driver) {
			try {
				return count($driver->findElements(WebDriverBy::id('email-enabled'))) > 0;
			} catch (ElementNotInteractableException $ex) {
				return false;
			}
		});
		$this->webDriver->executeScript('arguments[0].click(); console.log(arguments[0]);', [
			$this->webDriver->findElement(WebDriverBy::id('email-enabled')),
		]);
		$this->webDriver->wait(15, 200)->until(WebDriverExpectedCondition::elementTextContains(WebDriverBy::id('twofactor-email-settings'), 'We have sent an email containing your authentication code to:'));
		$this->assertHasEMail(IEMailService::STATE_CREATED);

		// Enter a wrong code
		$this->webDriver->findElement(WebDriverBy::id('email-confirmation'))->sendKeys('000000');
		$this->webDriver->findElement(WebDriverBy::id('email-confirmation-submit'))->click();

		// Wait for the notification
		// TODO: this was replaced with toastify https://github.com/nextcloud/server/pull/15124
		// $this->webDriver->wait(15, 200)->until(WebDriverExpectedCondition::elementTextContains(WebDriverBy::id('notification'), 'Could not verify your key. Please try again'));

		// Enter a correct code
		$this->webDriver->findElement(WebDriverBy::id('email-confirmation'))->sendKeys($this->getValidCode());
		$this->webDriver->findElement(WebDriverBy::id('email-confirmation-submit'))->click();

		// Try to locate checked checkbox
		$this->webDriver->wait(20, 200)->until(function (WebDriver $driver) {
			try {
				return $driver->findElement(WebDriverBy::id('email-enabled'))->getAttribute('checked') === 'true';
			} catch (ElementNotInteractableException $ex) {
				return false;
			}
		});
		$this->assertHasEMail(IEMailService::STATE_ENABLED);
	}

	private function assertHasEMail($state): void {
		try {
			$dbTwoFactorEMail = $this->twoFactorEMailMapper->getTwoFactorEMail($this->user);
			if ($state !== (int)$dbTwoFactorEMail->getState()) {
				self::fail('TwoFactorEMail has has wrong state');
			}
		} catch (DoesNotExistException $ex) {
			self::fail('User does not have a TwoFactorEMail');
		}
	}

	private function getValidCode(): string {
		try {
			$dbTwoFactorEMail = $this->twoFactorEMailMapper->getTwoFactorEMail($this->user);
		} catch (DoesNotExistException $e) {
			self::fail('User does not have a TwoFactorEMail');
		}
		return $dbTwoFactorEMail->getAuthCode();
	}

	private function createTwoFactorEMail(): void {
		$dbTwoFactorEMail = new TwoFactorEMail();
		$dbTwoFactorEMail->setUserId($this->user->getUID());
		$this->twoFactorEMailMapper->insert($dbTwoFactorEMail);

		/** @var IRegistry $registry */
		$registry = OC::$server->query(IRegistry::class);
		/** @var EMailProvider $provider */
		$provider = OC::$server->query(EMailProvider::class);
		$registry->enableProviderFor($provider, $this->user);
	}

	public function testLoginShouldFailWithWrongOTP(): void {
		$this->createTwoFactorEMail();

		$this->webDriver->get('http://localhost:8080/index.php/login');
		self::assertStringContainsString('Nextcloud', $this->webDriver->getTitle());

		// Log in
		$this->webDriver->findElement(WebDriverBy::id('user'))->sendKeys($this->user->getUID());
		$this->webDriver->findElement(WebDriverBy::id('password'))->sendKeys('password');
		$this->webDriver->findElement(WebDriverBy::cssSelector('form[name=login] [type=submit]'))->click();

		$this->webDriver->wait(20, 200)->until(function (WebDriver $driver) {
			try {
				return $driver->findElements(WebDriverBy::className('email-form'));
			} catch (ElementNotInteractableException $ex) {
				return false;
			}
		});

		// Enter a wrong OTP
		$this->webDriver->findElement(WebDriverBy::name('challenge'))->sendKeys('000000');
		$this->webDriver->findElement(WebDriverBy::cssSelector('button[type="submit"]'))->submit();

		$this->webDriver->wait(20, 200)->until(WebDriverExpectedCondition::elementTextContains(WebDriverBy::className('body-login-container'), 'Error while validating your second factor'));

		$this->assertEquals('http://localhost:8080/index.php/login/challenge/email', $this->webDriver->getCurrentURL());
	}
}
