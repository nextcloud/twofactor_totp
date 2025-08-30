<?php

declare(strict_types=1);

/*
 * SPDX-FileCopyrightText: 2018 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\TwoFactorEMail\Exception;

use OCP\IUser;
use Throwable;

final class EMailNotSetException extends SendEMailException {
	public function __construct(
		public readonly IUser $user,
		?Throwable $previous = null,
	) {
		parent::__construct("Failed to send e-mail to user '{$user->getUID()}': no e-mail set", previous: $previous);
	}
}
