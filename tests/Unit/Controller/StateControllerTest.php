<?php

/*
 * SPDX-FileCopyrightText: 2025 Olav and Niklas Seyfarth, Contributors <https://github.com/datenschutz-individuell/twofactor_email/blob/master/CONTRIBUTORS.md>
 * SPDX-License-Identifier: AGPL-3.0-only
 */

namespace OCA\TwoFactorEMail\Test\Unit\Controller;

use OCA\TwoFactorEMail\AppInfo\Application;
use OCA\TwoFactorEMail\Controller\StateController;
use OCA\TwoFactorEMail\Service\IStateManager;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;
use OCP\IUser;
use OCP\IUserSession;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class StateControllerTest extends TestCase {
	private IRequest&MockObject $request;
	private IUserSession&MockObject $userSession;
	private IStateManager&MockObject $stateManager;

	private StateController $controller;

	protected function setUp(): void {
		parent::setUp();

		$this->request = $this->createMock(IRequest::class);
		$this->userSession = $this->createMock(IUserSession::class);
		$this->stateManager = $this->createMock(IStateManager::class);

		$this->controller = new StateController(
			Application::APP_ID,
			$this->request,
			$this->userSession,
			$this->stateManager,
		);
	}

	public function testDisable() {
		$user = $this->createMock(IUser::class);
		$this->userSession->expects($this->once())
			->method('getUser')
			->willReturn($user);
		$this->stateManager->expects($this->once())
			->method('disable')
			->with($user);

		$expected = new JSONResponse([
			'enabled' => false,
		]);

		$this->assertEquals($expected, $this->controller->update(false));
	}

	public function testEnableWithoutEmail() {
		$user = $this->createMock(IUser::class);
		$user->expects($this->once())
			->method('getEMailAddress')
			->willReturn(null);
		$this->userSession->expects($this->once())
			->method('getUser')
			->willReturn($user);
		$this->stateManager->expects($this->never())
			->method('enable')
			->with($user);

		$expected = new JSONResponse([
			'enabled' => false,
			'error' => 'no-email',
		]);

		$this->assertEquals($expected, $this->controller->update(true));
	}

	public function testEnableWithEmail() {
		$user = $this->createMock(IUser::class);
		$user->expects($this->once())
			->method('getEMailAddress')
			->willReturn('user@localhost');
		$this->userSession->expects($this->once())
			->method('getUser')
			->willReturn($user);
		$this->stateManager->expects($this->once())
			->method('enable')
			->with($user);

		$expected = new JSONResponse([
			'enabled' => true,
		]);

		$this->assertEquals($expected, $this->controller->update(true));
	}
}
