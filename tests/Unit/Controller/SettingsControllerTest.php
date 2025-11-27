<?php

declare(strict_types=1);

/*
 * SPDX-FileCopyrightText: 2016 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-only
 */
namespace OCA\TwoFactorTOTP\Unit\Controller;

use InvalidArgumentException;
use OCA\TwoFactorTOTP\Controller\SettingsController;
use OCA\TwoFactorTOTP\Service\ITotp;
use OCA\TwoFactorTOTP\Service\Totp;
use OCP\AppFramework\Http\JSONResponse;
use OCP\Defaults;
use OCP\IRequest;
use OCP\IUser;
use OCP\IUserSession;
use PHPUnit\Framework\TestCase;

final class SettingsControllerTest extends TestCase {
	private $userSession;
	private $totp;
	private $defaults;

	/** @var SettingsController */
	private $controller;

	protected function setUp(): void {
		$request = $this->createMock(IRequest::class);
		$this->userSession = $this->createMock(IUserSession::class);
		$this->totp = $this->createMock(Totp::class);
		$this->defaults = new Defaults();

		$this->controller = new SettingsController('twofactor_totp', $request, $this->userSession, $this->totp, $this->defaults);
	}

	public function testDisabledState(): void {
		$user = $this->createMock(IUser::class);
		$this->userSession->expects($this->once())
			->method('getUser')
			->willReturn($user);
		$this->totp->expects($this->once())
			->method('hasSecret')
			->with($user)
			->willReturn(false);

		$expected = new JSONResponse([
			'state' => false,
		]);

		$this->assertEquals($expected, $this->controller->state());
	}

	public function testCreateSecret(): void {
		$user = $this->createMock(IUser::class);
		$this->userSession->expects($this->exactly(2))
			->method('getUser')
			->willReturn($user);
		$user->expects($this->once())
			->method('getCloudId')
			->willReturn('user@instance.com');
		$this->totp->expects($this->once())
			->method('createSecret')
			->with($user)
			->willReturn('newsecret');
		$issuer = rawurlencode($this->defaults->getName());
		$expected = new JSONResponse([
			'state' => ITotp::STATE_CREATED,
			'secret' => 'newsecret',
			'qrUrl' => "otpauth://totp/$issuer%3Auser%40instance.com?secret=newsecret&issuer=$issuer",
		]);

		$this->assertEquals($expected, $this->controller->enable(ITotp::STATE_CREATED));
	}

	public function testEnableSecret(): void {
		$user = $this->createMock(IUser::class);
		$this->userSession->expects($this->once())
			->method('getUser')
			->willReturn($user);
		$this->totp->expects($this->once())
			->method('enable')
			->with($user, '123456')
			->willReturn(true);

		$expected = new JSONResponse([
			'state' => ITotp::STATE_ENABLED,
		]);

		$this->assertEquals($expected, $this->controller->enable(ITotp::STATE_ENABLED, '123456'));
	}

	public function testDisableSecret(): void {
		$user = $this->createMock(IUser::class);
		$this->userSession->expects($this->once())
			->method('getUser')
			->willReturn($user);
		$this->totp->expects($this->once())
			->method('deleteSecret');

		$expected = new JSONResponse([
			'state' => ITotp::STATE_DISABLED,
		]);

		$this->assertEquals($expected, $this->controller->enable(ITotp::STATE_DISABLED));
	}

	public function testEnableInvalidState(): void {
		$user = $this->createMock(IUser::class);
		$this->userSession->expects($this->once())
			->method('getUser')
			->willReturn($user);

		$this->expectException(InvalidArgumentException::class);
		$this->controller->enable(17);
	}
}
