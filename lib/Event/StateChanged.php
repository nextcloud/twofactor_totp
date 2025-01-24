<?php

declare(strict_types=1);

/*
 * SPDX-FileCopyrightText: 2018 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-only
 */

namespace OCA\TwoFactorTOTP\Event;

use OCP\EventDispatcher\Event;
use OCP\IUser;

class StateChanged extends Event {

	public function __construct(
		private IUser $user,
		private bool $enabled,
	) {
		parent::__construct();
	}

	public function getUser(): IUser {
		return $this->user;
	}

	public function isEnabled(): bool {
		return $this->enabled;
	}
}
