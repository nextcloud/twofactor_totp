<?php

declare(strict_types=1);

/*
 * SPDX-FileCopyrightText: 2025 Olav and Niklas Seyfarth, Contributors <https://github.com/datenschutz-individuell/twofactor_email/blob/master/CONTRIBUTORS.md>
 * SPDX-License-Identifier: AGPL-3.0-only
 */

namespace OCA\TwoFactorEMail\Test\Unit\Event;

use ChristophWurst\Nextcloud\Testing\TestCase;
use OCA\TwoFactorEMail\Event\StateChanged;
use OCP\IUser;

class StateChangedTest extends TestCase {
	public function testEnabled() {
		$user = $this->createMock(IUser::class);
		$event = new StateChanged($user, true);

		$this->assertTrue($event->isEnabled());
	}

	public function testDisabled() {
		$user = $this->createMock(IUser::class);
		$event = new StateChanged($user, false);

		$this->assertFalse($event->isEnabled());
	}
}
