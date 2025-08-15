<?php

declare(strict_types=1);

/*
 * SPDX-FileCopyrightText: 2017 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-only
 */

namespace OCA\TwoFactorEMail\Listener;

use OCA\TwoFactorEMail\Service\IStateManager;
use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;
use OCP\Accounts\UserUpdatedEvent;

/**
 * @template-implements IEventListener<UserUpdatedEvent>
 */
class EmailDeleted implements IEventListener {

	public function __construct(
		private IStateManager $service,
	) {
	}

	public function handle(Event $event): void {
		if ($event instanceof UserUpdatedEvent && empty($event->getUser()->getEMailAddress())) {
			$this->service->disable($event->getUser());
        }
	}
}
