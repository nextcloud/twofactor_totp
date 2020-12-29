<?php

declare(strict_types=1);

/**
 * @author Christoph Wurst <christoph@winzerhof-wurst.at>
 * @copyright Copyright (c) 2018 Christoph Wurst <christoph@winzerhof-wurst.at>
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

namespace OCA\TwoFactorTOTP\Unit\Event;

use ChristophWurst\Nextcloud\Testing\TestCase;
use OCA\TwoFactorTOTP\Event\StateChanged;
use OCP\IUser;

class StateChangedTest extends TestCase {
	public function testEnabled() {
		$user = $this->createMock(IUser::class);
		$event = new StateChanged($user, true);

		$enabled = $event->isEnabled();

		$this->assertTrue($enabled);
	}

	public function testDisabled() {
		$user = $this->createMock(IUser::class);
		$event = new StateChanged($user, false);

		$enabled = $event->isEnabled();

		$this->assertFalse($enabled);
	}
}
