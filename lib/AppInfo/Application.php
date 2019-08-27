<?php

declare(strict_types=1);

/**
 * @author Christoph Wurst <christoph@winzerhof-wurst.at>
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

namespace OCA\TwoFactorTOTP\AppInfo;

use OCA\TwoFactorTOTP\Event\DisabledByAdmin;
use OCA\TwoFactorTOTP\Event\StateChanged;
use OCA\TwoFactorTOTP\Listener\IListener;
use OCA\TwoFactorTOTP\Listener\StateChangeActivity;
use OCA\TwoFactorTOTP\Listener\StateChangeRegistryUpdater;
use OCA\TwoFactorTOTP\Service\ITotp;
use OCA\TwoFactorTOTP\Service\Totp;
use OCP\AppFramework\App;
use OCP\EventDispatcher\IEventDispatcher;

class Application extends App {

	const APP_ID = 'twofactor_totp';

	public function __construct(array $urlParams = []) {
		parent::__construct(self::APP_ID, $urlParams);

		$container = $this->getContainer();
		$container->registerAlias(ITotp::class, Totp::class);

		/** @var IEventDispatcher $dispatcher */
		$dispatcher = $container->query(IEventDispatcher::class);
		$dispatcher->addServiceListener(StateChanged::class, StateChangeActivity::class);
		$dispatcher->addServiceListener(StateChanged::class, StateChangeRegistryUpdater::class);
		$dispatcher->addServiceListener(DisabledByAdmin::class, StateChangeActivity::class);
	}

}
