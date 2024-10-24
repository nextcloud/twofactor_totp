<?php
/*
 * SPDX-FileCopyrightText: 2019 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
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
