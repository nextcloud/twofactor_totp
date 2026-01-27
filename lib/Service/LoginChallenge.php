<?php

declare(strict_types=1);

/*
 * SPDX-FileCopyrightText: 2025 Olav and Niklas Seyfarth, Contributors <https://github.com/datenschutz-individuell/twofactor_email/blob/main/CONTRIBUTORS.md>
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\TwoFactorEMail\Service;

use OCP\IUser;

final class LoginChallenge implements ILoginChallenge {
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
		$isValid = $submittedCode === $code;
		if ($isValid) {
			$this->codeStorage->deleteCode($user->getUID());
			return true;
		} else {
			// don't delete, could be a typo
			return false;
		}
	}
}
