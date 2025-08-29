<?php

declare(strict_types=1);

/*
 * SPDX-FileCopyrightText: 2018 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\TwoFactorEMail\Migration;

use Closure;
use OCP\Config\IUserConfig;
use OCP\DB\ISchemaWrapper;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

class Version030000Date20250814100800 extends SimpleMigrationStep {
	private const APP_ID = 'twofactor_email';
	private const V2_KEY_CODE = 'authentication_code';
	private const V3_KEY_CODE = 'code';
	private const V3_KEY_CREATED_AT = 'code_created_at';
	private const CODE_REGEX = '/^\d{6}$/';

	public function __construct(
		private IUserConfig $config,
	) {}

	public function changeSchema(IOutput $output, Closure $schemaClosure, array $options): ISchemaWrapper
	{
		/** @var ISchemaWrapper $schema */
		$schema = $schemaClosure();

		if ($schema->hasTable('twofactor_email')) {
			$output->info('Dropping legacy table twofactor_email in case early v3 versions were installed');
			$schema->dropTable('twofactor_email');
		}

		return $schema;
	}

	/*
	 * This function was implemented using OCA/ICodeStorage. This lead to errors due to that class not
	 * being available while in migration step (prior to enabling v3). I figured that out myself.
	 * To only use OCP classes, I used duck.ai with GPT-5 mini. I verified and adjusted the code.
	 */
	public function postSchemaChange(IOutput $output, Closure $schemaClosure, array $options): void
	{
		$now = time();
		$migrated = 0;
		$skippedInvalid = 0;
		$failed = 0;

		$legacyCodes = $this->config->getValuesByUsers(self::APP_ID, self::V2_KEY_CODE) ?? [];

		if (empty($legacyCodes)) {
			$this->config->deleteApp(self::APP_ID);
			$output->info('No legacy authentication codes found; nothing to migrate.');
			return;
		}

		foreach ($legacyCodes as $userId => $code) {
			if (!is_string($code)) { $code = (string)$code; }
			if (!preg_match(self::CODE_REGEX, $code)) {
				$skippedInvalid++; $output->debug("Skipping invalid code for user {$userId}");
				continue;
			}

			try {
				$this->config->setValueString($userId, self::APP_ID, self::V3_KEY_CODE, $code);
				$this->config->setValueInt($userId, self::APP_ID, self::V3_KEY_CREATED_AT, $now);
				$migrated++;
			} catch (\Throwable $e) {
				$failed++;
				$output->debug("Failed to migrate code for user {$userId}: " . $e->getMessage());
			}
		}

		try {
			$this->config->deleteApp(self::APP_ID);
		} catch (\Throwable $e) {
			$output->warning('Failed to delete legacy app config: ' . $e->getMessage());
		}

		$output->info("Migrated {$migrated} authentication codes.");
		if ($skippedInvalid > 0) {
			$output->warning("Skipped {$skippedInvalid} invalid legacy entries.");
		}
		if ($failed > 0) {
			$output->warning("Failed to write {$failed} entries to new config.");
		}
	}
}
