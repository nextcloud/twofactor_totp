<?php
/**
 * @author Sıla Boyraz <boyrazs15@itu.edu.tr>
 *
 * @copyright Copyright (c) 2019, ownCloud GmbH
 * @license AGPL-3.0
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

namespace OCA\TwoFactor_Totp\Command;

use OCA\TwoFactor_Totp\Db\TotpSecretMapper;
use OCP\IUserManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DeleteRedundantSecretsCommand extends Command {

	/** @var TotpSecretMapper */
	private $secretMapper;

	/** @var IUserManager */
	private $userManager;

	public function __construct(
		TotpSecretMapper $secretMapper,
		IUserManager $userManager
	) {
		parent::__construct();
		$this->secretMapper = $secretMapper;
		$this->userManager = $userManager;
	}

	protected function configure() {
		$this
			->setName('twofactor_totp:delete-redundant-secret')
			->setDescription(
				'Delete the redundant secret of non-existing users'
			);
	}

	/**
	 * @param InputInterface $input
	 * @param OutputInterface $output
	 * @return int
	 */
	protected function execute(InputInterface $input, OutputInterface $output) {
		$secrets = $this->secretMapper->getAllSecrets();
		$count=0;
		foreach ($secrets as $secret) {
			$userId = $secret['user_id'];
			if (!$this->userManager->userExists($userId)) {
				$this->secretMapper->deleteSecretsByUserId($userId);
				$output->writeln("<info>The redundant secret of $userId is deleted.</info>");
				$count++;
			}
		}

		$output->writeln("<info>$count redundant secrets are deleted</info>");
		return 0;
	}
}
