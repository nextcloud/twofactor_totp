<?php
/**
 * @copyright 2019 Christoph Wurst <christoph@winzerhof-wurst.at>
 *
 * @author 2019 Christoph Wurst <christoph@winzerhof-wurst.at>
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
 */

namespace OCA\TwoFactorTOTP\Test\Unit\Provider;

use ChristophWurst\Nextcloud\Testing\TestCase;
use OCA\TwoFactorTOTP\Provider\AtLoginProvider;
use OCP\IURLGenerator;
use PHPUnit\Framework\MockObject\MockObject;

class AtLoginProviderTest extends TestCase {

	/** @var IURLGenerator|MockObject */
	private $urlGenerator;

	/** @var AtLoginProvider */
	private $provider;

	protected function setUp(): void {
		parent::setUp();

		$this->urlGenerator = $this->createMock(IURLGenerator::class);

		$this->provider = new AtLoginProvider(
			$this->urlGenerator
		);
	}


	public function testGetBody() {
		// Not really anything to test, let's see if it does :boom:, though
		$this->provider->getBody();

		$this->addToAssertionCount(1);
	}
}
