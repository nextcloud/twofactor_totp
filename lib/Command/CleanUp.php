<?php

declare(strict_types=1);

/*
 * SPDX-FileCopyrightText: 2022 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\TwoFactorEMail\Command;

use OCA\TwoFactorEMail\Service\ICodeStorage;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CleanUp extends Command {

	public function __construct(
		private ICodeStorage $codeStorage,
	) {
		parent::__construct();
	}

	protected function configure(): void {
		$this
			->setName('twofactor_email:cleanup')
			->setDescription('Remove expired two-factor e-mail codes.');
	}

	protected function execute(InputInterface $input, OutputInterface $output): int {
		$io = new SymfonyStyle($input, $output);
		$io->title('Removing expired two-factor e-mail codes');
		$this->codeStorage->deleteExpired();
		$io->success('Done.');
		return 0;
	}
}
