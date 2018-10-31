<?php
/**
 * @author Semih Serhat Karakaya <karakayasemi@itu.edu.tr>
 *
 * Two-factor TOTP
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

use InvalidArgumentException;
use OCA\TwoFactor_Totp\Db\TotpSecretMapper;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\IUserManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SetSecretVerificationStatusCommand extends Command {

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
			->setName('twofactor_totp:set-secret-verification-status')
			->setDescription(
				'Set secret verification status of specified users or all users'
			)
			->addArgument(
				'set-verified',
				InputArgument::REQUIRED,
				'Secret verification status to set. (true or false)'
			)
			->addOption(
				'all',
				null,
				InputOption::VALUE_NONE,
				'Will affect all users that use TOTP'
			)
			->addOption(
				'uid',
				'u',
				InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
				'The user\'s uid is used. This option can be used as --uid "Alice" --uid "Bob"'
			);
	}

	/**
	 * @param InputInterface $input
	 * @param OutputInterface $output
	 * @return int
	 */
	protected function execute(InputInterface $input, OutputInterface $output) {
		try {
			$options = $this->parseInput($input);
		} catch (InvalidArgumentException $e) {
			$message = $e->getMessage();
			$output->writeln("<error>$message</error>");
			return 1;
		}
		$status = $options['status'];
		if ($status === true) {
			$commandActionString = 'verified';
		} else {
			$commandActionString = 'unverified';
		}

		if ($options['uids'] === 'all') {
			$this->secretMapper->setAllSecretsVerificationStatus($status);
			$output->writeln("<info>The status of all TOTP secrets has been set to $commandActionString</info>");
			return 0;
		}
		foreach ($options['uids'] as $uid) {
			try {
				$user = $this->userManager->get($uid);
				if (!$user) {
					$output->writeln("<error>User $uid does not exist</error>");
					continue;
				}
				$dbSecret = $this->secretMapper->getSecret($user);
				$dbSecret->setVerified($options['status']);
				$this->secretMapper->update($dbSecret);
				$output->writeln("<info>The secret of $uid is $commandActionString</info>");
			} catch (DoesNotExistException $ex) {
				$output->writeln("<error>User has no secret: $uid</error>");
				return 1;
			}
		}
		return 0;
	}

	/**
	 * @param InputInterface $input
	 * @return array
	 */
	protected function parseInput(InputInterface $input) {
		$status = $input->getArgument('set-verified');
		if ($status === 'true') {
			$status = true;
		} elseif ($status === 'false') {
			$status = false;
		} else {
			throw new InvalidArgumentException(
				"Argument value is not valid. Use true or false as argument value."
			);
		}

		if ($input->getOption('all') !== false) {
			$uids = 'all';
		} elseif (\count($input->getOption('uid'))) {
			$uids = $input->getOption('uid');
		} else {
			throw new InvalidArgumentException(
				"No user input specified. Use at least one --uid option to specify an user or --all option for all users."
			);
		}

		$options = [
			'uids' => $uids,
			'status' => $status
		];
		return $options;
	}
}
