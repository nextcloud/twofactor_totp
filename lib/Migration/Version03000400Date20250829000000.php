<?php

declare(strict_types=1);

/*
 * SPDX-FileCopyrightText: 2025 Olav and Niklas Seyfarth, Contributors <https://github.com/datenschutz-individuell/twofactor_email/blob/master/CONTRIBUTORS.md>
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\TwoFactorEMail\Migration;

// IUserConfig cannot be used since it's only available in NC ≥32 but migration also happens before that
use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\IConfig;
use OCP\IUser;
use OCP\IUserManager;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;
use Throwable;

final class Version03000400Date20250829000000 extends SimpleMigrationStep {
	private const APP_ID = 'twofactor_email';
	private const V2_KEY_CODE = 'authentication_code';
	private const V3_KEY_CODE = 'code';
	private const V3_KEY_CREATED_AT = 'code_created_at';
	private const CODE_REGEX = '/^\d{6}$/';

	public function __construct(
		private IConfig $config,
		private IUserManager $userManager,
	) {
	}

	public function changeSchema(IOutput $output, Closure $schemaClosure, array $options): ISchemaWrapper {
		/** @var ISchemaWrapper $schema */
		$schema = $schemaClosure();

		if ($schema->hasTable('twofactor_email')) {
			$output->info('Dropping legacy table twofactor_email in case early v3 versions were installed');
			$schema->dropTable('twofactor_email');
		}

		return $schema;
	}

	public function postSchemaChange(IOutput $output, Closure $schemaClosure, array $options): void {
		$now = time();
		$migrated = 0;
		$skippedInvalid = 0;
		$failed = 0;

		$legacyCodes = [];

		// Get a list of all current user IDs as array for bulk operation
		$uids = [];
		try {
			$this->userManager->callForAllUsers(function (IUser $user) use (&$uids) {
				$uids[] = $user->getUID();
			});
		} catch (Throwable $e) {
			$output->warning('Failed to enumerate users: ' . $e->getMessage());
		}

		// Abort, if config does not yield any user – a Nextcloud should have at least one (admin) user
		if (empty($uids)) {
			$output->warning('No users found, that is very unlikely; more likely is that something is wrong with this migration script');
			return;
		}

		// Only get twofactor_email v2 authentication codes for all user IDs, we don't need its other keys
		try {
			$legacyCodes = $this->config->getUserValueForUsers(self::APP_ID, self::V2_KEY_CODE, $uids) ?? [];
		} catch (Throwable $e) {
			// This might be an issue with a VERY LARGE number of users and LITTLE free memory
			$output->warning('Failed to read legacy codes in bulk: ' . $e->getMessage());
		}

		// Gracefully exit, if config does not contain any twofactor_email v2 authentication codes
		if (empty($legacyCodes)) {
			$output->info('No legacy authentication codes found; nothing to migrate.');
			return;
		}

		// Delete all settings of twofactor_email v2; we need to do this first if we want to purge ALL key/values
		try {
			$this->config->deleteAppFromAllUsers(self::APP_ID);
		} catch (Throwable $e) {
			$output->warning('Failed to delete legacy app settings: ' . $e->getMessage());
		}

		// Migrate to new scheme (see V3 constants above)
		foreach ($legacyCodes as $userId => $code) {
			$uid = (string)$userId;
			if (!is_string($code)) {
				$code = (string)$code;
			}

			// V2 only used 6-digit codes, only migrate if the value is as such
			if (!preg_match(self::CODE_REGEX, $code)) {
				$skippedInvalid++;
				$output->debug("Skipping invalid code for user $uid");
				continue;
			}

			// verify that it's written, setUserValue doesn't return status but throws exception
			try {
				$this->config->setUserValue($uid, self::APP_ID, self::V3_KEY_CODE, $code);
				$this->config->setUserValue($uid, self::APP_ID, self::V3_KEY_CREATED_AT, (string)$now);

				$migrated++;
				$output->debug("Migrated code $code for user $uid. Added current time $now as 'created at' since v2 did not have an expiry mechanism.");
			} catch (Throwable $e) {
				$failed++;
				$output->warning("Failed to migrate code for user $uid: " . $e->getMessage());
			}
		}

		$output->info("Migrated $migrated authentication codes.");
		if ($skippedInvalid > 0) {
			$output->info("Skipped $skippedInvalid invalid legacy entries.");
		}
		if ($failed > 0) {
			$output->info("Failed to write $failed entries to new config.");
		}
	}
}
