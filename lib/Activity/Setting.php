<?php

declare(strict_types=1);

/*
 * SPDX-FileCopyrightText: 2016 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-only
 */

namespace OCA\TwoFactorEMail\Activity;

use OCA\TwoFactorEMail\AppInfo\Application;
use OCP\Activity\ISetting;
use OCP\IL10N;

class Setting implements ISetting {

	public function __construct(
		private IL10N $l10n,
	) {
	}

	public function canChangeMail(): bool {
		return false;
	}

	public function canChangeStream(): bool {
		return false;
	}

	public function getIdentifier(): string {
		return Application::APP_ID;
	}

	public function getName(): string {
		return $this->l10n->t('E-mail');
	}

	public function getPriority(): int {
		return 10;
	}

	public function isDefaultEnabledMail(): bool {
		return true;
	}

	public function isDefaultEnabledStream(): bool {
		return true;
	}
}
