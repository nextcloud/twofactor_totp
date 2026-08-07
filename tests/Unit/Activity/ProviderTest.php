<?php

/*
 * SPDX-FileCopyrightText: 2016 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-only
 */

namespace OCA\TwoFactorTOTP\Test\Unit\Activity;

use ChristophWurst\Nextcloud\Testing\TestCase;
use InvalidArgumentException;
use OCA\TwoFactorTOTP\Activity\Provider;
use OCP\Activity\IEvent;
use OCP\IL10N;
use OCP\ILogger;
use OCP\IURLGenerator;
use OCP\L10N\IFactory;
use PHPUnit\Framework\Attributes\DataProvider;

class ProviderTest extends TestCase {
	private $l10n;
	private $urlGenerator;

	/** @var Provider */
	private $provider;

	protected function setUp(): void {
		parent::setUp();

		$this->l10n = $this->createStub(IFactory::class);
		$this->urlGenerator = $this->createStub(IURLGenerator::class);
		$logger = $this->createStub(ILogger::class);

		$this->provider = new Provider($this->l10n, $this->urlGenerator, $logger);
	}

	public function testParseUnrelated(): void {
		$lang = 'ru';
		$event = $this->createMock(IEvent::class);
		$event->expects($this->once())
			->method('getApp')
			->willReturn('comments');
		$this->expectException(InvalidArgumentException::class);

		$this->provider->parse($lang, $event);
	}

	public static function subjectData(): \Iterator {
		yield ['totp_enabled_subject'];
		yield ['totp_disabled_subject'];
		yield ['totp_disabled_by_admin'];
	}

	#[DataProvider('subjectData')]
	public function testParse($subject): void {
		$lang = 'ru';
		$event = $this->createMock(IEvent::class);
		$l = $this->createStub(IL10N::class);

		$event->expects($this->once())
			->method('getApp')
			->willReturn('twofactor_totp');
		$this->l10n->method('get')
			->willReturn($l);
		$this->urlGenerator->method('imagePath')
			->willReturn('path/to/image');
		$this->urlGenerator->method('getAbsoluteURL')
			->willReturn('absolute/path/to/image');
		$event->expects($this->once())
			->method('setIcon')
			->with('absolute/path/to/image');
		$event->expects($this->once())
			->method('getSubject')
			->willReturn($subject);
		$event->expects($this->once())
			->method('setSubject');

		$this->provider->parse($lang, $event);
	}
}
