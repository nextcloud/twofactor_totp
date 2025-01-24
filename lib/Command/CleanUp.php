<?php

declare(strict_types=1);

/*
 * SPDX-FileCopyrightText: 2022 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
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

	public function __construct(
		private IDBConnection $db,
		private IUserManager $userManager,
		private TotpSecretMapper $totpSecretMapper,
	) {
		parent::__construct();
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
