<?php

/*
 * SPDX-FileCopyrightText: 2016 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-only
 */

namespace OCA\TwoFactorEMail\Unit\Activity;

use ChristophWurst\Nextcloud\Testing\TestCase;
use OCA\TwoFactorEMail\Activity\Setting;
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

	public function testAll() {
		$this->assertEquals(false, $this->setting->canChangeMail());
		$this->assertEquals(false, $this->setting->canChangeStream());
		$this->assertEquals('twofactor_email', $this->setting->getIdentifier());
		$this->l10n->expects($this->once())
			->method('t')
			->with('E-mail')
			->willReturn('E-Mail');
		$this->assertEquals('E-Mail', $this->setting->getName());
		$this->assertEquals(10, $this->setting->getPriority());
		$this->assertEquals(true, $this->setting->isDefaultEnabledMail());
		$this->assertEquals(true, $this->setting->isDefaultEnabledStream());
	}
}
