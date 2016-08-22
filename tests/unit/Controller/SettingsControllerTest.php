<?php

/**
 * @author Christoph Wurst <christoph@winzerhof-wurst.at>
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

use Endroid\QrCode\QrCode;
use OCA\TwoFactor_Totp\Controller\SettingsController;
use OCP\Defaults;
use Test\TestCase;

class SettingsControllerTest extends TestCase {

	private $request;
	private $userSession;
	private $totp;
	private $defaults;

	/** @var SettingsController */
	private $controller;

	protected function setUp() {
		parent::setUp();

		$this->request = $this->getMock('\OCP\IRequest');
		$this->userSession = $this->getMock('\OCP\IUserSession');
		$this->totp = $this->getMock('\OCA\TwoFactor_Totp\Service\ITotp');
		$this->defaults = new Defaults();

		$this->controller = new SettingsController('twofactor_totp', $this->request, $this->userSession, $this->totp, $this->defaults);
	}

	public function testNothing() {
		$user = $this->getMock('\OCP\IUser');
		$this->userSession->expects($this->once())
			->method('getUser')
			->will($this->returnValue($user));
		$this->totp->expects($this->once())
			->method('hasSecret')
			->with($user)
			->will($this->returnValue(true));

		$expected = [
			'enabled' => true,
		];

		$this->assertEquals($expected, $this->controller->state());
	}

	public function testEnable() {
		$user = $this->getMock('\OCP\IUser');
		$this->userSession->expects($this->exactly(2))
			->method('getUser')
			->will($this->returnValue($user));
		$user->expects($this->once())
			->method('getCloudId')
			->will($this->returnValue('user@instance.com'));
		$this->totp->expects($this->once())
			->method('createSecret')
			->with($user)
			->will($this->returnValue('newsecret'));

		$qrCode = new QrCode();
		$issuer = rawurlencode($this->defaults->getName());
		$qr = $qrCode->setText("otpauth://totp/user%40instance.com?secret=newsecret&issuer=$issuer")
			->setSize(150)
			->getDataUri();

		$expected = [
			'enabled' => true,
			'secret' => 'newsecret',
			'qr' => $qr,
		];

		$this->assertEquals($expected, $this->controller->enable(true));
	}

	public function testEnableDisable() {
		$user = $this->getMock('\OCP\IUser');
		$this->userSession->expects($this->once())
			->method('getUser')
			->will($this->returnValue($user));
		$this->totp->expects($this->once())
			->method('deleteSecret');

		$expected = [
			'enabled' => false,
		];

		$this->assertEquals($expected, $this->controller->enable(false));
	}

}
