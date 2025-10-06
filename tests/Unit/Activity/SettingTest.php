<?php

/*
 * SPDX-FileCopyrightText: 2016 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-only
 */

namespace OCA\TwoFactorTOTP\Test\Unit\Activity;

use ChristophWurst\Nextcloud\Testing\TestCase;
use OCA\TwoFactorTOTP\Activity\Setting;
use OCP\IL10N;

class SettingTest extends TestCase {
	private $l10n;

	/** @var Setting */
	private $setting;

	protected function setUp(): void {
		parent::setUp();

		$this->l10n = $this->createMock(IL10N::class);

		$this->setting = new Setting($this->l10n);
	}

	public function testAll(): void {
		$this->assertEquals(false, $this->setting->canChangeMail());
		$this->assertEquals(false, $this->setting->canChangeStream());
		$this->assertEquals('twofactor_totp', $this->setting->getIdentifier());
		$this->l10n->expects($this->once())
			->method('t')
			->with('TOTP (Authenticator app)')
			->willReturn('TOTP (Google Authentifizierer)');
		$this->assertEquals('TOTP (Google Authentifizierer)', $this->setting->getName());
		$this->assertEquals(10, $this->setting->getPriority());
		$this->assertEquals(true, $this->setting->isDefaultEnabledMail());
		$this->assertEquals(true, $this->setting->isDefaultEnabledStream());
	}
}
