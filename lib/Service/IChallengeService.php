<?php

declare(strict_types = 1);

/*
 * SPDX-FileCopyrightText: 2017 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-only
 */

namespace OCA\TwoFactorEMail\Service;

use OCP\IUser;

interface IChallengeService {
	public function sendChallenge(IUser $user): void;

	public function verifyChallenge(IUser $user, string $submittedCode): bool;
}
