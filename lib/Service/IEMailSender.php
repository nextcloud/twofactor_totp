<?php

declare(strict_types=1);

/*
 * SPDX-FileCopyrightText: 2025 Olav and Niklas Seyfarth, Contributors <https://github.com/datenschutz-individuell/twofactor_email/blob/main/CONTRIBUTORS.md>
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\TwoFactorEMail\Service;

use OCA\TwoFactorEMail\Exception\EMailNotSet;
use OCA\TwoFactorEMail\Exception\SendEMailFailed;
use OCP\IUser;

interface IEMailSender {
	/**
	 * @param IUser $user
	 * @param string $code
	 * @throws EMailNotSet
	 * @throws SendEMailFailed
	 */
	public function sendChallengeEMail(IUser $user, string $code): void;
}
