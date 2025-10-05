<?php

declare(strict_types=1);

/*
 * SPDX-FileCopyrightText: 2025 Olav and Niklas Seyfarth, Contributors <https://github.com/datenschutz-individuell/twofactor_email/blob/master/CONTRIBUTORS.md>
 * SPDX-FileCopyrightText: 2025 Olav and Niklas Seyfarth, Contributors <https://github.com/datenschutz-individuell/twofactor_email/blob/master/CONTRIBUTORS.md>
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\TwoFactorEMail\Activity;

enum Notification: string {
	case ENABLED_BY_USER = 'twofactor_email_enabled_by_user';
	case DISABLED_BY_USER = 'twofactor_email_disabled_by_user';
	case ENABLED_BY_ADMIN = 'twofactor_email_enabled_by_admin';
	case DISABLED_BY_ADMIN = 'twofactor_email_disabled_by_admin';

	public function getSubjectText(): string {
		return match ($this) {
			Notification::ENABLED_BY_USER => 'You enabled e-mail two-factor authentication for your account',
			Notification::DISABLED_BY_USER => 'You disabled e-mail two-factor authentication for your account',
			Notification::ENABLED_BY_ADMIN => 'E-mail two-factor authentication was enabled by an admin',
			Notification::DISABLED_BY_ADMIN => 'E-mail two-factor authentication was disabled by an admin',
		};
	}
}
