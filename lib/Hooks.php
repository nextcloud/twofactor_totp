<?php

/**
 * @author Semih Serhat Karakaya <karakayasemi@itu.edu.tr>
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

namespace OCA\TwoFactor_Totp;

use OCA\TwoFactor_Totp\Db\TotpSecretMapper;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Class Hooks
 *
 * @package OCA\TwoFactor_Totp
 */
class Hooks {

	/**
	 * @var EventDispatcherInterface $eventDispatcher
	 */
	private $eventDispatcher;

	/**
	 * @var TotpSecretMapper $secretMapper
	 */
	private $secretMapper;

	/**
	 * Hooks constructor.
	 *
	 * @param EventDispatcherInterface $dispatcher
	 * @param TotpSecretMapper $secretMapper
	 */
	public function __construct(EventDispatcherInterface $dispatcher, TotpSecretMapper $secretMapper) {
		$this->eventDispatcher = $dispatcher;
		$this->secretMapper = $secretMapper;
	}

	public function register() {
		$this->eventDispatcher->addListener(
			'user.afterdelete',
			function (GenericEvent $event) {
				$this->afterDeleteUser($event->getArgument('uid'));
			}
		);
	}

	/**
	 * @param string $uid
	 */
	public function afterDeleteUser($uid) {
		$this->secretMapper->deleteSecretsByUserId($uid);
	}
}
