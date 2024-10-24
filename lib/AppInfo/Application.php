<?php

declare(strict_types=1);

/*
 * SPDX-FileCopyrightText: 2016 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-only
 */

namespace OCA\TwoFactorTOTP\AppInfo;

use OCA\TwoFactorTOTP\Event\DisabledByAdmin;
use OCA\TwoFactorTOTP\Event\StateChanged;
use OCA\TwoFactorTOTP\Listener\StateChangeActivity;
use OCA\TwoFactorTOTP\Listener\StateChangeRegistryUpdater;
use OCA\TwoFactorTOTP\Listener\UserDeleted;
use OCA\TwoFactorTOTP\Service\ITotp;
use OCA\TwoFactorTOTP\Service\Totp;
use OCP\AppFramework\App;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\AppFramework\Bootstrap\IRegistrationContext;
use OCP\User\Events\UserDeletedEvent;

class Application extends App implements IBootstrap {
	public const APP_ID = 'twofactor_totp';

	public function __construct() {
		parent::__construct(self::APP_ID);
	}

	public function register(IRegistrationContext $context): void {
		include_once __DIR__ . '/../../vendor/autoload.php';

		$context->registerServiceAlias(ITotp::class, Totp::class);

		$context->registerEventListener(StateChanged::class, StateChangeActivity::class);
		$context->registerEventListener(StateChanged::class, StateChangeRegistryUpdater::class);
		$context->registerEventListener(DisabledByAdmin::class, StateChangeActivity::class);
		$context->registerEventListener(UserDeletedEvent::class, UserDeleted::class);
	}

	public function boot(IBootContext $context): void {
	}
}
