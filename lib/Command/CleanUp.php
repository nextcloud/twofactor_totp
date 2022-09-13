<?php

declare(strict_types=1);

/**
 * @copyright Copyright (c) 2022 Daniel Kesselberg <mail@danielkesselberg.de>
 *
 * @author Daniel Kesselberg <mail@danielkesselberg.de>
 *
 * @license AGPL-3.0-or-later
 *
 * This code is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License, version 3,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License, version 3,
 * along with this program.  If not, see <http://www.gnu.org/licenses/>
 *
 */

namespace OCA\TwoFactorTOTP\Command;

use OCA\TwoFactorTOTP\Db\TotpSecretMapper;
use OCP\DB\Exception;
use OCP\IDBConnection;
use OCP\IUserManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CleanUp extends Command {
	/** @var IDBConnection */
	private $db;

	/** @var IUserManager */
	private $userManager;

	/** @var TotpSecretMapper */
	private $totpSecretMapper;

	public function __construct(
		IDBConnection $db,
		IUserManager $userManager,
		TotpSecretMapper $totpSecretMapper
	) {
		parent::__construct();

		$this->db = $db;
		$this->userManager = $userManager;
		$this->totpSecretMapper = $totpSecretMapper;
	}

	protected function configure(): void {
		$this
			->setName('twofactor_totp:cleanup')
			->setDescription('Remove orphaned totp secrets');
	}

	protected function execute(InputInterface $input, OutputInterface $output): int {
		$io = new SymfonyStyle($input, $output);
		$io->title('Remove totp secrets for deleted users');

		foreach ($this->findUserIds() as $userId) {
			if ($this->userManager->userExists($userId) === false) {
				try {
					$io->text('Delete secret for uid "' . $userId . '"');
					$this->totpSecretMapper->deleteSecretByUserId($userId);
				} catch (Exception $e) {
					$io->caution('Error deleting secret: ' . $e->getMessage());
				}
			}
		}

		$io->success('Orphaned totp secrets removed.');

		$io->text('Thank you for using Two-Factor TOTP!');
		return 0;
	}

	/**
	 * @throws Exception
	 */
	private function findUserIds(): array {
		$userIds = [];

		$qb = $this->db->getQueryBuilder()
			->selectDistinct('user_id')
			->from($this->totpSecretMapper->getTableName());

		$result = $qb->executeQuery();

		while ($row = $result->fetch()) {
			$userIds[] = $row['user_id'];
		}

		$result->closeCursor();

		return $userIds;
	}
}
