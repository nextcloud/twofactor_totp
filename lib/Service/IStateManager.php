<?php

declare(strict_types=1);

/*
 * SPDX-FileCopyrightText: 2025 Olav and Niklas Seyfarth, Contributors <https://github.com/datenschutz-individuell/twofactor_email/blob/main/CONTRIBUTORS.md>
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\TwoFactorEMail\Service;

use OCP\IUser;

interface IStateManager {
	public function enable(IUser $user, bool $byAdmin = false): void;
	public function disable(IUser $user, bool $byAdmin = false): void;
	public function isEnabled(IUser $user): bool;
}
