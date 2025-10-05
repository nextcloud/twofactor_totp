<?php

declare(strict_types=1);

/*
 * SPDX-FileCopyrightText: 2025 Olav and Niklas Seyfarth, Contributors <https://github.com/datenschutz-individuell/twofactor_email/blob/master/CONTRIBUTORS.md>
 * SPDX-License-Identifier: AGPL-3.0-only
 */

namespace OCA\TwoFactorEMail\Listener;

use OCA\TwoFactorEMail\Service\IStateManager;
use OCP\Accounts\UserUpdatedEvent;
use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;

/**
 * @template-implements IEventListener<UserUpdatedEvent>
 */
final class EMailDeleted implements IEventListener {

	public function __construct(
		private IStateManager $service,
	) {
	}

	public function handle(Event $event): void {
		if ($event instanceof UserUpdatedEvent && $event->getUser()->getEMailAddress() === null) {
			$this->service->disable($event->getUser());
		}
	}
}
