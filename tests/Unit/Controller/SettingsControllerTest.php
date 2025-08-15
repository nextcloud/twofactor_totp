<?php

/*
 * SPDX-FileCopyrightText: 2016 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-only
 */

namespace OCA\TwoFactorEMail\Test\Unit\Controller;

use InvalidArgumentException;
use OCA\TwoFactorEMail\Controller\SettingsController;
use OCA\TwoFactorEMail\Service\IChallengeService;
use OCA\TwoFactorEMail\Service\IStateManager;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;
use OCP\IUser;
use OCP\IUserSession;
use OCP\Security\ISecureRandom;
use PHPUnit\Framework\TestCase;

class SettingsControllerTest extends TestCase {
	private $request;
	private $userSession;
	private $emailService;
	private $stateManager;
	private $secureRandom;
	/** @var SettingsController */
	private $controller;

	protected function setUp(): void {
		parent::setUp();

		$this->request = $this->createMock(IRequest::class);
		$this->userSession = $this->createMock(IUserSession::class);
		$this->emailService = $this->createMock(IChallengeService::class);
		$this->stateManager = $this->createMock(IStateManager::class);
		$this->secureRandom = $this->createMock(ISecureRandom::class);

		$this->controller = new SettingsController('twofactor_email', $this->request, $this->userSession, $this->emailService, $this->stateManager, $this->providerState, $this->secureRandom);
	}

	public function testDisabledState() {
		$user = $this->createMock(IUser::class);
		$this->userSession->expects($this->once())
			->method('getUser')
			->willReturn($user);
		$this->stateManager->expects($this->once())
			->method('isEnabled')
			->with($user)
			->willReturn(false);

		$expected = new JSONResponse([
			'state' => false,
		]);

		$this->assertEquals($expected, $this->controller->state());
	}

	public function testCreateTwoFactorEMail() {
		$user = $this->createMock(IUser::class);
		$this->userSession->expects($this->once())
			->method('getUser')
			->willReturn($user);
		$this->emailService->expects($this->once())
			->method('setAndSendAuthCode')
			->willReturn("user@instance.com");
		$expected = new JSONResponse([
			'state' => IChallengeService::STATE_CREATED,
			'email' => 'user@instance.com'
		]);

		$this->assertEquals($expected, $this->controller->enable(IChallengeService::STATE_CREATED));
	}

	public function testEnableTwoFactorEMail() {
		$user = $this->createMock(IUser::class);
		$this->userSession->expects($this->once())
			->method('getUser')
			->willReturn($user);
		$this->stateManager->expects($this->once())
			->method('enable')
			->with($user, '123456')
			->willReturn(true);

		$expected = new JSONResponse([
			'state' => IChallengeService::STATE_ENABLED,
		]);

		$this->assertEquals($expected, $this->controller->enable(IChallengeService::STATE_ENABLED, '123456'));
	}

	public function testDisableTwoFactorEMail() {
		$user = $this->createMock(IUser::class);
		$this->userSession->expects($this->once())
			->method('getUser')
			->willReturn($user);
		$this->stateManager->expects($this->once())
			->method('disable');

		$expected = new JSONResponse([
			'state' => IChallengeService::STATE_DISABLED,
		]);

		$this->assertEquals($expected, $this->controller->enable(IChallengeService::STATE_DISABLED));
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
