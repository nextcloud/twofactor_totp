<?php

declare(strict_types=1);

/*
 * SPDX-FileCopyrightText: 2017 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-only
 */

namespace OCA\TwoFactorEMail\Listener;

use OCA\TwoFactorEMail\Db\TwoFactorEMailMapper;
use OCP\DB\Exception as DbException;
use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;
use OCP\Accounts\UserUpdatedEvent;
use Psr\Log\LoggerInterface;

/**
 * @template-implements IEventListener<UserUpdatedEvent>
 */
class EmailDeleted implements IEventListener {

	/** @var TwoFactorEMailMapper */
	private $twoFactorEMailMapper;

	/** @var LoggerInterface */
	private $logger;

	public function __construct(TwoFactorEMailMapper $twoFactorEMailMapper, LoggerInterface $logger) {
		$this->twoFactorEMailMapper = $twoFactorEMailMapper;
		$this->logger = $logger;
	}

	public function handle(Event $event): void {
		if ($event instanceof UserUpdatedEvent && empty($event->getUser()->getEMailAddress())) {
			try {
                $this->twoFactorEMailMapper->deleteTwoFactorEMailByUserId($event->getUser()->getUID());
            } catch (DbException $e) {
				$this->logger->warning($e->getMessage(), ['uid' => $event->getUser()->getUID()]);
            }
        }
	}
}
