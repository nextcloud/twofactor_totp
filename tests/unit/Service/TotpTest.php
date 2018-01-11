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

namespace OCA\TwoFactor_Totp\Tests\Service;

use OCA\TwoFactor_Totp\Db\TotpSecret;
use OCA\TwoFactor_Totp\Db\TotpSecretMapper;
use OCA\TwoFactor_Totp\Exception\NoTotpSecretFoundException;
use OCA\TwoFactor_Totp\Service\Totp;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\IUser;
use OCP\Security\ICrypto;
use Otp\Otp;
use Test\TestCase;

/**
 * Class TotpTest
 */
class TotpTest extends TestCase {

	/** @var TotpSecretMapper | \PHPUnit_Framework_MockObject_MockObject */
	private $secretMapper;

	/** @var ICrypto | \PHPUnit_Framework_MockObject_MockObject */
	private $crypto;

	/** @var Otp | \PHPUnit_Framework_MockObject_MockObject  */
	private $otp;

	/** @var Totp */
	private $totp;

	protected function setUp() {
		parent::setUp();

		$this->secretMapper = $this->createMock(TotpSecretMapper::class);
		$this->crypto = $this->createMock(ICrypto::class);
		$this->otp = $this->createMock(Otp::class);

		$this->totp = new Totp($this->secretMapper, $this->crypto, $this->otp);
	}

	/**
	 * @dataProvider validationProvider
	 * @param string $lastKey
	 * @param string $key
	 * @param boolean $validationResult
	 * @param boolean $expectedResult
	 */
	public function testValidateKey($lastKey, $key, $validationResult, $expectedResult) {
		/** @var IUser | \PHPUnit_Framework_MockObject_MockObject $user  */
		$user = $this->createMock(IUser::class);
		$dbSecret = $this
			->getMockBuilder(TotpSecret::class)
			->setMethods(['getSecret', 'getLastValidatedKey', 'setLastValidatedKey'])
			->getMock();

		$dbSecret->expects($this->once())
			->method('getLastValidatedKey')
			->will($this->returnValue($lastKey));
		$this->secretMapper->expects($this->once())
			->method('getSecret')
			->with($user)
			->will($this->returnValue($dbSecret));

		if ($lastKey !== $key) {
			$dbSecret->expects($this->once())
				->method('getSecret')
				->will($this->returnValue('secret'));
			$this->otp->expects($this->once())
				->method('checkTotp')
				->will($this->returnValue($validationResult));
		}
		if ($expectedResult === true) {
			$dbSecret->expects($this->once())
				->method('setLastValidatedKey')
				->with($key);
			$this->secretMapper->expects($this->once())
				->method('update')
				->with($dbSecret);
		}
		$this->assertEquals($this->totp->validateKey($user, $key), $expectedResult);
	}

	public function validationProvider() {
		return [
			[ '000000', '000000', true, false ],
			[ '000000', '000000', false, false ],
			[ '000000', '000001', false, false ],
			[ '000000', '000001', true, true ]
		];
	}

	public function testValidateSecretNoSecret() {
		/** @var IUser | \PHPUnit_Framework_MockObject_MockObject $user  */
		$user = $this->createMock(IUser::class);

		$this->secretMapper->expects($this->once())
			->method('getSecret')
			->with($user)
			->will($this->throwException(new DoesNotExistException('')));
		$this->expectException(NoTotpSecretFoundException::class);
		$this->totp->validateKey($user, 'testkey');
	}
}
