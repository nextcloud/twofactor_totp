<?php

declare(strict_types=1);

namespace OCA\TwoFactorTOTP\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\Migration\SimpleMigrationStep;
use OCP\Migration\IOutput;

class Version030000Date20190305114917 extends SimpleMigrationStep {

	/**
	 * @param IOutput $output
	 * @param Closure $schemaClosure The `\Closure` returns a `ISchemaWrapper`
	 * @param array $options
	 * @return null|ISchemaWrapper
	 */
	public function changeSchema(IOutput $output, Closure $schemaClosure, array $options) {
		/** @var ISchemaWrapper $schema */
		$schema = $schemaClosure();

		$table = $schema->getTable('twofactor_totp_secrets');
		// TODO: use \OCP\DB\Types::BIGINT
		$table->addColumn('last_counter', 'bigint', [
			'notnull' => true,
			'default' => -1,
		]);

		return $schema;
	}
}
