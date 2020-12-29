<?php

/**
 * @author Christoph Wurst <christoph@winzerhof-wurst.at>
 * @copyright Copyright (c) 2016 Christoph Wurst <christoph@winzerhof-wurst.at>
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
use OCA\TwoFactorTOTP\Service\Totp;
use OCP\AppFramework\Http\JSONResponse;
use OCP\Defaults;
use OCP\IRequest;
use OCP\IUser;
use OCP\IUserSession;
use PHPUnit\Framework\TestCase;

class SettingsControllerTest extends TestCase {
	private $request;
	private $userSession;
	private $totp;
	private $defaults;

	/** @var SettingsController */
	private $controller;

	protected function setUp() {
		parent::setUp();

		$this->request = $this->createMock(IRequest::class);
		$this->userSession = $this->createMock(IUserSession::class);
		$this->totp = $this->createMock(Totp::class);
		$this->defaults = new Defaults();

		$this->controller = new SettingsController('twofactor_totp', $this->request, $this->userSession, $this->totp, $this->defaults);
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

		$expected = new JSONResponse([
			'state' => false,
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
			->with($user)
			->willReturn('newsecret');
		$issuer = rawurlencode($this->defaults->getName());
		$expected = new JSONResponse([
			'state' => ITotp::STATE_CREATED,
			'secret' => 'newsecret',
			'qrUrl' => "otpauth://totp/$issuer%3Auser%40instance.com?secret=newsecret&issuer=$issuer",
		]);

		$this->assertEquals($expected, $this->controller->enable(true));
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
			->method('deleteSecret');

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
