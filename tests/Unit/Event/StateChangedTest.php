<?php

declare(strict_types=1);

/*
 * SPDX-FileCopyrightText: 2018 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-only
 */

namespace OCA\TwoFactorTOTP\Unit\Event;

use ChristophWurst\Nextcloud\Testing\TestCase;
use OCA\TwoFactorTOTP\Event\StateChanged;
use OCP\IUser;

class StateChangedTest extends TestCase {
	public function testEnabled(): void {
		$user = $this->createMock(IUser::class);
		$event = new StateChanged($user, true);

		$enabled = $event->isEnabled();

		$this->assertTrue($enabled);
	}

	public function testDisabled(): void {
		$user = $this->createMock(IUser::class);
		$event = new StateChanged($user, false);

		$enabled = $event->isEnabled();

		$this->assertFalse($enabled);
	}
}
