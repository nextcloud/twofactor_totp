<?php

declare(strict_types=1);

/*
 * SPDX-FileCopyrightText: 2018 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-only
 */

namespace OCA\TwoFactorTOTP\Event;

use OCP\IUser;

class DisabledByAdmin extends StateChanged {
	public function __construct(IUser $user) {
		parent::__construct($user, false);
	}
}
