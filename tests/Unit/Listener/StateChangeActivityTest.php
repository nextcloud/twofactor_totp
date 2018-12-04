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

namespace OCA\TwoFactorTOTP\Unit\Listener;

use ChristophWurst\Nextcloud\Testing\TestCase;
use OCA\TwoFactorTOTP\Event\DisabledByAdmin;
use OCA\TwoFactorTOTP\Event\StateChanged;
use OCA\TwoFactorTOTP\Listener\StateChangeActivity;
use OCP\Activity\IEvent;
use OCP\Activity\IManager;
use OCP\IUser;

class StateChangeActivityTest extends TestCase {

	/** @var StateChangeActivity */
	private $listener;

	/** @var IManager */
	private $activityManager;

	protected function setUp() {
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
			->with('twofactor_totp')
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
			->with('twofactor_totp')
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
			->with('totp_disabled_by_admin')
			->willReturnSelf();
		$this->activityManager->expects($this->once())
			->method('publish')
			->with($activityEvent);

		$this->listener->handle($event);
	}

}