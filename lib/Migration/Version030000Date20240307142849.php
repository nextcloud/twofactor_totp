<?php

declare(strict_types=1);

/**
 * @copyright Copyright (c) 2018 Roeland Jago Douma <roeland@famdouma.nl>
 *
 * @author Nico Kluge <nico.kluge@klugecoded.com>
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
