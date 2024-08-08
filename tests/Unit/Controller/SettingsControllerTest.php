<?php

/**
 * @author Christoph Wurst <christoph@winzerhof-wurst.at>
 * @copyright Copyright (c) 2016 Christoph Wurst <christoph@winzerhof-wurst.at>
 * @author 2024 [ernolf] Raphael Gradenwitz <raphael.gradenwitz@googlemail.com>
 *
 * Two-factor TOTP
 *
 * This code is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License, version 3,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License, version 3,
 * along with this program.  If not, see <http://www.gnu.org/licenses/>
 *
 */

namespace OCA\TwoFactorTOTP\Unit\Controller;

use InvalidArgumentException;
use OCA\TwoFactorTOTP\Controller\SettingsController;
use OCA\TwoFactorTOTP\Service\ITotp;
use OCP\AppFramework\Http\JSONResponse;
use OCP\Defaults;
use OCP\IRequest;
use OCP\IURLGenerator;
use OCP\IUser;
use OCP\IUserSession;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class SettingsControllerTest extends TestCase {
	private $request;
	private $userSession;
	private $totp;
	private $defaults;
	private $urlGenerator;
	private $logger;

	/** @var SettingsController */
	private $controller;

	protected function setUp(): void {
		parent::setUp();

		$this->request = $this->createMock(IRequest::class);
		$this->userSession = $this->createMock(IUserSession::class);
		$this->totp = $this->createMock(ITotp::class);
		$this->defaults = $this->createMock(Defaults::class);
		$this->urlGenerator = $this->createMock(IURLGenerator::class);
		$this->logger = $this->createMock(LoggerInterface::class);

		$this->controller = new SettingsController(
			'twofactor_totp',
			$this->request,
			$this->userSession,
			$this->totp,
			$this->defaults,
			$this->urlGenerator,
			$this->logger
		);
	}

	public function testDisabledState() {
		$user = $this->createMock(IUser::class);
		$this->userSession->expects($this->once())
			->method('getUser')
			->willReturn($user);
		$this->totp->expects($this->once())
			->method('hasSecret')
			->with($user)
			->willReturn(false);

		$this->totp->expects($this->once())
			->method('getAlgorithmId')
			->with($user)
			->willReturn(0);
		$this->totp->expects($this->once())
			->method('getDigits')
			->with($user)
			->willReturn(0);
		$this->totp->expects($this->once())
			->method('getPeriod')
			->with($user)
			->willReturn(0);

		$expected = new JSONResponse([
			'state' => ITotp::STATE_DISABLED,
			'algorithm' => 0,
			'digits' => 0,
			'period' => 0
		]);

		$this->assertEquals($expected, $this->controller->state());
	}

	public function testCreateSecret() {
		$user = $this->createMock(IUser::class);
		$this->userSession->expects($this->exactly(2))
			->method('getUser')
			->willReturn($user);
		$user->expects($this->once())
			->method('getCloudId')
			->willReturn('user@instance.com');

		$this->totp->expects($this->once())
			->method('createSecret')
			->with($user, null, 1, 6, 30)
			->willReturn('newsecret');
		
		$this->totp->expects($this->once())
			->method('getDefaultAlgorithm')
			->willReturn(1);
		$this->totp->expects($this->once())
			->method('getDefaultDigits')
			->willReturn(6);
		$this->totp->expects($this->once())
			->method('getDefaultPeriod')
			->willReturn(30);

		$issuer = rawurlencode($this->defaults->getName());
		$qrUrl = "otpauth://totp/{$issuer}:user%40instance.com?secret=newsecret&issuer=$issuer&algorithm=SHA1&digits=6&period=30&image=";

		$expected = new JSONResponse([
			'state' => ITotp::STATE_CREATED,
			'secret' => 'newsecret',
			'qrUrl' => $qrUrl
		]);

		$this->assertEquals($expected, $this->controller->enable(ITotp::STATE_CREATED));
	}

	public function testEnableSecret() {
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

	public function testDisableSecret() {
		$user = $this->createMock(IUser::class);
		$this->userSession->expects($this->once())
			->method('getUser')
			->willReturn($user);
		$this->totp->expects($this->once())
			->method('deleteSecret')
			->with($user);

		$expected = new JSONResponse([
			'state' => ITotp::STATE_DISABLED,
		]);

		$this->assertEquals($expected, $this->controller->enable(ITotp::STATE_DISABLED));
	}

	public function testEnableInvalidState() {
		$user = $this->createMock(IUser::class);
		$this->userSession->expects($this->once())
			->method('getUser')
			->willReturn($user);

		$this->expectException(InvalidArgumentException::class);
		$this->controller->enable(17);
	}
}
