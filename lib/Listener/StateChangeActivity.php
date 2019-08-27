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

namespace OCA\TwoFactorTOTP\Listener;

use OCA\TwoFactorTOTP\Event\DisabledByAdmin;
use OCA\TwoFactorTOTP\Event\StateChanged;
use OCP\Activity\IManager as ActivityManager;
use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;

class StateChangeActivity implements IEventListener {

	/** @var ActivityManager */
	private $activityManager;

	public function __construct(ActivityManager $activityManager) {
		$this->activityManager = $activityManager;
	}

	public function handle(Event $event): void {
		if ($event instanceof StateChanged) {
			if ($event instanceof DisabledByAdmin) {
				$subject = 'totp_disabled_by_admin';
			} else {
				$subject = $event->isEnabled() ? 'totp_enabled_subject' : 'totp_disabled_subject';
			}
			$user = $event->getUser();

			$activity = $this->activityManager->generateEvent();
			$activity->setApp('twofactor_totp')
				->setType('security')
				->setAuthor($user->getUID())
				->setAffectedUser($user->getUID())
				->setSubject($subject);
			$this->activityManager->publish($activity);
		}
	}
}
