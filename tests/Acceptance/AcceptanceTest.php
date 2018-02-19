<?php

/**
 * @author Christoph Wurst <christoph@winzerhof-wurst.at>
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

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\WebDriverBrowserType;
use Facebook\WebDriver\Remote\WebDriverCapabilityType;
use GuzzleHttp\Client;
use PHPUnit_Framework_TestCase;

abstract class AcceptanceTest extends PHPUnit_Framework_TestCase {

	/** @var RemoteWebDriver */
	protected $webDriver;

	protected function setUp() {
		parent::setUp();

		$capabilities = [
		    WebDriverCapabilityType::BROWSER_NAME => $this->getBrowser(),
		];

		if ($this->isRunningOnCI()) {
			$capabilities['tunnel-identifier'] = getenv('TRAVIS_JOB_NUMBER');
			$capabilities['build'] = getenv('TRAVIS_BUILD_NUMBER');
			$capabilities['name'] = $this->getTestName();
			$user = getenv('SAUCE_USERNAME');
			$accessKey = getenv('SAUCE_ACCESS_KEY');
			$this->webDriver = RemoteWebDriver::create("http://$user:$accessKey@ondemand.saucelabs.com/wd/hub", $capabilities);
		} else {
			$user = getenv('SAUCE_USERNAME');
			$accessKey = getenv('SAUCE_ACCESS_KEY');
			$this->webDriver = RemoteWebDriver::create("http://$user:$accessKey@localhost:4445/wd/hub", $capabilities);
		}
	}

	private function getBrowser() {
		$fromEnv = getenv('SELENIUM_BROWSER');
		if ($fromEnv !== false) {
			return $fromEnv;
		}
		return WebDriverBrowserType::FIREFOX;
	}

	private function getTestName() {
		if ($this->isRunningOnCI()) {
			return 'PR' . getenv('TRAVIS_PULL_REQUEST') . ', Build ' . getenv('TRAVIS_BUILD_NUMBER') . ', Test ' . self::class . '::' . $this->getName();
		} else {
			return 'Test ' . self::class . '::' . $this->getName();
		}
	}

	protected function tearDown() {
		parent::tearDown();

		$sessionId = $this->webDriver->getSessionID();

		$this->webDriver->quit();

		if ($this->isRunningOnCI()) {
			$this->reportTestStatusToSauce($sessionId);
		}
	}

	/**
	 * @param string $sessionId sauce labs job id
	 */
	private function reportTestStatusToSauce($sessionId) {
		$failed = parent::hasFailed();
		$httpClient = new Client();
		$httpClient->put("https://saucelabs.com/rest/v1/nextcloud-totp/jobs/$sessionId", [
		    'auth' => [
			getenv('SAUCE_USERNAME'),
			getenv('SAUCE_ACCESS_KEY'),
		    ],
		    'json' => [
			'passed' => !$failed,
		    ],
		]);
	}

	private function isRunningOnCI() {
		return getenv('TRAVIS') !== false;
	}

}
