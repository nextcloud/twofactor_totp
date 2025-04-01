<?php

declare(strict_types=1);

/*
 * SPDX-FileCopyrightText: 2025 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\TwoFactorTOTP\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

class Version140000Date202503027114917 extends SimpleMigrationStep {
	private bool $hasVerfiedColumn = false;

	public function __construct(
		private IDBConnection $db,
	) {
	}

	/**
	 * @param IOutput $output
	 * @param Closure $schemaClosure The `\Closure` returns a `ISchemaWrapper`
	 * @psalm-param Closure():ISchemaWrapper $schemaClosure
	 * @param array $options
	 * @return null|ISchemaWrapper
	 */
	public function changeSchema(IOutput $output, \Closure $schemaClosure, array $options) {
		$schema = $schemaClosure();
		$table = $schema->getTable('twofactor_totp_secrets');
		if ($table->hasColumn('verified')) {
			$this->hasVerfiedColumn = true;
		}
		return null;
	}

	/**
	 * @param IOutput $output
	 * @param Closure $schemaClosure The `\Closure` returns a `ISchemaWrapper`
	 * @psalm-param Closure():ISchemaWrapper $schemaClosure
	 * @param array $options
	 */
	public function postSchemaChange(IOutput $output, \Closure $schemaClosure, array $options) {
		if ($this->hasVerfiedColumn) {
			// There is a 'verified' column which comes from owncloud
			// If verified is set to 0 then the twofactor auth was not properly enabled and we should remove those entries
			$qb = $this->db->getQueryBuilder();
			$qb->delete('twofactor_totp_secrets')
				->where($qb->expr()->eq('verified', $qb->createNamedParameter(0, IQueryBuilder::PARAM_INT)))
				->executeStatement();
		}
	}

}
