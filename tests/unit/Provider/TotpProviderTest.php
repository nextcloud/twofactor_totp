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

namespace OCA\TwoFactor_Totp\Tests\Provider;

use OCA\TwoFactor_Totp\Provider\TotpProvider;
use OCA\TwoFactor_Totp\Service\ITotp;
use OCP\IL10N;
use OCP\IUser;
use Test\TestCase;

/**
 * Class TotpTest
 */
class TotpProviderTest extends TestCase {

	/** @var ITotp | \PHPUnit\Framework\MockObject\MockObject  $totp */
	private $totp;

	/** @var IL10N | \PHPUnit\Framework\MockObject\MockObject */
	private $l;

	/** @var IUser | \PHPUnit\Framework\MockObject\MockObject */
	private $user;

	/** @var TotpProvider $totpProvider */
	private $totpProvider;

	protected function setUp(): void {
		parent::setUp();

		$this->totp = $this->createMock(ITotp::class);
		$this->l = $this->createMock(IL10N::class);
		$this->user = $this->createMock(IUser::class);

		$this->totpProvider = new TotpProvider($this->totp, $this->l);
	}

	public function testVerifyChallange() {
		$this->totp->expects($this->once())
			->method('validateKey')
			->with($this->user, '111111');
		$this->totpProvider->verifyChallenge($this->user, '111111');
	}
}
