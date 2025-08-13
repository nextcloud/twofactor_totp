<?php

declare(strict_types=1);

/*
 * SPDX-FileCopyrightText: 2017 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-only
 */

namespace OCA\TwoFactorEMail\Listener;

use OCA\TwoFactorEMail\Db\TwoFactorEMailMapper;
use OCA\TwoFactorEMail\Provider\EMailProvider;
use OCP\Authentication\TwoFactorAuth\IRegistry;
use OCP\DB\Exception as DbException;
use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;
use OCP\Accounts\UserUpdatedEvent;
use Psr\Log\LoggerInterface;

/**
 * @template-implements IEventListener<UserUpdatedEvent>
 */
class EmailDeleted implements IEventListener {

	public function __construct(
		private EMailProvider $provider,
		private IRegistry $registry,
	) {
	}

	public function handle(Event $event): void {
		if ($event instanceof UserUpdatedEvent && empty($event->getUser()->getEMailAddress())) {
			$this->registry->disableProviderFor($this->provider, $event->getUser());
        }
	}
}
