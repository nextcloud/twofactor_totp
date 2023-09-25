<?php

declare(strict_types=1);

/**
 * @copyright Copyright (c) 2018 Roeland Jago Douma <roeland@famdouma.nl>
 *
 * @author Roeland Jago Douma <roeland@famdouma.nl>
 *
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
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
