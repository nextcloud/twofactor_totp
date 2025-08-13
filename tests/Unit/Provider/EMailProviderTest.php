<?php

declare(strict_types=1);

/*
 * SPDX-FileCopyrightText: 2018 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\TwoFactorEMail\Test\Unit\Provider;

use ChristophWurst\Nextcloud\Testing\TestCase;
use OCA\TwoFactorEMail\Provider\AtLoginProvider;
use OCA\TwoFactorEMail\Provider\EMailProvider;
use OCA\TwoFactorEMail\Service\IEMailProviderState;
use OCA\TwoFactorEMail\Service\IEMailService;
use OCA\TwoFactorEMail\Settings\Personal;
use OCP\AppFramework\IAppContainer;
use OCP\AppFramework\Services\IInitialState;
use OCP\IL10N;
use OCP\IURLGenerator;
use OCP\IUser;
use OCP\Security\ISecureRandom;
use PHPUnit\Framework\MockObject\MockObject;

class EMailProviderTest extends TestCase {

	/** @var IEMailProviderState|MockObject */
	private $providerState;

	/** @var IEMailService|MockObject */
	private $emailService;

	/** @var IL10N|MockObject */
	private $l10n;

	/** @var IAppContainer|MockObject */
	private $container;

	/** @var IInitialStateService|MockObject */
	private $initialState;

	/** @var IURLGenerator|MockObject */
	private $urlGenerator;

	/** @var EMailProvider */
	private $provider;

	protected function setUp(): void {
		parent::setUp();

		$this->providerState = $this->createMock(IEMailProviderState::class);
		$this->emailService = $this->createMock(IEMailService::class);
		$this->l10n = $this->createMock(IL10N::class);
		$this->container = $this->createMock(IAppContainer::class);
		$this->initialState = $this->createMock(IInitialState::class);
		$this->urlGenerator = $this->createMock(IURLGenerator::class);
		$this->secureRandom = $this->createMock(ISecureRandom::class);

		$this->provider = new EMailProvider(
			$this->providerState,
			$this->emailService,
			$this->l10n,
			$this->container,
			$this->initialState,
			$this->urlGenerator,
			$this->secureRandom
		);
	}

	public function testGetId(): void {
		$expectedId = 'email';

		$id = $this->provider->getId();

		self::assertEquals($expectedId, $id);
	}

	public function testGetDisplayName(): void {
		$expected = 'E-mail';

		$displayName = $this->provider->getDisplayName();

		self::assertEquals($expected, $displayName);
	}

	public function testGetDescription(): void {
		$description = 'Authenticate by e-mail';
		$this->l10n->expects($this->once())
			->method('t')
			->willReturnArgument(0);

		$descr = $this->provider->getDescription();

		self::assertEquals($description, $descr);
	}

	public function testGetLightIcon(): void {
		$this->urlGenerator->expects(self::once())
			->method('imagePath')
			->with('twofactor_email', 'app.svg')
			->willReturn('/path/to/app.svg');

		$icon = $this->provider->getLightIcon();

		self::assertEquals('/path/to/app.svg', $icon);
	}

	public function testGetDarkIcon(): void {
		$this->urlGenerator->expects(self::once())
			->method('imagePath')
			->with('twofactor_email', 'app-dark.svg')
			->willReturn('/path/to/app-dark.svg');

		$icon = $this->provider->getDarkIcon();

		self::assertEquals('/path/to/app-dark.svg', $icon);
	}

	public function testGetPersonalSettings(): void {
		$expected = new Personal();

		$user = $this->createMock(IUser::class);
		$this->providerState->expects($this->once())
			->method('isEnabled')
			->with($user)
			->willReturn(true);
		$this->initialState->expects($this->once())
			->method('provideInitialState')
			->with(
				'state',
				true
			);

		$actual = $this->provider->getPersonalSettings($user);

		self::assertEquals($expected, $actual);
	}

	public function testDeactivate(): void {
		$user = $this->createMock(IUser::class);
		$this->emailService->expects($this->once())
			->method('deleteTwoFactorEMail')
			->with($user);

		$this->provider->disableFor($user);
	}

	public function testGetSetupProvider(): void {
		/** @var IUser|MockObject $user */
		$user = $this->createMock(IUser::class);
		$provider = $this->createMock(AtLoginProvider::class);
		$this->container->expects($this->once())
			->method('query')
			->with(AtLoginProvider::class)
			->willReturn($provider);

		$result = $this->provider->getLoginSetup($user);

		self::assertSame($provider, $result);
	}
}
