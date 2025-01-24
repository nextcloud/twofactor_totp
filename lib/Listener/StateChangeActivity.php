<?php

declare(strict_types=1);

/*
 * SPDX-FileCopyrightText: 2018 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-only
 */

namespace OCA\TwoFactorTOTP\Listener;

use OCA\TwoFactorTOTP\Event\DisabledByAdmin;
use OCA\TwoFactorTOTP\Event\StateChanged;
use OCP\Activity\IManager as ActivityManager;
use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;

/**
 * @template-implements IEventListener<StateChanged>
 */
class StateChangeActivity implements IEventListener {

	public function __construct(
		private ActivityManager $activityManager,
	) {
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
