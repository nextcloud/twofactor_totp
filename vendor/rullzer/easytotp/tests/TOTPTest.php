<?php
declare(strict_types=1);
/**
 * @copyright Copyright (c) 2019, Roeland Jago Douma <roeland@famdouma.nl>
 *
 * @author Roeland Jago Douma <roeland@famdouma.nl>
 *
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace EasyTOTP\Tests;

use EasyTOTP\TimeService;
use EasyTOTP\TOTP;
use EasyTOTP\TOTPInterface;
use EasyTOTP\TOTPInvalidResultInterface;
use EasyTOTP\TOTPValidResultInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Otp test case.
 */
class TOTPTest extends TestCase
{
	/** @var string */
	private $secret = "12345678901234567890";

	/** @var TimeService|MockObject */
	private $timeService;

	/**
	 * Prepares the environment before running a test.
	 */
	protected function setUp(): void {
		parent::setUp();

		$this->timeService = $this->createMock(TimeService::class);
	}

	/**
	 * Invalid counter values for tests
	 */
	public function totpTestValues()
	{
		return [
			[59,          '94287082', TOTPInterface::HASH_SHA1,   1],
			[59,          '46119246', TOTPInterface::HASH_SHA256, 1],
			[59,          '90693936', TOTPInterface::HASH_SHA512, 1],
			[1111111109,  '07081804', TOTPInterface::HASH_SHA1,   37037036],
			[1111111109,  '68084774', TOTPInterface::HASH_SHA256, 37037036],
			[1111111109,  '25091201', TOTPInterface::HASH_SHA512, 37037036],
			[1111111111,  '14050471', TOTPInterface::HASH_SHA1,   37037037],
			[1111111111,  '67062674', TOTPInterface::HASH_SHA256, 37037037],
			[1111111111,  '99943326', TOTPInterface::HASH_SHA512, 37037037],
			[1234567890,  '89005924', TOTPInterface::HASH_SHA1,   41152263],
			[1234567890,  '91819424', TOTPInterface::HASH_SHA256, 41152263],
			[1234567890,  '93441116', TOTPInterface::HASH_SHA512, 41152263],
			[2000000000,  '69279037', TOTPInterface::HASH_SHA1,   66666666],
			[2000000000,  '90698825', TOTPInterface::HASH_SHA256, 66666666],
			[2000000000,  '38618901', TOTPInterface::HASH_SHA512, 66666666],
			[20000000000, '65353130', TOTPInterface::HASH_SHA1,   666666666],
			[20000000000, '77737706', TOTPInterface::HASH_SHA256, 666666666],
			[20000000000, '47863826', TOTPInterface::HASH_SHA512, 666666666],
		];
	}

	/**
	 * @dataProvider totpTestValues
	 *
	 * @param int $time
	 * @param string $otp
	 * @param string $hashFunction
	 */
	public function testTOTPRFC(int $time, string $otp, string $hashFunction) {
		$this->timeService->method('getTime')
			->willReturn($time);

		$totp = new TOTP(
			$this->prepareSecret($this->prepareSecret($this->secret, $hashFunction), $hashFunction),
			30,
			8,
			0,
			$hashFunction,
			$this->timeService);

		$result = $totp->verify($otp, 0);
		$this->assertInstanceOf(TOTPValidResultInterface::class, $result);
	}

	/**
	 * @dataProvider totpTestValues
	 *
	 * @param int $time
	 * @param string $otp
	 * @param string $hashFunction
	 */
	public function testTimeDriftBack(int $time, string $otp, string $hashFunction) {
		$this->timeService->method('getTime')
			->willReturn($time-30);

		$totp = new TOTP(
			$this->prepareSecret($this->secret, $hashFunction),
			30,
			8,
			0,
			$hashFunction,
			$this->timeService);

		$result = $totp->verify($otp, 1);
		$this->assertInstanceOf(TOTPValidResultInterface::class, $result);
	}

