<?php

declare(strict_types=1);

/*
 * SPDX-FileCopyrightText: 2018 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\TwoFactorEMail\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

class Version030000Date20240307142849 extends SimpleMigrationStep {
	public function changeSchema(IOutput $output, Closure $schemaClosure, array $options) {
		/** @var ISchemaWrapper $schema */
		$schema = $schemaClosure();

		if (!$schema->hasTable('twofactor_email')) {
			$table = $schema->createTable('twofactor_email');
			$table->addColumn('id', \OCP\DB\Types::INTEGER, [
				'autoincrement' => true,
				'notnull' => true,
			]);
			$table->addColumn('user_id', \OCP\DB\Types::STRING, [
				'notnull' => true,
				'length' => 64,
			]);
			$table->addColumn('email', \OCP\DB\Types::STRING, [
				'notnull' => false,
				'length' => 255,
			]);
			$table->addColumn('state', \OCP\DB\Types::INTEGER, [
				'notnull' => true,
			]);
			$table->addColumn('auth_code', \OCP\DB\Types::STRING, [
				'notnull' => false,
				'length' => 6,
				'default' => null,
			]);
			$table->setPrimaryKey(['id']);
			$table->addUniqueIndex(['user_id'], 'twofactor_email_user_id');
		}
		return $schema;
	}
}
