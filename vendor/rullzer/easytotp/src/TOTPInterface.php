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

interface TOTPInterface {

	public const HASH_SHA1 = 'sha1';
	public const HASH_SHA256 = 'sha256';
	public const HASH_SHA512 = 'sha512';

	/**
	 * @param string $otp The one time password to verify
	 * @param int $drift How many windows to look back and ahead
	 * @param int $lastKnownCounter The last known counter value (can be obtained from TOTPValidResultInterface). This
	 * avoid reuse of the same token (which is forbidden by the RFC.
	 * @return mixed
	 */
	public function verify(string $otp, int $drift = 1, ?int $lastKnownCounter = null): TOTPResultInterface;

	public function getDigits(): int;

	public function getHashFunction(): string;

	public function getOffset(): int;

	public function getSecret(): string;

	public function getTimeStep(): int;
}
