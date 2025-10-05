<?php

/*
 * SPDX-FileCopyrightText: 2025 Olav and Niklas Seyfarth, Contributors <https://github.com/datenschutz-individuell/twofactor_email/blob/main/CONTRIBUTORS.md>
 * SPDX-License-Identifier: AGPL-3.0-only
 */

namespace OCA\TwoFactorEMail\Test\Unit\Activity;

use ChristophWurst\Nextcloud\Testing\TestCase;
use OCA\TwoFactorEMail\Activity\Setting;
use OCP\IL10N;
use PHPUnit\Framework\MockObject\MockObject;

class SettingTest extends TestCase {
	private IL10N&MockObject $l10n;

	private Setting $setting;

	protected function setUp(): void {
		parent::setUp();

		$this->l10n = $this->createMock(IL10N::class);

		$this->setting = new Setting($this->l10n);
	}

	public function testAll() {
		$this->assertFalse($this->setting->canChangeMail());
		$this->assertFalse($this->setting->canChangeStream());
		$this->assertEquals('twofactor_email', $this->setting->getIdentifier());
		$this->l10n->expects($this->once())
			->method('t')
			->with('E-mail')
			->willReturn('E-Mail');
		$this->assertEquals('E-Mail', $this->setting->getName());
		$this->assertEquals(10, $this->setting->getPriority());
		$this->assertTrue($this->setting->isDefaultEnabledMail());
		$this->assertTrue($this->setting->isDefaultEnabledStream());
	}
}
