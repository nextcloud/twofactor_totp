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
use Override;

/** @psalm-api */
class Version140000Date202503027114917 extends SimpleMigrationStep {
	private bool $hasVerfiedColumn = false;

	public function __construct(
		private readonly IDBConnection $db,
	) {
	}

	/**
	 * @param IOutput $output
	 * @param Closure $schemaClosure The `\Closure` returns a `ISchemaWrapper`
	 * @psalm-param Closure():ISchemaWrapper $schemaClosure
	 * @param array $options
	 * @return null|ISchemaWrapper
	 */
	#[Override]
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
	#[Override]
	public function postSchemaChange(IOutput $output, \Closure $schemaClosure, array $options) {
		if ($this->hasVerfiedColumn) {
			// There is a 'verified' column which comes from owncloud
			// If verified is set to 0 and the secret is not actively enabled (state=2/STATE_ENABLED),
			// the twofactor auth was not properly set up and we should remove those entries.
			// We must not delete state=2 rows: when the state column was added (2019), all existing rows
			// received state=2 as a default regardless of verified. Rows inserted by Nextcloud also never
			// have verified set (it stays at the DB default of 0), so verified=0 alone does not indicate
			// an incomplete setup for Nextcloud-native secrets.
			$qb = $this->db->getQueryBuilder();
			$qb->delete('twofactor_totp_secrets')
				->where($qb->expr()->eq('verified', $qb->createNamedParameter(0, IQueryBuilder::PARAM_INT)))
				->andWhere($qb->expr()->neq('state', $qb->createNamedParameter(2, IQueryBuilder::PARAM_INT))) // 2 = ITotp::STATE_ENABLED
				->executeStatement();
		}
	}

}
