<?php

declare(strict_types=1);

/*
 * SPDX-FileCopyrightText: 2018 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-only
 */

namespace OCA\TwoFactorEMail\Listener;

use OCA\TwoFactorEMail\AppInfo\Application;
use OCA\TwoFactorEMail\Event\DisabledByAdmin;
use OCA\TwoFactorEMail\Event\StateChanged;
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
				$subject = 'twofactor_email_disabled_by_admin';
			} else {
				$subject = $event->isEnabled() ? 'twofactor_email_enabled_subject' : 'twofactor_email_disabled_subject';
			}
			$user = $event->getUser();

			$activity = $this->activityManager->generateEvent();
			$activity->setApp(Application::APP_ID)
				->setType('security')
				->setAuthor($user->getUID())
				->setAffectedUser($user->getUID())
				->setSubject($subject);
			$this->activityManager->publish($activity);
		}
	}
}
