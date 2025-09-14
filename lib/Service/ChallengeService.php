<?php

declare(strict_types=1);

/*
 * SPDX-FileCopyrightText: 2018 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\TwoFactorEMail\Service;

use OCP\IUser;

final class ChallengeService implements IChallengeService {
	public function __construct(
		private ICodeGenerator $codeGenerator,
		private ICodeStorage $codeStorage,
		private IEMailSender $emailSender,
	) {
	}

	public function sendChallenge(IUser $user): void {
		$code = $this->codeGenerator->generateChallengeCode();
		$this->codeStorage->writeCode($user->getUID(), $code);
		$this->emailSender->sendChallengeEMail($user, $code);
	}

	public function verifyChallenge(IUser $user, string $submittedCode): bool {
		$submittedCode = trim($submittedCode);
		$code = $this->codeStorage->readCode($user->getUID());
		return $submittedCode === $code;
	}
}
