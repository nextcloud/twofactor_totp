<?php

declare(strict_types=1);

/*
 * SPDX-FileCopyrightText: 2016 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-only
 */

namespace OCA\TwoFactorEMail\AppInfo;

use OCA\TwoFactorEMail\Event\StateChanged;
use OCA\TwoFactorEMail\Listener\EmailDeleted;
use OCA\TwoFactorEMail\Listener\StateChangeActivity;
use OCA\TwoFactorEMail\Listener\StateChangeRegistryUpdater;
use OCA\TwoFactorEMail\Service\ChallengeService;
use OCA\TwoFactorEMail\Service\ConstantApplicationSettings;
use OCA\TwoFactorEMail\Service\EMailAddressMasker;
use OCA\TwoFactorEMail\Service\EMailSender;
use OCA\TwoFactorEMail\Service\IApplicationSettings;
use OCA\TwoFactorEMail\Service\IChallengeService;
use OCA\TwoFactorEMail\Service\ICodeGenerator;
use OCA\TwoFactorEMail\Service\ICodeStorage;
use OCA\TwoFactorEMail\Service\IEMailAddressMasker;
use OCA\TwoFactorEMail\Service\IEMailSender;
use OCA\TwoFactorEMail\Service\IStateManager;
use OCA\TwoFactorEMail\Service\NumericalCodeGenerator;
use OCA\TwoFactorEMail\Service\PreferencesCodeStorage;
use OCA\TwoFactorEMail\Service\StateManager;
use OCP\Accounts\UserUpdatedEvent;
use OCP\AppFramework\App;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\AppFramework\Bootstrap\IRegistrationContext;

final class Application extends App implements IBootstrap {
	public const APP_ID = 'twofactor_email';

	public function __construct() {
		parent::__construct(self::APP_ID);
	}

	public function register(IRegistrationContext $context): void {
		include_once __DIR__ . '/../../vendor/autoload.php';

		$context->registerServiceAlias(IApplicationSettings::class, ConstantApplicationSettings::class);
		$context->registerServiceAlias(IChallengeService::class, ChallengeService::class);
		$context->registerServiceAlias(ICodeGenerator::class, NumericalCodeGenerator::class);
		$context->registerServiceAlias(ICodeStorage::class, PreferencesCodeStorage::class);
		$context->registerServiceAlias(IEMailAddressMasker::class, EMailAddressMasker::class);
		$context->registerServiceAlias(IEMailSender::class, EMailSender::class);
		$context->registerServiceAlias(IStateManager::class, StateManager::class);

		$context->registerEventListener(StateChanged::class, StateChangeActivity::class);
		$context->registerEventListener(StateChanged::class, StateChangeRegistryUpdater::class);
		$context->registerEventListener(UserUpdatedEvent::class, EmailDeleted::class);
	}

	public function boot(IBootContext $context): void {
	}
}
