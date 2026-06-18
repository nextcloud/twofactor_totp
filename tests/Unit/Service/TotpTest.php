<?php

declare(strict_types=1);

/*
 * SPDX-FileCopyrightText: 2024 Raphael Gradenwitz <raphael.gradenwitz@googlemail.com>
 * SPDX-FileCopyrightText: 2026 Christoph Wurst <christoph@winzerhof-wurst.at>
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\TwoFactorTOTP\Unit\Service;

use OCA\TwoFactorTOTP\Db\TotpSecret;
use OCA\TwoFactorTOTP\Db\TotpSecretMapper;
use OCA\TwoFactorTOTP\Service\ITotp;
use OCA\TwoFactorTOTP\Service\Totp;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Services\IAppConfig;
use OCP\EventDispatcher\IEventDispatcher;
use OCP\IUser;
use OCP\Security\ICrypto;
use OCP\Security\ISecureRandom;
use PHPUnit\Framework\TestCase;

final class TotpTest extends TestCase {

	private TotpSecretMapper $secretMapper;
	private ICrypto $crypto;
	private ISecureRandom $random;
	private IAppConfig $appConfig;
	private Totp $totp;

	protected function setUp(): void {
		$this->secretMapper = $this->createMock(TotpSecretMapper::class);
		$this->crypto = $this->createMock(ICrypto::class);
		$this->random = $this->createMock(ISecureRandom::class);
		$this->appConfig = $this->createMock(IAppConfig::class);

		$this->totp = new Totp(
			$this->secretMapper,
			$this->crypto,
			$this->createStub(IEventDispatcher::class),
			$this->random,
			$this->appConfig,
		);
	}

	private function stubNoExistingSecret(): void {
		$this->secretMapper->method('getSecret')
			->willThrowException(new DoesNotExistException(''));
	}

	public function testCreateSecretStoresDefaultAlgorithm(): void {
		$this->stubNoExistingSecret();
		$this->appConfig->method('getAppValueString')
			->with('algorithm', ITotp::DEFAULT_ALGORITHM)
			->willReturn(ITotp::DEFAULT_ALGORITHM);
		$this->appConfig->method('getAppValueInt')
			->willReturn(ITotp::DEFAULT_SECRET_LENGTH);
		$this->random->method('generate')->willReturn(str_repeat('A', 32));
		$this->crypto->method('encrypt')->willReturnArgument(0);

		$this->secretMapper->expects($this->once())
			->method('insert')
			->with($this->callback(function (TotpSecret $entity): bool {
				$this->assertSame(ITotp::ALGORITHM_SHA1, $entity->getAlgorithm());
				return true;
			}))
			->willReturnArgument(0);

		$user = $this->createStub(IUser::class);
		$this->totp->createSecret($user);
	}

	public function testCreateSecretStoresConfiguredAlgorithm(): void {
		$this->stubNoExistingSecret();
		$this->appConfig->method('getAppValueString')
			->with('algorithm', ITotp::DEFAULT_ALGORITHM)
			->willReturn(ITotp::ALGORITHM_SHA256);
		$this->appConfig->method('getAppValueInt')
			->willReturn(ITotp::DEFAULT_SECRET_LENGTH);
		$this->random->method('generate')->willReturn(str_repeat('A', 32));
		$this->crypto->method('encrypt')->willReturnArgument(0);

		$this->secretMapper->expects($this->once())
			->method('insert')
			->with($this->callback(function (TotpSecret $entity): bool {
				$this->assertSame(ITotp::ALGORITHM_SHA256, $entity->getAlgorithm());
				return true;
			}))
			->willReturnArgument(0);

		$user = $this->createStub(IUser::class);
		$this->totp->createSecret($user);
	}

	public function testCreateSecretFallsBackToDefaultForInvalidAlgorithm(): void {
		$this->stubNoExistingSecret();
		$this->appConfig->method('getAppValueString')
			->with('algorithm', ITotp::DEFAULT_ALGORITHM)
			->willReturn('md5');
		$this->appConfig->method('getAppValueInt')
			->willReturn(ITotp::DEFAULT_SECRET_LENGTH);
		$this->random->method('generate')->willReturn(str_repeat('A', 32));
		$this->crypto->method('encrypt')->willReturnArgument(0);

		$this->secretMapper->expects($this->once())
			->method('insert')
			->with($this->callback(function (TotpSecret $entity): bool {
				$this->assertSame(ITotp::DEFAULT_ALGORITHM, $entity->getAlgorithm());
				return true;
			}))
			->willReturnArgument(0);

		$user = $this->createStub(IUser::class);
		$this->totp->createSecret($user);
	}

	public function testCreateSecretUsesConfiguredLength(): void {
		$this->stubNoExistingSecret();
		$this->appConfig->method('getAppValueString')
			->willReturn(ITotp::DEFAULT_ALGORITHM);
		$this->appConfig->method('getAppValueInt')
			->with('secret_length', ITotp::DEFAULT_SECRET_LENGTH)
			->willReturn(64);
		$this->crypto->method('encrypt')->willReturnArgument(0);

		$this->random->expects($this->once())
			->method('generate')
			->with(64, ISecureRandom::CHAR_UPPER . '234567')
			->willReturn(str_repeat('A', 64));

		$this->secretMapper->method('insert')->willReturnArgument(0);

		$user = $this->createStub(IUser::class);
		$this->totp->createSecret($user);
	}

	public function testCreateSecretFallsBackToDefaultLengthWhenTooShort(): void {
		$this->stubNoExistingSecret();
		$this->appConfig->method('getAppValueString')
			->willReturn(ITotp::DEFAULT_ALGORITHM);
		$this->appConfig->method('getAppValueInt')
			->with('secret_length', ITotp::DEFAULT_SECRET_LENGTH)
			->willReturn(5);
		$this->crypto->method('encrypt')->willReturnArgument(0);

		$this->random->expects($this->once())
			->method('generate')
			->with(ITotp::DEFAULT_SECRET_LENGTH, ISecureRandom::CHAR_UPPER . '234567')
			->willReturn(str_repeat('A', ITotp::DEFAULT_SECRET_LENGTH));

		$this->secretMapper->method('insert')->willReturnArgument(0);

		$user = $this->createStub(IUser::class);
		$this->totp->createSecret($user);
	}

	public function testCreateSecretFallsBackToDefaultLengthWhenTooLong(): void {
		$this->stubNoExistingSecret();
		$this->appConfig->method('getAppValueString')
			->willReturn(ITotp::DEFAULT_ALGORITHM);
		$this->appConfig->method('getAppValueInt')
			->with('secret_length', ITotp::DEFAULT_SECRET_LENGTH)
			->willReturn(200);
		$this->crypto->method('encrypt')->willReturnArgument(0);

		$this->random->expects($this->once())
			->method('generate')
			->with(ITotp::DEFAULT_SECRET_LENGTH, ISecureRandom::CHAR_UPPER . '234567')
			->willReturn(str_repeat('A', ITotp::DEFAULT_SECRET_LENGTH));

		$this->secretMapper->method('insert')->willReturnArgument(0);

		$user = $this->createStub(IUser::class);
		$this->totp->createSecret($user);
	}
}
