<?php

declare(strict_types=1);

/*
 * SPDX-FileCopyrightText: 2024 Raphael Gradenwitz <raphael.gradenwitz@googlemail.com>
 * SPDX-FileCopyrightText: 2026 Christoph Wurst <christoph@winzerhof-wurst.at>
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\TwoFactorTOTP\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\DB\Types;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;
use Override;

/** @psalm-api */
class Version170100Date20260611120000 extends SimpleMigrationStep {

	#[Override]
	public function changeSchema(IOutput $output, Closure $schemaClosure, array $options): ?ISchemaWrapper {
		/** @var ISchemaWrapper $schema */
		$schema = $schemaClosure();
		$table = $schema->getTable('twofactor_totp_secrets');

		if (!$table->hasColumn('algorithm')) {
			$table->addColumn('algorithm', Types::STRING, [
				'notnull' => true,
				'length' => 10,
				'default' => 'sha1',
			]);
		}

		return $schema;
	}
}
