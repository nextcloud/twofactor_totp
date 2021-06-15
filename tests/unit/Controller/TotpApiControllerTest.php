<?php

/**
 * @author Semih Serhat Karakaya <karakayasemi@itu.edu.tr>
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

namespace OCA\TwoFactor_Totp\Unit\Controller;

use OCA\TwoFactor_Totp\Controller\TotpApiController;
use OCA\TwoFactor_Totp\Exception\NoTotpSecretFoundException;
use OCA\TwoFactor_Totp\Service\ITotp;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataResponse;
use OCP\ILogger;
use OCP\IRequest;
use OCP\IUser;
use OCP\IUserManager;
use Test\TestCase;

class TotpApiControllerTest extends TestCase {

	/** @var IRequest | PHPUnit\Framework\MockObject\MockObject */
	private $request;

	/** @var IUserManager | PHPUnit\Framework\MockObject\MockObject */
	private $userManager;

	/** @var IUser | PHPUnit\Framework\MockObject\MockObject */
	private $user;

	/** @var ITotp | PHPUnit\Framework\MockObject\MockObject */
	private $totp;

	/** @var ILogger | PHPUnit\Framework\MockObject\MockObject */
	private $logger;

	/** @var TotpApiController */
	private $controller;

	protected function setUp(): void {
		parent::setUp();
		$this->user = $this->createMock(IUser::class);
		$this->request = $this->createMock(IRequest::class);
		$this->userManager = $this->createMock(IUserManager::class);
		$this->totp = $this->createMock(ITotp::class);
		$this->logger = $this->createMock(ILogger::class);

		$this->controller = new TotpApiController(
			'twofactor_totp',
			$this->request,
			$this->totp,
			$this->userManager,
			$this->logger
		);
	}

	/**
	 * @dataProvider dataTestValidateKey
	 *
	 * @param string $uid
	 * @param boolean $result
	 */
	public function testValidateKey($uid, $result) {
		$this->userManager->expects($this->once())
			->method('get')
			->with($uid)
			->will($this->returnValue($this->user));
		$this->totp->expects($this->once())
			->method('validateKey')
			->with($this->user, '111111')
			->will($this->returnValue($result));

		$expected =  new DataResponse(['data' => ['result' => $result]]);
		$this->assertEquals($expected, $this->controller->validateKey($uid, '111111'));
	}

	public function dataTestValidateKey() {
		return [
			['testuser', false],
			['testuser', true],
		];
	}

	public function testValidateKeyUserNotExist() {
		$this->userManager->expects($this->once())
			->method('get')
			->with('notexist')
			->will($this->returnValue(null));
		$expected =  new DataResponse(
			[
				'statuscode' => 404,
				'data' => ['result' => false]
			],
			Http::STATUS_NOT_FOUND
		);
		$this->assertEquals($expected, $this->controller->validateKey('notexist', '111111'));
	}

	public function testValidateKeySecretNotExist() {
		$exception = new NoTotpSecretFoundException();
		$this->user = $this->createMock(IUser::class);
		$this->userManager->expects($this->once())
			->method('get')
			->with('testuser')
			->will($this->returnValue($this->user));
		$this->totp->expects($this->once())
			->method('validateKey')
			->with($this->user, '111111')
			->will($this->throwException($exception));
		$this->logger->expects($this->once())
			->method('logException')
			->with($exception);
		$expected =  new DataResponse(
			[
				'statuscode' => 404,
				'data' => ['result' => false]
			],
			Http::STATUS_NOT_FOUND
		);
		$this->assertEquals($expected, $this->controller->validateKey('testuser', '111111'));
	}
}
