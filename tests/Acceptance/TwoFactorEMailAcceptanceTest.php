<?php

declare(strict_types=1);

/*
 * SPDX-FileCopyrightText: 2017 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-only
 */

namespace OCA\TwoFactorEMail\Test\Acceptance;

use ChristophWurst\Nextcloud\Testing\Selenium;
use ChristophWurst\Nextcloud\Testing\TestCase;
use ChristophWurst\Nextcloud\Testing\TestUser;
use Facebook\WebDriver\Exception\ElementNotInteractableException;
use Facebook\WebDriver\WebDriver;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use OC;
use OCA\TwoFactorEMail\Service\IStateManager;
use OCP\IUser;

/**
 * @group Acceptance
 */
class TwoFactorEMailAcceptanceTest extends TestCase {
	use TestUser;
	use Selenium;

	/** @var IUser */
	private $user;

	public function setUp(): void {
		parent::setUp();

		$this->user = $this->createTestUser();
		$this->user->setSystemEMailAddress('test@localhost');
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
				return count($driver->findElements(WebDriverBy::id('twofactor-email-settings'))) > 0;
			} catch (ElementNotInteractableException $ex) {
				return false;
			}
		});
		$this->webDriver->findElement(WebDriverBy::id('twofactor-email-settings'))->click();

		// Try to locate checked checkbox
		$this->webDriver->wait(20, 200)->until(function (WebDriver $driver) {
			try {
				return $driver->findElement(WebDriverBy::id('twofactor-email-settings'))->getAttribute('checked') === 'true';
			} catch (ElementNotInteractableException $ex) {
				return false;
			}
		});

		/** @var IStateManager $providerState */
		$providerState = OC::$server->query(IStateManager::class);
		self::assertTrue($providerState->isEnabled($this->user));
	}

	public function testLoginShouldFailWithWrongOTP(): void {
		/** @var IStateManager $stateManager */
		$stateManager = OC::$server->query(IStateManager::class);
		$stateManager->enable($this->user);

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
