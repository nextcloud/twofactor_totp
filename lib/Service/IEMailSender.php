<?php

declare(strict_types=1);

/*
 * SPDX-FileCopyrightText: 2018 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\TwoFactorEMail\Service;

use OCA\TwoFactorEMail\Exception\SendEMailException;
use OCP\IUser;

interface IEMailSender {
	/**
	 * @param IUser $user
	 * @param string $code
	 * @throws SendEMailException
	 */
	public function sendChallengeEMail(IUser $user, string $code): void;
}
