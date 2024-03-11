<?php

declare(strict_types=1);

/**
 * @copyright Copyright (c) 2022 Daniel Kesselberg <mail@danielkesselberg.de>
 *
 * @author Nico Kluge <nico.kluge@klugecoded.com>
 * @author Daniel Kesselberg <mail@danielkesselberg.de>
 *
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace OCA\TwoFactorEMail\Listener;

use OCA\TwoFactorEMail\Db\TwoFactorEMailMapper;
use OCP\DB\Exception;
use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;
use OCP\User\Events\UserDeletedEvent;
use Psr\Log\LoggerInterface;

/**
 * @template-implements IEventListener<UserDeletedEvent>
 */
class UserDeleted implements IEventListener {

	/** @var TwoFactorEMailMapper */
	private $twoFactorEMailMapper;

	/** @var LoggerInterface */
	private $logger;

	public function __construct(TwoFactorEMailMapper $twoFactorEMailMapper, LoggerInterface $logger) {
		$this->twoFactorEMailMapper = $twoFactorEMailMapper;
		$this->logger = $logger;
	}

	public function handle(Event $event): void {
		if ($event instanceof UserDeletedEvent) {
			try {
				$this->twoFactorEMailMapper->deleteTwoFactorEMailByUserId($event->getUser()->getUID());
			} catch (Exception $e) {
				$this->logger->warning($e->getMessage(), ['uid' => $event->getUser()->getUID()]);
			}
		}
	}
}
