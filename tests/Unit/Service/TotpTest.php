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
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class TotpTest extends TestCase {

	private ICrypto $crypto;
	private IAppConfig $appConfig;

	protected function setUp(): void {
		$this->crypto = $this->createStub(ICrypto::class);
		$this->crypto->method('encrypt')->willReturnArgument(0);
		$this->appConfig = $this->createStub(IAppConfig::class);
	}

	private function buildTotp(TotpSecretMapper $secretMapper, ISecureRandom $random): Totp {
		return new Totp(
			$secretMapper,
			$this->crypto,
			$this->createStub(IEventDispatcher::class),
			$random,
			$this->appConfig,
		);
	}

	private function stubMapperWithoutSecret(): TotpSecretMapper {
		$secretMapper = $this->createStub(TotpSecretMapper::class);
		$secretMapper->method('getSecret')
			->willThrowException(new DoesNotExistException(''));
		$secretMapper->method('insert')->willReturnArgument(0);
		return $secretMapper;
	}

	private function mockMapperExpectingAlgorithm(string $expectedAlgorithm): TotpSecretMapper&MockObject {
		$secretMapper = $this->createMock(TotpSecretMapper::class);
		$secretMapper->method('getSecret')
			->willThrowException(new DoesNotExistException(''));
		$secretMapper->expects($this->once())
			->method('insert')
			->with($this->callback(function (TotpSecret $entity) use ($expectedAlgorithm): bool {
				$this->assertSame($expectedAlgorithm, $entity->getAlgorithm());
				return true;
			}))
			->willReturnArgument(0);
		return $secretMapper;
	}

	public function testCreateSecretStoresDefaultAlgorithm(): void {
		$this->appConfig->method('getAppValueString')->willReturn(ITotp::DEFAULT_ALGORITHM);
		$this->appConfig->method('getAppValueInt')->willReturn(ITotp::DEFAULT_SECRET_LENGTH);
		$random = $this->createStub(ISecureRandom::class);
		$random->method('generate')->willReturn(str_repeat('A', 32));
		$secretMapper = $this->mockMapperExpectingAlgorithm(ITotp::ALGORITHM_SHA1);
		$totp = $this->buildTotp($secretMapper, $random);

		$user = $this->createStub(IUser::class);
		$totp->createSecret($user);
	}

	public function testCreateSecretStoresConfiguredAlgorithm(): void {
		$this->appConfig->method('getAppValueString')->willReturn(ITotp::ALGORITHM_SHA256);
		$this->appConfig->method('getAppValueInt')->willReturn(ITotp::DEFAULT_SECRET_LENGTH);
		$random = $this->createStub(ISecureRandom::class);
		$random->method('generate')->willReturn(str_repeat('A', 32));
		$secretMapper = $this->mockMapperExpectingAlgorithm(ITotp::ALGORITHM_SHA256);
		$totp = $this->buildTotp($secretMapper, $random);

		$user = $this->createStub(IUser::class);
		$totp->createSecret($user);
	}

	public function testCreateSecretFallsBackToDefaultForInvalidAlgorithm(): void {
		$this->appConfig->method('getAppValueString')->willReturn('md5');
		$this->appConfig->method('getAppValueInt')->willReturn(ITotp::DEFAULT_SECRET_LENGTH);
		$random = $this->createStub(ISecureRandom::class);
		$random->method('generate')->willReturn(str_repeat('A', 32));
		$secretMapper = $this->mockMapperExpectingAlgorithm(ITotp::DEFAULT_ALGORITHM);
		$totp = $this->buildTotp($secretMapper, $random);

		$user = $this->createStub(IUser::class);
		$totp->createSecret($user);
	}

	public function testCreateSecretUsesConfiguredLength(): void {
		$this->appConfig->method('getAppValueString')->willReturn(ITotp::DEFAULT_ALGORITHM);
		$this->appConfig->method('getAppValueInt')->willReturn(64);
		$random = $this->createMock(ISecureRandom::class);
		$random->expects($this->once())
			->method('generate')
			->with(64, ISecureRandom::CHAR_UPPER . '234567')
			->willReturn(str_repeat('A', 64));
		$totp = $this->buildTotp($this->stubMapperWithoutSecret(), $random);

		$user = $this->createStub(IUser::class);
		$totp->createSecret($user);
	}

	public function testCreateSecretFallsBackToDefaultLengthWhenTooShort(): void {
		$this->appConfig->method('getAppValueString')->willReturn(ITotp::DEFAULT_ALGORITHM);
		$this->appConfig->method('getAppValueInt')->willReturn(5);
		$random = $this->createMock(ISecureRandom::class);
		$random->expects($this->once())
			->method('generate')
			->with(ITotp::DEFAULT_SECRET_LENGTH, ISecureRandom::CHAR_UPPER . '234567')
			->willReturn(str_repeat('A', ITotp::DEFAULT_SECRET_LENGTH));
		$totp = $this->buildTotp($this->stubMapperWithoutSecret(), $random);

		$user = $this->createStub(IUser::class);
		$totp->createSecret($user);
	}

	public function testCreateSecretFallsBackToDefaultLengthWhenTooLong(): void {
		$this->appConfig->method('getAppValueString')->willReturn(ITotp::DEFAULT_ALGORITHM);
		$this->appConfig->method('getAppValueInt')->willReturn(200);
		$random = $this->createMock(ISecureRandom::class);
		$random->expects($this->once())
			->method('generate')
			->with(ITotp::DEFAULT_SECRET_LENGTH, ISecureRandom::CHAR_UPPER . '234567')
			->willReturn(str_repeat('A', ITotp::DEFAULT_SECRET_LENGTH));
		$totp = $this->buildTotp($this->stubMapperWithoutSecret(), $random);

		$user = $this->createStub(IUser::class);
		$totp->createSecret($user);
	}
}
