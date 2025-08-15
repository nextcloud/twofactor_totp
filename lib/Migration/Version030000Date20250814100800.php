<?php

declare(strict_types=1);

/*
 * SPDX-FileCopyrightText: 2018 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\TwoFactorEMail\Migration;

use Closure;
use NCU\Config\IUserConfig;
use OCA\TwoFactorEMail\AppInfo\Application;
use OCA\TwoFactorEMail\Service\ICodeStorage;
use OCP\DB\ISchemaWrapper;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

class Version030000Date20250814100800 extends SimpleMigrationStep {
	public function __construct(
		private IUserConfig $config,
		private ICodeStorage $codeStorage,
	)
	{
	}

	public function changeSchema(IOutput $output, Closure $schemaClosure, array $options): ISchemaWrapper
	{
		/** @var ISchemaWrapper $schema */
		$schema = $schemaClosure();

		if ($schema->hasTable('twofactor_email')) {
			$schema->dropTable('twofactor_email');
		}

		return $schema;
	}

	public function postSchemaChange(IOutput $output, Closure $schemaClosure, array $options): void
	{
		$codes = $this->config->getValuesByUsers(Application::APP_ID, 'authentication_code');
		$this->config->deleteApp(Application::APP_ID);
		$now = time();
		foreach ($codes as $userId => $code) {
			$this->codeStorage->writeCode($userId, $code, $now);
		}
	}
}