	/**
	 * @dataProvider totpTestValues
	 *
	 * @param int $time
	 * @param string $otp
	 * @param string $hashFunction
	 * @param int $counter
	 */
	public function testTimeDriftForward(int $time, string $otp, string $hashFunction, int $counter) {
		$this->timeService->method('getTime')
			->willReturn($time+30);

		$totp = new TOTP(
			$this->prepareSecret($this->secret, $hashFunction),
			30,
			8,
			0,
			$hashFunction,
			$this->timeService);

		$result = $totp->verify($otp, 1);
		$this->assertInstanceOf(TOTPValidResultInterface::class, $result);
		$this->assertSame($counter, $result->getCounter());
	}

	/**
	 * @dataProvider totpTestValues
	 *
	 * @param int $time
	 * @param string $otp
	 * @param string $hashFunction
	 * @param int $counter
	 */
	public function testTimeDriftOnlyForwardFails(int $time, string $otp, string $hashFunction, int $counter) {
		$this->timeService->method('getTime')
			->willReturn($time);

		$totp = new TOTP(
			$this->prepareSecret($this->secret, $hashFunction),
			30,
			8,
			0,
			$hashFunction,
			$this->timeService);

		$result = $totp->verify($otp, 1, $counter+1);
		$this->assertInstanceOf(TOTPInvalidResultInterface::class, $result);
	}

	/**
	 * @dataProvider totpTestValues
	 *
	 * @param int $time
	 * @param string $otp
	 * @param string $hashFunction
	 * @param int $counter
	 */
	public function testTimeDriftOnlyForwardSuccess(int $time, string $otp, string $hashFunction, int $counter) {
		$this->timeService->method('getTime')
			->willReturn($time);

		$totp = new TOTP(
			$this->prepareSecret($this->secret, $hashFunction),
			30,
			8,
			0,
			$hashFunction,
			$this->timeService);

		$result = $totp->verify($otp, 1, $counter-1);
		$this->assertInstanceOf(TOTPValidResultInterface::class, $result);
		$this->assertSame($counter, $result->getCounter());
		$this->assertSame(0, $result->getDrift());
	}

	/**
	 * @dataProvider totpTestValues
	 *
	 * @param int $time
	 * @param string $otp
	 * @param string $hashFunction
	 * @param int $counter
	 */
	public function testTimeDriftClockSlow(int $time, string $otp, string $hashFunction, int $counter) {
		$this->timeService->method('getTime')
			->willReturn($time+30);

		$totp = new TOTP(
			$this->prepareSecret($this->secret, $hashFunction),
			30,
			8,
			0,
			$hashFunction,
			$this->timeService);

		$result = $totp->verify($otp, 1);
		$this->assertInstanceOf(TOTPValidResultInterface::class, $result);
		$this->assertSame($counter, $result->getCounter());
		$this->assertSame(-1, $result->getDrift());
	}

	/**
	 * @dataProvider totpTestValues
	 *
	 * @param int $time
	 * @param string $otp
	 * @param string $hashFunction
	 * @param int $counter
	 */
	public function testTimeDriftClockFase(int $time, string $otp, string $hashFunction, int $counter) {
		$this->timeService->method('getTime')
			->willReturn($time-30);

		$totp = new TOTP(
			$this->prepareSecret($this->secret, $hashFunction),
			30,
			8,
			0,
			$hashFunction,
			$this->timeService);

		$result = $totp->verify($otp, 1);
		$this->assertInstanceOf(TOTPValidResultInterface::class, $result);
		$this->assertSame($counter, $result->getCounter());
		$this->assertSame(1, $result->getDrift());
	}

	private function prepareSecret(string $secret, string $hashFunction) {
		if ($hashFunction === TOTPInterface::HASH_SHA1) {
			$secretLength = 20;
		} else if ($hashFunction === TOTPInterface::HASH_SHA256) {
			$secretLength = 32;
		} else if ($hashFunction === TOTPInterface::HASH_SHA512) {
			$secretLength = 64;
		} else {
			$this->fail('Invalid hash function');
		}

		while(strlen($secret) < $secretLength) {
			$secret .= $secret;
		}

		$secret = substr($secret, 0, $secretLength);
		return $secret;
	}
}
