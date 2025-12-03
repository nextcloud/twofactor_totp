<?php

declare(strict_types=1);

/*
 * SPDX-FileCopyrightText: 2018 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\TwoFactorTOTP\Test\Unit\Provider;

use ChristophWurst\Nextcloud\Testing\TestCase;
use OCA\TwoFactorTOTP\Db\TotpSecret;
use OCA\TwoFactorTOTP\Exception\NoTotpSecretFoundException;
use OCA\TwoFactorTOTP\Provider\AtLoginProvider;
use OCA\TwoFactorTOTP\Provider\TotpProvider;
use OCA\TwoFactorTOTP\Service\ITotp;
use OCA\TwoFactorTOTP\Settings\Personal;
use OCP\AppFramework\IAppContainer;
use OCP\AppFramework\Services\IInitialState;
use OCP\IL10N;
use OCP\IURLGenerator;
use OCP\IUser;
use PHPUnit\Framework\MockObject\MockObject;

class TotpProviderTest extends TestCase {

	/** @var ITotp|MockObject */
	private $totp;

	/** @var IL10N|MockObject */
	private $l10n;

	/** @var IAppContainer|MockObject */
	private $container;

	/** @var IInitialStateService|MockObject */
	private $initialState;

	/** @var IURLGenerator|MockObject */
	private $urlGenerator;

	/** @var TotpProvider */
	private $provider;

	protected function setUp(): void {
		parent::setUp();

		$this->totp = $this->createMock(ITotp::class);
		$this->l10n = $this->createMock(IL10N::class);
		$this->container = $this->createMock(IAppContainer::class);
		$this->initialState = $this->createMock(IInitialState::class);
		$this->urlGenerator = $this->createMock(IURLGenerator::class);

		$this->provider = new TotpProvider(
			$this->totp,
			$this->l10n,
			$this->container,
			$this->initialState,
			$this->urlGenerator
		);
	}

	public function testGetId(): void {
		$expectedId = 'totp';

		$id = $this->provider->getId();

		$this->assertEquals($expectedId, $id);
	}

	public function testGetDisplayName(): void {
		$expected = 'TOTP (Authenticator app)';

		$displayName = $this->provider->getDisplayName();

		$this->assertEquals($expected, $displayName);
	}

	public function testGetDescription(): void {
		$description = 'Authenticate with a TOTP app';
		$this->l10n->expects($this->once())
			->method('t')
			->willReturnArgument(0);

		$descr = $this->provider->getDescription();

		$this->assertEquals($description, $descr);
	}

	public function testGetLightIcon(): void {
		$this->urlGenerator->expects($this->once())
			->method('imagePath')
			->with('twofactor_totp', 'app.svg')
			->willReturn('/path/to/app.svg');

		$icon = $this->provider->getLightIcon();

		$this->assertEquals('/path/to/app.svg', $icon);
	}

	public function testGetDarkIcon(): void {
		$this->urlGenerator->expects($this->once())
			->method('imagePath')
			->with('twofactor_totp', 'app-dark.svg')
			->willReturn('/path/to/app-dark.svg');

		$icon = $this->provider->getDarkIcon();

		$this->assertEquals('/path/to/app-dark.svg', $icon);
	}

	public function testGetPersonalSettings(): void {
		$expected = new Personal();

		$user = $this->createMock(IUser::class);
		$this->totp->expects($this->once())
			->method('hasSecret')
			->with($user)
			->willReturn(true);
		$this->initialState->expects($this->once())
			->method('provideInitialState')
			->with(
				'state',
				true
			);

		$actual = $this->provider->getPersonalSettings($user);

		$this->assertEquals($expected, $actual);
	}

	public function testVerifyChallengeSecretNotFound(): void {
		$user = $this->createMock(IUser::class);
		$this->totp->expects($this->once())
			->method('getSecret')
			->with($user)
			->willThrowException(new NoTotpSecretFoundException());

		$result = $this->provider->verifyChallenge($user, '123456');

		$this->assertFalse($result);
	}

	public function testVerifyChallengeStripNonDigits(): void {
		$user = $this->createMock(IUser::class);
		$secret = new TotpSecret();
		$this->totp->expects(self::once())
			->method('getSecret')
			->with($user)
			->willReturn($secret);
		$this->totp->expects(self::once())
			->method('validateSecret')
			->with($secret, '123456')
			->willReturn(true);

		$result = $this->provider->verifyChallenge($user, '  123456  a	');

		$this->assertTrue($result);
	}

	public function testDeactivate(): void {
		$user = $this->createMock(IUser::class);
		$this->totp->expects($this->once())
			->method('deleteSecret')
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

		$this->assertSame($provider, $result);
	}
}
