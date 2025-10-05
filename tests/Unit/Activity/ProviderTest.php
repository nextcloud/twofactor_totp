<?php

/*
 * SPDX-FileCopyrightText: 2025 Olav and Niklas Seyfarth, Contributors <https://github.com/datenschutz-individuell/twofactor_email/blob/master/CONTRIBUTORS.md>
 * SPDX-License-Identifier: AGPL-3.0-only
 */

namespace OCA\TwoFactorEMail\Test\Unit\Activity;

use ChristophWurst\Nextcloud\Testing\TestCase;
use InvalidArgumentException;
use OCA\TwoFactorEMail\Activity\Notification;
use OCA\TwoFactorEMail\Activity\Provider;
use OCP\Activity\IEvent;
use OCP\IL10N;
use OCP\IURLGenerator;
use OCP\L10N\IFactory;
use PHPUnit\Framework\MockObject\MockObject;

class ProviderTest extends TestCase {
	private IFactory&MockObject $l10n;
	private IURLGenerator&MockObject $urlGenerator;

	private Provider $provider;

	protected function setUp(): void {
		parent::setUp();

		$this->l10n = $this->createMock(IFactory::class);
		$this->urlGenerator = $this->createMock(IURLGenerator::class);

		$this->provider = new Provider($this->l10n, $this->urlGenerator);
	}

	public function testParseUnrelated() {
		$lang = 'ru';
		$event = $this->createMock(IEvent::class);
		$event->expects($this->once())
			->method('getApp')
			->willReturn('comments');
		$this->expectException(InvalidArgumentException::class);

		$this->provider->parse($lang, $event);
	}

	public static function subjectData(): array {
		return [
			[Notification::ENABLED_BY_USER],
			[Notification::DISABLED_BY_USER],
			[Notification::ENABLED_BY_ADMIN],
			[Notification::DISABLED_BY_ADMIN],
		];
	}

	/**
	 * @dataProvider subjectData
	 */
	public function testParse(Notification $subject) {
		$lang = 'ru';
		$event = $this->createMock(IEvent::class);
		$l = $this->createMock(IL10N::class);

		$event->expects($this->once())
			->method('getApp')
			->willReturn('twofactor_email');
		$this->l10n->expects($this->once())
			->method('get')
			->with('twofactor_email', $lang)
			->willReturn($l);
		$this->urlGenerator->expects($this->once())
			->method('imagePath')
			->with('core', 'actions/password.svg')
			->willReturn('path/to/image');
		$this->urlGenerator->expects($this->once())
			->method('getAbsoluteURL')
			->with('path/to/image')
			->willReturn('absolute/path/to/image');
		$event->expects($this->once())
			->method('setIcon')
			->with('absolute/path/to/image');
		$event->expects($this->once())
			->method('getSubject')
			->willReturn($subject->value);
		$event->expects($this->once())
			->method('setSubject');

		$this->provider->parse($lang, $event);
	}
}
