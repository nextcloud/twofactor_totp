<?php

declare(strict_types=1);

/*
 * SPDX-FileCopyrightText: 2025 Olav and Niklas Seyfarth, Contributors <https://github.com/datenschutz-individuell/twofactor_email/blob/main/CONTRIBUTORS.md>
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\TwoFactorEMail\Exception;

use Exception;
use OCP\IUser;
use Throwable;

final class EMailNotSet extends Exception {
	public function __construct(
		public readonly IUser $user,
		?Throwable $previous = null,
	) {
		parent::__construct("Failed to send e-mail to user '{$user->getUID()}': No e-mail set", previous: $previous);
	}
}
