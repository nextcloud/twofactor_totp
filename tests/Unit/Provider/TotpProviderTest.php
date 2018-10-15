<?php

declare(strict_types=1);

/**
 * @copyright 2018 Christoph Wurst <christoph@winzerhof-wurst.at>
 *
 * @author 2018 Christoph Wurst <christoph@winzerhof-wurst.at>
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

namespace OCA\TwoFactorTOTP\Test\Unit\Provider;

use ChristophWurst\Nextcloud\Testing\TestCase;
use OCA\TwoFactorTOTP\Provider\TotpProvider;
use OCA\TwoFactorTOTP\Service\ITotp;
use OCA\TwoFactorTOTP\Settings\Personal;
use OCP\IL10N;
use OCP\IUser;
use PHPUnit\Framework\MockObject\MockObject;

class TotpProviderTest extends TestCase {

	/** @var ITotp|MockObject */
	private $totp;

	/** @var IL10N|MockObject */
	private $l10n;

	/** @var TotpProvider */
	private $provider;

	protected function setUp() {
		parent::setUp();

		$this->totp = $this->createMock(ITotp::class);
		$this->l10n = $this->createMock(IL10N::class);

		$this->provider = new TotpProvider(
			$this->totp,
			$this->l10n
		);
	}

	public function testGetId() {
		$expectedId = 'totp';

		$id = $this->provider->getId();

		$this->assertEquals($expectedId, $id);
	}

	public function testGetDisplayName() {
		$expected = 'TOTP (Authenticator app)';

		$displayName = $this->provider->getDisplayName();

		$this->assertEquals($expected, $displayName);
	}

	public function testGetDescription() {
		$description = 'Authenticate with a TOTP app';
		$this->l10n->expects($this->once())
			->method('t')
			->willReturnArgument(0);

		$descr = $this->provider->getDescription();

		$this->assertEquals($description, $descr);
	}

	public function testGetLightIcon() {
		$expected = image_path('twofactor_totp', 'app.svg');

		$icon = $this->provider->getLightIcon();

		$this->assertEquals($expected, $icon);
	}

	public function testGetDarkIcon() {
		$expected = image_path('twofactor_totp', 'app-dark.svg');

		$icon = $this->provider->getDarkIcon();

		$this->assertEquals($expected, $icon);
	}

	public function testGetPersonalSettings() {
		$expected = new Personal(ITotp::STATE_ENABLED);
		$user = $this->createMock(IUser::class);
		$this->totp->expects($this->once())
			->method('hasSecret')
			->with($user)
			->willReturn(true);

		$actual = $this->provider->getPersonalSettings($user);

		$this->assertEquals($expected, $actual);
	}

}
