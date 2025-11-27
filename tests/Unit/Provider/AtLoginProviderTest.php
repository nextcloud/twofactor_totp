<?php

/*
 * SPDX-FileCopyrightText: 2019 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\TwoFactorTOTP\Test\Unit\Provider;

use ChristophWurst\Nextcloud\Testing\TestCase;
use OCA\TwoFactorTOTP\Provider\AtLoginProvider;
use OCP\IURLGenerator;

class AtLoginProviderTest extends TestCase {

	/** @var AtLoginProvider */
	private $provider;

	protected function setUp(): void {
		parent::setUp();

		$urlGenerator = $this->createMock(IURLGenerator::class);

		$this->provider = new AtLoginProvider(
			$urlGenerator
		);
	}


	public function testGetBody(): void {
		// Not really anything to test, let's see if it does :boom:, though
		$this->provider->getBody();

		$this->addToAssertionCount(1);
	}
}
