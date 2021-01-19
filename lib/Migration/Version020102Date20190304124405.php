<?php

declare(strict_types=1);

namespace OCA\TwoFactorTOTP\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\Migration\SimpleMigrationStep;
use OCP\Migration\IOutput;

class Version020102Date20190304124405 extends SimpleMigrationStep {

	/**
	 * @param IOutput $output
	 * @param Closure $schemaClosure The `\Closure` returns a `ISchemaWrapper`
	 * @param array $options
	 *
	 * @return ISchemaWrapper
	 */
	public function changeSchema(IOutput $output, Closure $schemaClosure, array $options) {
		/** @var ISchemaWrapper $schema */
		$schema = $schemaClosure();

		$table = $schema->getTable('twofactor_totp_secrets');
		if (!$table->hasColumn('state')) {
			// TODO: use \OCP\DB\Types::INT
			$table->addColumn('state', 'integer', [
				'notnull' => true,
				'default' => 2,
			]);
		}
		if (!$table->hasPrimaryKey()) {
			$table->setPrimaryKey(['id']);
		}
		if (!$table->hasIndex('totp_secrets_user_id')) {
			$table->addUniqueIndex(['user_id'], 'totp_secrets_user_id');
		}

		return $schema;
	}
}
