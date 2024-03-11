<?php

declare(strict_types=1);

/**
 * @author Nico Kluge <nico.kluge@klugecoded.com>
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

namespace OCA\TwoFactorEMail\Unit\Listener;

use ChristophWurst\Nextcloud\Testing\TestCase;
use OCA\TwoFactorEMail\Event\DisabledByAdmin;
use OCA\TwoFactorEMail\Event\StateChanged;
use OCA\TwoFactorEMail\Listener\StateChangeActivity;
use OCP\Activity\IEvent;
use OCP\Activity\IManager;
use OCP\IUser;

class StateChangeActivityTest extends TestCase {

	/** @var StateChangeActivity */
	private $listener;

	/** @var IManager */
	private $activityManager;

	protected function setUp(): void {
		parent::setUp();

		$this->activityManager = $this->createMock(IManager::class);

		$this->listener = new StateChangeActivity($this->activityManager);
	}

	public function testHandleStateEvent() {
		$uid = 'user234';
		$user = $this->createMock(IUser::class);
		$user->method('getUID')->willReturn($uid);
		$event = new StateChanged($user, true);
		$activityEvent = $this->createMock(IEvent::class);
		$this->activityManager->expects($this->once())
			->method('generateEvent')
			->willReturn($activityEvent);
		$activityEvent->expects($this->once())
			->method('setApp')
			->with('twofactor_email')
			->willReturnSelf();
		$activityEvent->expects($this->once())
			->method('setType')
			->with('security')
			->willReturnSelf();
		$activityEvent->expects($this->once())
			->method('setAuthor')
			->with($uid)
			->willReturnSelf();
		$activityEvent->expects($this->once())
			->method('setAffectedUser')
			->with($uid)
			->willReturnSelf();
		$this->activityManager->expects($this->once())
			->method('publish')
			->with($activityEvent);

		$this->listener->handle($event);
	}

	public function testHandleDisabledByAdminEvent() {
		$uid = 'user234';
		$user = $this->createMock(IUser::class);
		$user->method('getUID')->willReturn($uid);
		$event = new DisabledByAdmin($user);
		$activityEvent = $this->createMock(IEvent::class);
		$this->activityManager->expects($this->once())
			->method('generateEvent')
			->willReturn($activityEvent);
		$activityEvent->expects($this->once())
			->method('setApp')
			->with('twofactor_email')
			->willReturnSelf();
		$activityEvent->expects($this->once())
			->method('setType')
			->with('security')
			->willReturnSelf();
		$activityEvent->expects($this->once())
			->method('setAuthor')
			->with($uid)
			->willReturnSelf();
		$activityEvent->expects($this->once())
			->method('setAffectedUser')
			->with($uid)
			->willReturnSelf();
		$activityEvent->expects($this->once())
			->method('setSubject')
			->with('twofactor_email_disabled_by_admin')
			->willReturnSelf();
		$this->activityManager->expects($this->once())
			->method('publish')
			->with($activityEvent);

		$this->listener->handle($event);
	}
}
