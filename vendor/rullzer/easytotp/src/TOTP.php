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

namespace EasyTOTP;

class TOTP implements TOTPInterface  {

	/** @var string */
	private $secret;
	/** @var int */
	private $digits;
	/** @var int */
	private $offset;
	/** @var int */
	private $timeStep;
	/** @var string */
	private $hashFunction;
	/** @var TimeService */
	private $timeService;

	public function __construct(string $secret, int $timeStep, int $digits, int $offset, string $hashFunction, TimeService $timeService) {
		$this->secret = $secret;
		$this->timeStep = $timeStep;
		$this->digits = $digits;
		$this->offset = $offset;
		$this->hashFunction = $hashFunction;
		$this->timeService = $timeService;
	}

	public function verify(string $otp, int $drift = 1, ?int $lastKnownCounter = null): TOTPResultInterface {
		$currentCounter = $this->getCurrentCounter();

		$start = $currentCounter - $drift;
		$end = $currentCounter + $drift;

		for ($i = $start; $i <= $end; $i++) {
			// Skip counters smaller than the minimum
			if ($lastKnownCounter !== null && $i <= $lastKnownCounter) {
				continue;
			}

			if (hash_equals($this->hotp($i), $otp)) {
				return new TOTPValidResult(
					$i,
					$i - $currentCounter
				);
			}
		}

		return new TOTPInvalidResult();
	}

	public function getDigits(): int {
		return $this->digits;
	}

	public function getHashFunction(): string {
		return $this->hashFunction;
	}

	public function getOffset(): int {
		return $this->offset;
	}

	public function getSecret(): string {
		return $this->secret;
	}

	public function getTimeStep(): int {
		return $this->timeStep;
	}

	private function binaryCounter(int $counter): string {
		if (PHP_INT_SIZE === 4) {
			/*
			 * Manually do 64bit magic
			 * This will do boom in 2038 ;)
			 */
			return pack('N*', 0) . pack('N*', $counter);
		}

		return pack('J', $counter);
	}

	/**
	 * See https://tools.ietf.org/html/rfc4226#section-5
	 */
	private function hotp(int $counter): string {
		$hash = hash_hmac(
			$this->hashFunction,
			$this->binaryCounter($counter),
			$this->secret,
			true
		);

		return str_pad((string)$this->truncate($hash), $this->digits, '0', STR_PAD_LEFT);
	}

	private function truncate(string $hash): int {
		$offset = \ord($hash[strlen($hash)-1]) & 0xf;

		return (
				((\ord($hash[$offset + 0]) & 0x7f) << 24) |
				((\ord($hash[$offset + 1]) & 0xff) << 16) |
				((\ord($hash[$offset + 2]) & 0xff) << 8) |
				(\ord($hash[$offset + 3]) & 0xff)
			) % (10 ** $this->digits);
	}

	private function getCurrentCounter(): int {
		return (int)floor(($this->timeService->getTime() + $this->offset) / $this->timeStep);
	}

}
