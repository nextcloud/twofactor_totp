<?php

declare(strict_types=1);

/*
 * SPDX-FileCopyrightText: 2018 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\TwoFactorEMail\Test\Unit\Provider;

use ChristophWurst\Nextcloud\Testing\TestCase;
use OCA\TwoFactorEMail\Provider\EMailProvider;
use OCA\TwoFactorEMail\Service\IChallengeService;
use OCA\TwoFactorEMail\Service\IStateManager;
use OCA\TwoFactorEMail\Settings\Personal;
use OCP\AppFramework\Services\IInitialState;
use OCP\IL10N;
use OCP\IURLGenerator;
use OCP\IUser;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;

class EMailProviderTest extends TestCase {
	private IL10N&MockObject $l10n;
	private IInitialState&MockObject $initialState;
	private IURLGenerator&MockObject $urlGenerator;
	private IChallengeService&MockObject $challengeService;
	private IStateManager&MockObject $stateManager;

	private EMailProvider $provider;

	/**
	 * @throws Exception
	 */
	protected function setUp(): void {
		parent::setUp();

		$this->l10n = $this->createMock(IL10N::class);
		$this->initialState = $this->createMock(IInitialState::class);
		$this->urlGenerator = $this->createMock(IURLGenerator::class);
		$this->challengeService = $this->createMock(IChallengeService::class);
		$this->stateManager = $this->createMock(IStateManager::class);

		$this->provider = new EMailProvider(
			$this->l10n,
			$this->initialState,
			$this->urlGenerator,
			$this->challengeService,
			$this->stateManager,
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
		$this->stateManager->expects($this->once())
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
		$this->stateManager->expects($this->once())
			->method('disable')
			->with($user);

		$this->provider->disableFor($user);
	}
}
