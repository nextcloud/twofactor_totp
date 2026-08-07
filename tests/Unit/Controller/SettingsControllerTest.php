<?php

declare(strict_types=1);

/*
 * SPDX-FileCopyrightText: 2016 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-only
 */
namespace OCA\TwoFactorTOTP\Unit\Controller;

use InvalidArgumentException;
use OCA\TwoFactorTOTP\Controller\SettingsController;
use OCA\TwoFactorTOTP\Db\TotpSecret;
use OCA\TwoFactorTOTP\Service\ITotp;
use OCP\AppFramework\Http\JSONResponse;
use OCP\Defaults;
use OCP\IRequest;
use OCP\IURLGenerator;
use OCP\IUser;
use OCP\IUserSession;
use PHPUnit\Framework\TestCase;

final class SettingsControllerTest extends TestCase {
	private $userSession;
	private $totp;
	private $defaults;
	private $urlGenerator;

	/** @var SettingsController */
	private $controller;

	protected function setUp(): void {
		$this->userSession = $this->createStub(IUserSession::class);
		$this->totp = $this->createStub(ITotp::class);
		$this->defaults = new Defaults();
		$this->urlGenerator = $this->createStub(IURLGenerator::class);

		$this->controller = new SettingsController('twofactor_totp', $this->createStub(IRequest::class), $this->userSession, $this->totp, $this->defaults, $this->urlGenerator);
	}

	public function testDisabledState(): void {
		$user = $this->createStub(IUser::class);
		$this->userSession->method('getUser')
			->willReturn($user);
		$this->totp->method('hasSecret')
			->willReturn(false);

		$expected = new JSONResponse([
			'state' => false,
		]);

		$this->assertEquals($expected, $this->controller->state());
	}

	public function testCreateSecret(): void {
		$user = $this->createStub(IUser::class);
		$this->userSession->method('getUser')
			->willReturn($user);
		$user->method('getCloudId')
			->willReturn('user@instance.com');
		$this->totp->method('createSecret')
			->willReturn('newsecret');
		$dbSecret = new TotpSecret();
		$dbSecret->setAlgorithm(ITotp::ALGORITHM_SHA1);
		$this->totp->method('getSecret')
			->willReturn($dbSecret);
		$this->urlGenerator->method('linkToRoute')
			->willReturn('/path/to/favicon');
		$this->urlGenerator->method('getAbsoluteURL')
			->willReturn('https://cloud.example.com/path/to/favicon');

		$issuer = rawurlencode((string)$this->defaults->getName());
		$image = rawurlencode('https://cloud.example.com/path/to/favicon');
		$expected = new JSONResponse([
			'state' => ITotp::STATE_CREATED,
			'secret' => 'newsecret',
			'qrUrl' => "otpauth://totp/$issuer%3Auser%40instance.com?secret=newsecret&issuer=$issuer&algorithm=SHA1&image=$image",
		]);

		$this->assertEquals($expected, $this->controller->enable(ITotp::STATE_CREATED));
	}

	public function testCreateSecretWithNonDefaultAlgorithm(): void {
		$user = $this->createStub(IUser::class);
		$this->userSession->method('getUser')
			->willReturn($user);
		$user->method('getCloudId')
			->willReturn('user@instance.com');
		$this->totp->method('createSecret')
			->willReturn('newsecret');
		$dbSecret = new TotpSecret();
		$dbSecret->setAlgorithm(ITotp::ALGORITHM_SHA256);
		$this->totp->method('getSecret')
			->willReturn($dbSecret);
		$this->urlGenerator->method('linkToRoute')
			->willReturn('/path/to/favicon');
		$this->urlGenerator->method('getAbsoluteURL')
			->willReturn('https://cloud.example.com/path/to/favicon');

		$issuer = rawurlencode((string)$this->defaults->getName());
		$image = rawurlencode('https://cloud.example.com/path/to/favicon');
		$expected = new JSONResponse([
			'state' => ITotp::STATE_CREATED,
			'secret' => 'newsecret',
			'qrUrl' => "otpauth://totp/$issuer%3Auser%40instance.com?secret=newsecret&issuer=$issuer&algorithm=SHA256&image=$image",
		]);

		$this->assertEquals($expected, $this->controller->enable(ITotp::STATE_CREATED));
	}

	public function testEnableSecret(): void {
		$user = $this->createStub(IUser::class);
		$this->userSession->method('getUser')
			->willReturn($user);
		$totp = $this->createMock(ITotp::class);
		$totp->expects($this->once())
			->method('enable')
			->with($user, '123456')
			->willReturn(true);
		$controller = new SettingsController('twofactor_totp', $this->createStub(IRequest::class), $this->userSession, $totp, $this->defaults, $this->urlGenerator);

		$expected = new JSONResponse([
			'state' => ITotp::STATE_ENABLED,
		]);

		$this->assertEquals($expected, $controller->enable(ITotp::STATE_ENABLED, '123456'));
	}

	public function testDisableSecret(): void {
		$user = $this->createStub(IUser::class);
		$this->userSession->method('getUser')
			->willReturn($user);
		$totp = $this->createMock(ITotp::class);
		$totp->expects($this->once())
			->method('deleteSecret')
			->with($user);
		$controller = new SettingsController('twofactor_totp', $this->createStub(IRequest::class), $this->userSession, $totp, $this->defaults, $this->urlGenerator);

		$expected = new JSONResponse([
			'state' => ITotp::STATE_DISABLED,
		]);

		$this->assertEquals($expected, $controller->enable(ITotp::STATE_DISABLED));
	}

	public function testEnableInvalidState(): void {
		$user = $this->createStub(IUser::class);
		$this->userSession->method('getUser')
			->willReturn($user);

		$this->expectException(InvalidArgumentException::class);
		$this->controller->enable(17);
	}
}
