<?php

declare(strict_types=1);

/*
 * SPDX-FileCopyrightText: 2018 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\TwoFactorTOTP\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

class Version010501Date20181018124436 extends SimpleMigrationStep {

	/**
	 * @param IOutput $output
	 * @param Closure $schemaClosure The `\Closure` returns a `ISchemaWrapper`
	 * @param array $options
	 * @return ISchemaWrapper
	 */
	public function changeSchema(IOutput $output, Closure $schemaClosure, array $options) {
		/** @var ISchemaWrapper $schema */
		$schema = $schemaClosure();

		if (!$schema->hasTable('twofactor_totp_secrets')) {
			$table = $schema->createTable('twofactor_totp_secrets');
			// TODO: use \OCP\DB\Types::INT
			$table->addColumn('id', 'integer', [
				'autoincrement' => true,
				'notnull' => true,
				'length' => 4,
			]);
			// TODO: use \OCP\DB\Types::STRING
			$table->addColumn('user_id', 'string', [
				'notnull' => true,
				'length' => 64,
				'default' => '',
			]);
			// TODO: use \OCP\DB\Types::TEXT
			$table->addColumn('secret', 'text', [
				'notnull' => true,
			]);
			// TODO: \OCP\DB\Types::INT
			$table->addColumn('state', 'integer', [
				'notnull' => true,
				'default' => 2,
			]);
			$table->setPrimaryKey(['id']);
			$table->addUniqueIndex(['user_id'], 'totp_secrets_user_id');
		}
		return $schema;
	}
}
