<?php

declare(strict_types=1);

/**
 * @author Nico Kluge <nico.kluge@klugecoded.com>
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

namespace OCA\TwoFactorEMail\AppInfo;

use OCA\TwoFactorEMail\Event\DisabledByAdmin;
use OCA\TwoFactorEMail\Event\StateChanged;
use OCA\TwoFactorEMail\Listener\EmailDeleted;
use OCA\TwoFactorEMail\Listener\StateChangeActivity;
use OCA\TwoFactorEMail\Listener\StateChangeRegistryUpdater;
use OCA\TwoFactorEMail\Listener\UserDeleted;
use OCA\TwoFactorEMail\Service\IEMailService;
use OCA\TwoFactorEMail\Service\EMailService;
use OCP\Accounts\UserUpdatedEvent;
use OCP\AppFramework\App;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\AppFramework\Bootstrap\IRegistrationContext;
use OCP\User\Events\UserDeletedEvent;

class Application extends App implements IBootstrap {
	public const APP_ID = 'twofactor_email';

	public function __construct() {
		parent::__construct(self::APP_ID);
	}

	public function register(IRegistrationContext $context): void {
		include_once __DIR__ . '/../../vendor/autoload.php';

		$context->registerServiceAlias(IEMailService::class, EMailService::class);

		$context->registerEventListener(StateChanged::class, StateChangeActivity::class);
		$context->registerEventListener(StateChanged::class, StateChangeRegistryUpdater::class);
		$context->registerEventListener(DisabledByAdmin::class, StateChangeActivity::class);
		$context->registerEventListener(UserDeletedEvent::class, UserDeleted::class);
		$context->registerEventListener(UserUpdatedEvent::class, EmailDeleted::class);
	}

	public function boot(IBootContext $context): void {
	}
}
